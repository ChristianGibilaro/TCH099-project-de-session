package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
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
    private static final String TAG               = "MessagerieChat";
    private static final String GET_MESSAGES_URL  =
            "http://10.0.2.2:9999/api/singleMessageSelonChatIDOnly/";
    private static final String POST_MESSAGE_URL  =
            "http://10.0.2.2:9999/api/envoyerMessage";

    private TextView       tvConvoName;
    private RecyclerView   recyclerMessages;
    private ImageButton    btnBack, btnSend;
    private EditText       etMessage;
    private MessageAdapter messageAdapter;

    private String chatID;
    private String apiKey;
    /** Placeholder for your logged-in user’s name.  */
    private String currentUserName = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
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

        Log.d(TAG, "onCreate: chatID=" + chatID
                + ", apiKey=" + apiKey
                + ", convoName=" + convoName
                + ", user=" + currentUserName);

        if (convoName != null) {
            tvConvoName.setText(convoName);
        }

        recyclerMessages.setLayoutManager(new LinearLayoutManager(this));
        messageAdapter = new MessageAdapter();
        recyclerMessages.setAdapter(messageAdapter);

        btnBack.setOnClickListener(v -> finish());

        btnSend.setOnClickListener(v -> {
            String text = etMessage.getText().toString().trim();
            if (text.isEmpty()) return;

            // immediately show in UI
            messageAdapter.addMessage(new Message(text), currentUserName);
            recyclerMessages.scrollToPosition(messageAdapter.getItemCount() - 1);
            etMessage.setText("");

            // then post it
            new SendMessageTask().execute(chatID, apiKey, text);
        });

        // load history
        new LoadMessagesTask().execute(chatID);
    }

    private class LoadMessagesTask extends AsyncTask<String, Void, List<Message>> {
        private final List<String> senders = new ArrayList<>();

        @Override
        protected List<Message> doInBackground(String... params) {
            String id         = params[0];
            List<Message> msgs = new ArrayList<>();
            senders.clear();

            HttpURLConnection conn = null;
            try {
                String urlString =
                        GET_MESSAGES_URL + URLEncoder.encode(id, "UTF-8");
                URL url = new URL(urlString);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");

                int code = conn.getResponseCode();
                InputStream in = (code == 200)
                        ? conn.getInputStream()
                        : conn.getErrorStream();

                BufferedReader reader =
                        new BufferedReader(new InputStreamReader(in));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    sb.append(line);
                }
                reader.close();

                String raw = sb.toString();
                if (raw.trim().startsWith("[")) {
                    JSONArray arr = new JSONArray(raw);
                    for (int i = 0; i < arr.length(); i++) {
                        JSONObject o = arr.getJSONObject(i);
                        String content = o.optString("content",
                                o.optString("Content", ""));
                        // adjust this key to match your JSON
                        String sender  = o.optString("sender",
                                o.optString("pseudo", currentUserName));

                        msgs.add(new Message(content));
                        senders.add(sender);
                    }
                } else {
                    Log.e(TAG, "Expected JSON array but got: " + raw);
                }
            } catch (Exception e) {
                Log.e(TAG, "Error loading messages", e);
            } finally {
                if (conn != null) conn.disconnect();
            }
            return msgs;
        }

        @Override
        protected void onPostExecute(List<Message> msgs) {
            for (int i = 0; i < msgs.size(); i++) {
                messageAdapter.addMessage(msgs.get(i), senders.get(i));
            }
            if (!msgs.isEmpty()) {
                recyclerMessages
                        .scrollToPosition(messageAdapter.getItemCount() - 1);
            }
        }
    }

    private class SendMessageTask extends AsyncTask<String, Void, Boolean> {
        @Override
        protected Boolean doInBackground(String... params) {
            String chatId  = params[0];
            String key     = params[1];
            String content = params[2];
            HttpURLConnection conn = null;
            try {
                URL url = new URL(POST_MESSAGE_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setRequestProperty(
                        "Content-Type", "application/x-www-form-urlencoded"
                );
                conn.setRequestProperty("Accept", "application/json");
                conn.setDoOutput(true);

                String postData =
                        "chatID="  + URLEncoder.encode(chatId,  "UTF-8")
                                + "&apiKey=" + URLEncoder.encode(key,     "UTF-8")
                                + "&message="+ URLEncoder.encode(content, "UTF-8");

                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();

                int code = conn.getResponseCode();
                InputStream in = (code == 200)
                        ? conn.getInputStream()
                        : conn.getErrorStream();
                BufferedReader reader =
                        new BufferedReader(new InputStreamReader(in));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    sb.append(line);
                }
                reader.close();

                return (code == HttpURLConnection.HTTP_OK);
            } catch (Exception e) {
                Log.e(TAG, "Error sending message", e);
                return false;
            } finally {
                if (conn != null) conn.disconnect();
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
            }
        }
    }
}
