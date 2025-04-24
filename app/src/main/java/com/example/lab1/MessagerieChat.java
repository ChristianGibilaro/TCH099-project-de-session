package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;

public class MessagerieChat extends AppCompatActivity {
    private static final String TAG                 = "MessagerieChat";
    private static final String GET_MESSAGES_URL    =
            "http://10.0.2.2:9999/api/singleMessageSelonChatIDOnly/";
    private static final String POST_MESSAGE_URL    =
            "http://10.0.2.2:9999/api/envoyerMessage";
    private static final String USERINFO_BY_ID_URL  =
            "http://10.0.2.2:9999/api/chat/userinfoById";

    private TextView       tvConvoName;
    private RecyclerView   recyclerMessages;
    private ImageButton    btnBack, btnSend;
    private EditText       etMessage;
    private MessageAdapter messageAdapter;

    private String chatID;
    private String apiKey;
    private String currentUserName = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Log.d(TAG, "onCreate: initializing views");
        setContentView(R.layout.activity_messagerie_chat);

        tvConvoName      = findViewById(R.id.tvConvoName);
        recyclerMessages = findViewById(R.id.recyclerMessages);
        btnBack          = findViewById(R.id.btnBack);
        btnSend          = findViewById(R.id.btnSend);
        etMessage        = findViewById(R.id.etMessage);

        chatID = getIntent().getStringExtra("chatID");
        apiKey = getIntent().getStringExtra("apiKey");
        String convoName = getIntent().getStringExtra("convoName");
        String passedUser = getIntent().getStringExtra("currentUserName");
        if (passedUser != null) currentUserName = passedUser;

        Log.d(TAG, "onCreate: chatID=" + chatID +
                ", apiKey=" + apiKey +
                ", currentUserName=" + currentUserName);
        if (convoName != null) {
            tvConvoName.setText(convoName);
            Log.d(TAG, "onCreate: convoName=" + convoName);
        }

        recyclerMessages.setLayoutManager(new LinearLayoutManager(this));
        messageAdapter = new MessageAdapter();
        recyclerMessages.setAdapter(messageAdapter);

        btnBack.setOnClickListener(v -> {
            Log.d(TAG, "onBack clicked, finishing activity");
            finish();
        });
        btnSend.setOnClickListener(v -> {
            String text = etMessage.getText().toString().trim();
            Log.d(TAG, "onSend clicked: text='" + text + "'");
            if (text.isEmpty()) {
                Log.w(TAG, "onSend: text is empty, ignoring");
                return;
            }

            // show placeholder immediately
            messageAdapter.addMessage(new Message(text), currentUserName, "");
            recyclerMessages.scrollToPosition(messageAdapter.getItemCount() - 1);
            etMessage.setText("");

            Log.d(TAG, "SendMessageTask: executing with chatID=" + chatID + ", apiKey=" + apiKey);
            new SendMessageTask().execute(chatID, apiKey, text);
        });

        Log.d(TAG, "Starting initial LoadMessagesTask");
        new LoadMessagesTask().execute(chatID);
    }

    private class LoadMessagesTask extends AsyncTask<String, Void, List<Message>> {
        private final List<String> fetchedPseudos = new ArrayList<>();
        private final List<String> fetchedImages  = new ArrayList<>();
        private int previousCount;

        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            // remember how many items we already have
            previousCount = messageAdapter.getItemCount();
            Log.d(TAG, "LoadMessagesTask: onPreExecute, previousCount=" + previousCount);
        }

        @Override
        protected List<Message> doInBackground(String... params) {
            String id = params[0];
            Log.d(TAG, "LoadMessagesTask: doInBackground for chatID=" + id);
            List<Message> msgs = new ArrayList<>();

            try {
                // 1) Fetch full array
                String urlStr = GET_MESSAGES_URL
                        + URLEncoder.encode(id, "UTF-8")
                        + "?apiKey=" + URLEncoder.encode(apiKey, "UTF-8");
                Log.d(TAG, "GET messages URL: " + urlStr);
                HttpURLConnection conn = (HttpURLConnection) new URL(urlStr).openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");

                int code = conn.getResponseCode();
                Log.d(TAG, "GET messages response code: " + code);
                InputStream in = (code == 200) ? conn.getInputStream() : conn.getErrorStream();
                BufferedReader reader = new BufferedReader(new InputStreamReader(in));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) sb.append(line);
                reader.close();
                conn.disconnect();

                String raw = sb.toString();
                Log.d(TAG, "Raw messages JSON: " + raw);
                JSONArray arr = new JSONArray(raw);
                Log.d(TAG, "Messages array length: " + arr.length());

                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    String content = o.optString("content", o.optString("Content", ""));
                    int userId = o.optInt("senderID", o.optInt("SenderID", -1));
                    Log.d(TAG, "Message[" + i + "]: content='" + content + "', userId=" + userId);

                    msgs.add(new Message(content));

                    // 2) fetch that user’s pseudo+img
                    String pseudo = currentUserName;
                    String imgUrl = "";
                    String infoUrl = USERINFO_BY_ID_URL
                            + "?userID=" + URLEncoder.encode(String.valueOf(userId), "UTF-8");
                    Log.d(TAG, "Fetching userInfo URL: " + infoUrl);
                    HttpURLConnection uc = (HttpURLConnection) new URL(infoUrl).openConnection();
                    uc.setRequestMethod("GET");
                    uc.setRequestProperty("Accept", "application/json");
                    int infoCode = uc.getResponseCode();
                    Log.d(TAG, "userInfo response code: " + infoCode);
                    if (infoCode == 200) {
                        BufferedReader ir = new BufferedReader(new InputStreamReader(uc.getInputStream()));
                        StringBuilder isb = new StringBuilder();
                        String il;
                        while ((il = ir.readLine()) != null) isb.append(il);
                        ir.close();
                        JSONObject info = new JSONObject(isb.toString());
                        Log.d(TAG, "userInfo JSON: " + info.toString());
                        if (info.optBoolean("success", false)) {
                            pseudo = info.optString("pseudo", pseudo);
                            imgUrl = info.optString("img", "");
                            Log.d(TAG, "Parsed userInfo for userId=" + userId +
                                    ": pseudo='" + pseudo + "', imgUrl='" + imgUrl + "'");
                        }
                    } else {
                        Log.w(TAG, "userInfo non-200 code=" + infoCode);
                    }
                    uc.disconnect();

                    fetchedPseudos.add(pseudo);
                    fetchedImages.add(imgUrl);
                }

            } catch (Exception e) {
                Log.e(TAG, "Error loading messages", e);
            }
            return msgs;
        }

        @Override
        protected void onPostExecute(List<Message> msgs) {
            Log.d(TAG, "LoadMessagesTask: onPostExecute, total msgs=" + msgs.size() +
                    " (previousCount=" + previousCount + ")");
            for (int i = 0; i < msgs.size(); i++) {
                String name = fetchedPseudos.get(i);
                String img  = fetchedImages.get(i);
                if (i < previousCount) {
                    // update existing placeholder / old message
                    messageAdapter.updateMessageInfo(i, name, img);
                    Log.d(TAG, "Updated adapter[" + i + "] with real name/img");
                } else {
                    // append brand-new ones
                    messageAdapter.addMessage(msgs.get(i), name, img);
                    Log.d(TAG, "Appended new adapter[" + i + "]: content='" + msgs.get(i).getContent() + "'");
                }
            }
            if (!msgs.isEmpty()) {
                recyclerMessages.scrollToPosition(messageAdapter.getItemCount() - 1);
            }
        }
    }

    private class SendMessageTask extends AsyncTask<String, Void, Boolean> {
        @Override
        protected Boolean doInBackground(String... params) {
            try {
                String chatId = params[0], key = params[1], content = params[2];
                String postData = "chatID=" + URLEncoder.encode(chatId, "UTF-8")
                        + "&apiKey=" + URLEncoder.encode(key, "UTF-8")
                        + "&message=" + URLEncoder.encode(content, "UTF-8");
                Log.d(TAG, "SendMessageTask: POST data=" + postData);

                HttpURLConnection conn = (HttpURLConnection)
                        new URL(POST_MESSAGE_URL).openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
                conn.setRequestProperty("Accept", "application/json");

                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();

                int code = conn.getResponseCode();
                Log.d(TAG, "SendMessageTask: response code=" + code);
                conn.disconnect();
                return code == HttpURLConnection.HTTP_OK;
            } catch (Exception e) {
                Log.e(TAG, "Error sending message", e);
                return false;
            }
        }

        @Override
        protected void onPostExecute(Boolean ok) {
            if (!ok) {
                Toast.makeText(
                        MessagerieChat.this,
                        "Échec de l'envoi du message",
                        Toast.LENGTH_SHORT
                ).show();
            } else {
                // simply re-fetch and update placeholder / any new ones
                new LoadMessagesTask().execute(chatID);
            }
        }
    }
}
