package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class Login extends AppCompatActivity {

    private static final String TAG = "LoginActivity";
    private EditText etEmail, etPassword;
    private Button btnLogin, btnSignup;

    // Fixed to the real login route
    private final String LOGIN_URL = "http://10.0.2.2:9999/api/user/connect";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        etEmail           = findViewById(R.id.etEmail);
        etPassword        = findViewById(R.id.etPassword);
        btnLogin          = findViewById(R.id.btnLogin);
        btnSignup         = findViewById(R.id.btnSignup);

        Log.d(TAG, "onCreate: Login screen started");

        String preEmail = getIntent().getStringExtra("registeredEmail");
        if (preEmail != null && !preEmail.isEmpty()) {
            etEmail.setText(preEmail);
            Log.d(TAG, "onCreate: pre‑filled email → " + preEmail);
        }



        btnSignup.setOnClickListener(v -> {
            Log.d(TAG, "onClick: Signup tapped");
            startActivity(new Intent(Login.this, Register.class));
        });

        btnLogin.setOnClickListener(v -> {
            Log.d(TAG, "onClick: Login tapped");
            verifyCredentials();
        });
    }

    private void verifyCredentials() {
        String email    = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString();

        Log.d(TAG, "verifyCredentials: email='" + email + "'  password=(hidden)");

        if (email.isEmpty() || password.isEmpty()) {
            Toast.makeText(this,
                    "Veuillez saisir email et mot de passe.",
                    Toast.LENGTH_SHORT
            ).show();
            Log.w(TAG, "verifyCredentials: missing fields");
            return;
        }

        try {
            String postData =
                    "email="    + URLEncoder.encode(email,    "UTF-8")
                            + "&password=" + URLEncoder.encode(password, "UTF-8");
            Log.d(TAG, "verifyCredentials: postData → " + postData);

            new LoginTask().execute(postData);
        } catch (Exception e) {
            Log.e(TAG, "verifyCredentials: error encoding params", e);
            Toast.makeText(this,
                    "Erreur préparation connexion.",
                    Toast.LENGTH_SHORT
            ).show();
        }
    }

    private class LoginTask extends AsyncTask<String, Void, String> {
        @Override
        protected void onPreExecute() {
            Log.d(TAG, "LoginTask ▶ onPreExecute");
        }

        @Override
        protected String doInBackground(String... args) {
            String postData = args[0];
            HttpURLConnection conn = null;

            try {
                URL url = new URL(LOGIN_URL);
                Log.d(TAG, "LoginTask ▶ connect to " + LOGIN_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setDoOutput(true);
                conn.setRequestProperty(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                );
                conn.setRequestProperty("Accept", "application/json");

                // send body
                OutputStream os = conn.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();
                Log.d(TAG, "LoginTask ▶ POST sent");

                int code = conn.getResponseCode();
                Log.d(TAG, "LoginTask ▶ HTTP response code: " + code);

                InputStream is = (
                        code == HttpURLConnection.HTTP_OK
                                ? conn.getInputStream()
                                : conn.getErrorStream()
                );
                BufferedReader reader = new BufferedReader(
                        new InputStreamReader(is)
                );
                StringBuilder resp = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    resp.append(line);
                }
                reader.close();
                String response = resp.toString();
                Log.d(TAG, "LoginTask ▶ raw response: " + response);
                return response;

            } catch (Exception e) {
                Log.e(TAG, "LoginTask ▶ network error", e);
                return null;
            } finally {
                if (conn != null) {
                    conn.disconnect();
                    Log.d(TAG, "LoginTask ▶ connection closed");
                }
            }
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d(TAG, "LoginTask ▶ onPostExecute: " + result);
            if (result == null) {
                Toast.makeText(Login.this,
                        "Erreur de connexion au serveur",
                        Toast.LENGTH_LONG
                ).show();
                return;
            }

            try {
                JSONObject json = new JSONObject(result);
                boolean ok = json.optBoolean("success", false);
                Log.d(TAG, "LoginTask ▶ parsed success=" + ok);

                if (ok) {
                    String apiKey = json.getString("apiKey");
                    Log.i(TAG, "LoginTask ▶ received apiKey=" + apiKey);

                    // Pass apiKey to next activity
                    Intent intent = new Intent(Login.this, Home.class);
                    intent.putExtra("apiKey", apiKey);
                    startActivity(intent);
                    finish();

                } else {
                    String msg = json.optString(
                            "message",
                            "Email ou mot de passe incorrect."
                    );
                    Log.w(TAG, "LoginTask ▶ server error message: " + msg);
                    Toast.makeText(Login.this, msg, Toast.LENGTH_LONG).show();
                }
            } catch (Exception e) {
                Log.e(TAG, "LoginTask ▶ JSON parse error", e);
                Toast.makeText(Login.this,
                        "Réponse inattendue du serveur",
                        Toast.LENGTH_LONG
                ).show();
            }
        }
    }
}
