package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
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

public class MessagerieHome extends AppCompatActivity {

    private static final String TAG = "MessagerieHome";
    private static final int REQUEST_CREATE_CONVO = 1;
    private static final String CHATS_URL = "http://10.0.2.2:9999/api/user/chats";

    private RecyclerView convoList;
    private ConvoAdapter adapter;
    private ImageButton btnLogout, btnMessages;
    private ImageButton btnCreateConvo;
    private String apiKey;  // API key for authenticated calls

    private TextView tvNoConvo;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_home);

        // Retrieve apiKey from Intent
        apiKey = getIntent().getStringExtra("apiKey");
        Log.d(TAG, "onCreate: received apiKey = " + apiKey);

        btnCreateConvo = findViewById(R.id.btnCreateConvo);
        convoList = findViewById(R.id.convoList);
        btnLogout   = findViewById(R.id.btnLogout);
        btnMessages = findViewById(R.id.btnMessages);

        // Setup RecyclerView as a vertical list (1 column)
        convoList.setLayoutManager(new GridLayoutManager(this, 1));
        adapter = new ConvoAdapter();
        convoList.setAdapter(adapter);
        tvNoConvo      = findViewById(R.id.tvNoConvo);
        Log.d(TAG, "RecyclerView and adapter initialized");

        // Load chats for this user
        Log.d(TAG, "Calling loadChats() to fetch chat list");
        loadChats();

        btnLogout.setOnClickListener(v -> {
            Log.d("MessagerieHome", "Logout tapped, returning to Login");
            startActivity(new Intent(MessagerieHome.this, Login.class));
            finish();
        });
        btnMessages.setOnClickListener(v -> {
            Log.d("MessagerieHome", "Messages tapped (déjà dans MessagerieHome)");
            startActivity(new Intent(MessagerieHome.this, Home.class).putExtra("apiKey", apiKey));;
            // adapter.refreshConversations();
        });
        // Set conversation click listener
        adapter.setOnConvoClickListener(conversation -> {
            Log.d(TAG, "onConvoClick: clicked conversation id=" + conversation.getId());
            Intent intent = new Intent(MessagerieHome.this, MessagerieChat.class);
            intent.putExtra("chatID", conversation.getId());
            intent.putExtra("apiKey", apiKey);
            startActivity(intent);
        });

        // Create conversation button
        btnCreateConvo.setOnClickListener(view -> {
            Log.d(TAG, "btnCreateConvo clicked");
            Intent intent = new Intent(MessagerieHome.this, MessagerieCreate.class);
            intent.putExtra("apiKey", apiKey);
            startActivityForResult(intent, REQUEST_CREATE_CONVO);
        });

        int marginInPixels = getResources().getDimensionPixelSize(R.dimen.item_margin);
        convoList.addItemDecoration(new MarginItemDecoration(marginInPixels));
        Log.d(TAG, "MarginItemDecoration added with margin=" + marginInPixels);
    }

    /**
     * Fetches the list of chats for the current user via API and populates the adapter.
     */
    private void loadChats() {
        new LoadChatsTask().execute(apiKey);
    }

    private class LoadChatsTask extends AsyncTask<String, Void, List<Conversation>> {
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            Log.d(TAG, "LoadChatsTask ▶ onPreExecute");
        }

        @Override
        protected List<Conversation> doInBackground(String... args) {
            String apiKeyVal = args[0];
            Log.d(TAG, "LoadChatsTask ▶ doInBackground with apiKey=" + apiKeyVal);
            HttpURLConnection conn = null;
            try {
                URL url = new URL(CHATS_URL);
                Log.d(TAG, "LoadChatsTask ▶ Opening connection to " + CHATS_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
                conn.setRequestProperty("Accept", "application/json");

                String postData = "apiKey=" + URLEncoder.encode(apiKeyVal, "UTF-8");
                Log.d(TAG, "LoadChatsTask ▶ postData=" + postData);
                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();
                Log.d(TAG, "LoadChatsTask ▶ POST sent");

                int code = conn.getResponseCode();
                Log.d(TAG, "LoadChatsTask ▶ HTTP response code=" + code);
                InputStream is = (code == HttpURLConnection.HTTP_OK)
                        ? conn.getInputStream()
                        : conn.getErrorStream();
                Log.d(TAG, "LoadChatsTask ▶ Reading from " + (code == HttpURLConnection.HTTP_OK ? "inputStream" : "errorStream"));

                BufferedReader reader = new BufferedReader(new InputStreamReader(is));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    sb.append(line);
                    Log.v(TAG, "LoadChatsTask ▶ read line: " + line);
                }
                reader.close();
                String jsonText = sb.toString();
                Log.d(TAG, "LoadChatsTask ▶ raw JSON response=" + jsonText);

                // Handle error response
                if (code != HttpURLConnection.HTTP_OK) {
                    JSONObject err = new JSONObject(jsonText);
                    Log.e(TAG, "LoadChatsTask ▶ Server error: " + err.optString("message"));
                    return null;
                }

                // Parse JSON array
                JSONArray arr = new JSONArray(jsonText);
                Log.d(TAG, "LoadChatsTask ▶ JSON array length=" + arr.length());
                List<Conversation> list = new ArrayList<>();
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject obj = arr.getJSONObject(i);
                    String id = obj.getString("chatID");
                    String name = obj.getString("chatName");
                    String imgUrl = obj.getString("creatorImg");
                    Log.d(TAG, String.format("LoadChatsTask ▶ parsing chat %d: id=%s, name=%s", i, id, name));
                    list.add(new Conversation(id, name, imgUrl));
                }
                return list;

            } catch (Exception e) {
                Log.e(TAG, "LoadChatsTask ▶ Exception in doInBackground", e);
                return null;
            } finally {
                if (conn != null) {
                    conn.disconnect();
                    Log.d(TAG, "LoadChatsTask ▶ connection closed");
                }
            }
        }

        @Override
        protected void onPostExecute(List<Conversation> convos) {
            Log.d(TAG, "LoadChatsTask ▶ onPostExecute, convos=" +
                    (convos == null ? "null" : "size=" + convos.size()));
            if (convos != null && !convos.isEmpty()) {
                // Il y a des conversations à afficher
                tvNoConvo.setVisibility(View.GONE);
                convoList.setVisibility(View.VISIBLE);
                adapter.setConversations(convos);
            } else {
                // Aucune conversation : on cache la liste et on affiche le message
                convoList.setVisibility(View.GONE);
                tvNoConvo.setVisibility(View.VISIBLE);
            }
        }

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        Log.d(TAG, "onActivityResult: requestCode=" + requestCode + ", resultCode=" + resultCode);
        if (requestCode == REQUEST_CREATE_CONVO && resultCode == RESULT_OK) {
            Log.d(TAG, "New conversation created, reloading chats");
            loadChats();
        }
    }
}
