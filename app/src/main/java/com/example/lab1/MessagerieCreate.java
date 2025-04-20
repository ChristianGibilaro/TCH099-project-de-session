package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class MessagerieCreate extends AppCompatActivity {

    private static final String TAG = "MessagerieCreate";
    private static final String CREATE_CHAT_URL = "http://10.0.2.2:9999/api/creerChat";

    private EditText etConversationNameCreateMessage;
    private EditText etMemberPseudoCreateMessage;
    private EditText etAdditionalInfoCreateMessage;
    private LinearLayout btnAddMemberCreateMessageContainer;
    private LinearLayout layoutAddButtonCreateMessage;
    private String apiKey;  // API key for authenticated calls

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_create);

        // Retrieve apiKey from Intent
        apiKey = getIntent().getStringExtra("apiKey");
        Log.d(TAG, "onCreate: received apiKey = " + apiKey);

        etConversationNameCreateMessage = findViewById(R.id.etConversationNameCreateMessage);
        etMemberPseudoCreateMessage = findViewById(R.id.etMemberPseudoCreateMessage);
        etAdditionalInfoCreateMessage = findViewById(R.id.etAdditionalInfoCreateMessage);
        btnAddMemberCreateMessageContainer = findViewById(R.id.btnAddMemberCreateMessageContainer);
        layoutAddButtonCreateMessage = findViewById(R.id.layoutAddButtonCreateMessage);

        // The members field is not editable
        etAdditionalInfoCreateMessage.setEnabled(false);

        // Add member pseudo to list
        btnAddMemberCreateMessageContainer.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String newMember = etMemberPseudoCreateMessage.getText().toString().trim();
                if (newMember.isEmpty()) {
                    Toast.makeText(MessagerieCreate.this, "Veuillez saisir un pseudo.", Toast.LENGTH_SHORT).show();
                    return;
                }
                String currentMembers = etAdditionalInfoCreateMessage.getText().toString().trim();
                String updatedMembers = currentMembers.isEmpty() ? newMember : currentMembers + "," + newMember;
                etAdditionalInfoCreateMessage.setText(updatedMembers);
                etMemberPseudoCreateMessage.setText("");
                Log.d(TAG, "Added member: " + newMember + "; list=" + updatedMembers);
            }
        });

        // Create chat on button click
        layoutAddButtonCreateMessage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String convoName = etConversationNameCreateMessage.getText().toString().trim();
                String membersCsv = etAdditionalInfoCreateMessage.getText().toString().trim();
                if (convoName.isEmpty() || membersCsv.isEmpty()) {
                    Toast.makeText(MessagerieCreate.this, "Veuillez remplir le nom et ajouter au moins un membre.", Toast.LENGTH_SHORT).show();
                    Log.w(TAG, "Validation failed: convoName or members missing");
                    return;
                }
                // Convert CSV to JSON array string
                String[] pseudos = membersCsv.split(",");
                StringBuilder sb = new StringBuilder();
                sb.append('[');
                for (int i = 0; i < pseudos.length; i++) {
                    sb.append('"').append(pseudos[i].trim()).append('"');
                    if (i < pseudos.length - 1) sb.append(',');
                }
                sb.append(']');
                String pseudosJson = sb.toString();
                Log.d(TAG, "Creating chat: name=" + convoName + " pseudosJson=" + pseudosJson);

                // Launch AsyncTask to call API
                new CreateChatTask().execute(convoName, pseudosJson, apiKey);
            }
        });
    }

    private class CreateChatTask extends AsyncTask<String, Void, String> {
        private String apiKeyVal;

        @Override
        protected String doInBackground(String... args) {
            String chatName = args[0];
            String pseudosJson = args[1];
            apiKeyVal = args[2];
            HttpURLConnection conn = null;
            try {
                URL url = new URL(CREATE_CHAT_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
                conn.setRequestProperty("Accept", "application/json");

                // Build POST data
                String postData =
                        "apiKey=" + URLEncoder.encode(apiKeyVal, "UTF-8") +
                                "&chat_name=" + URLEncoder.encode(chatName, "UTF-8") +
                                "&pseudos=" + URLEncoder.encode(pseudosJson, "UTF-8");
                Log.d(TAG, "CreateChatTask ▶ postData=" + postData);

                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();

                int code = conn.getResponseCode();
                Log.d(TAG, "CreateChatTask ▶ HTTP code=" + code);
                InputStream is = (code == HttpURLConnection.HTTP_OK)
                        ? conn.getInputStream()
                        : conn.getErrorStream();

                BufferedReader reader = new BufferedReader(new InputStreamReader(is));
                StringBuilder resp = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    resp.append(line);
                }
                reader.close();
                Log.d(TAG, "CreateChatTask ▶ raw response=" + resp);
                return resp.toString();
            } catch (Exception e) {
                Log.e(TAG, "CreateChatTask ▶ network error", e);
                return null;
            } finally {
                if (conn != null) conn.disconnect();
            }
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d(TAG, "CreateChatTask ▶ onPostExecute=" + result);
            if (result == null) {
                Toast.makeText(MessagerieCreate.this, "Erreur réseau", Toast.LENGTH_LONG).show();
                return;
            }
            try {
                JSONObject json = new JSONObject(result);
                boolean success = json.optBoolean("success", false);
                if (success) {
                    Log.i(TAG, "Chat successfully created");
                    Toast.makeText(MessagerieCreate.this, "Chat créé !", Toast.LENGTH_LONG).show();
                    Intent intent = new Intent();
                    intent.putExtra("apiKey", apiKeyVal);
                    setResult(RESULT_OK, intent);
                    finish();
                } else {
                    String msg = json.optString("message", "Erreur création chat");
                    Log.w(TAG, "Server error: " + msg);
                    Toast.makeText(MessagerieCreate.this, msg, Toast.LENGTH_LONG).show();
                }
            } catch (Exception e) {
                Log.e(TAG, "CreateChatTask ▶ JSON parse error", e);
                Toast.makeText(MessagerieCreate.this, "Réponse invalide du serveur", Toast.LENGTH_LONG).show();
            }
        }
    }
}
