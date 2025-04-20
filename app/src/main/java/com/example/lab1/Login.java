package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;  // Pour les logs
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class Login extends AppCompatActivity {

    private static final String TAG = "LoginActivity";
    private EditText etEmail, etPassword;
    private TextView tvForgotPassword;
    private Button btnLogin, btnSignup;

    // URL pour l'endpoint de connexion
    private final String LOGIN_URL = "http://10.0.2.2:9999/api/connexionUser";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login); // Vérifie que c'est le bon layout

        // Initialisation des vues
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        tvForgotPassword = findViewById(R.id.tvForgotPassword);
        btnLogin = findViewById(R.id.btnLogin);
        btnSignup = findViewById(R.id.btnSignup);

        Log.d(TAG, "onCreate: Activité de login démarrée.");

        // Pré-remplissage de l'email si transmis depuis Register/Forgot
        String registeredEmail = getIntent().getStringExtra("registeredEmail");
        if (registeredEmail != null && !registeredEmail.isEmpty()) {
            etEmail.setText(registeredEmail);
            Log.d(TAG, "onCreate: Email pré-rempli: " + registeredEmail);
        }

        // Listener pour le lien "Mot de passe oublié ?"
        tvForgotPassword.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "onClick: Lien 'Mot de passe oublié' cliqué.");
                Intent intent = new Intent(Login.this, Forgot.class);
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
            }
        });

        // Listener pour le bouton de login : validation et envoi de la requête
        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "onClick: Bouton de login cliqué.");
                verifyCredentials();
            }
        });

        // Listener pour le bouton de sign up : passage à l'activité Register
        btnSignup.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "onClick: Bouton de sign up cliqué.");
                Intent intent = new Intent(Login.this, Register.class);
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
            }
        });
    }

    /**
     * Vérifie les entrées utilisateur et lance la tâche asynchrone de connexion.
     */
    private void verifyCredentials() {
        String email = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString().trim();

        Log.d(TAG, "verifyCredentials: email = " + email + ", password = " + password);

        if (email.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "Veuillez saisir email et mot de passe.", Toast.LENGTH_SHORT).show();
            Log.d(TAG, "verifyCredentials: Champs vides.");
            return;
        }

        try {
            // Construire les données POST en encodage URL
            String postData = "email=" + URLEncoder.encode(email, "UTF-8")
                    + "&password=" + URLEncoder.encode(password, "UTF-8");
            Log.d(TAG, "verifyCredentials: Données POST = " + postData);

            // Lancer la tâche asynchrone
            new LoginTask().execute(postData);
        } catch (Exception e) {
            Log.e(TAG, "verifyCredentials: Erreur lors de la préparation de la connexion: " + e.getMessage());
            e.printStackTrace();
            Toast.makeText(this, "Erreur lors de la préparation de la connexion.", Toast.LENGTH_SHORT).show();
        }
    }

    /**
     * Tâche asynchrone pour effectuer la requête de connexion en arrière-plan.
     */
    private class LoginTask extends AsyncTask<String, Void, String> {

        @Override
        protected void onPreExecute() {
            Log.d(TAG, "LoginTask: Début de la tâche asynchrone.");
        }

        @Override
        protected String doInBackground(String... params) {
            String postData = params[0];
            HttpURLConnection connection = null;
            try {
                URL url = new URL(LOGIN_URL);
                Log.d(TAG, "LoginTask: Ouverture de la connexion à " + LOGIN_URL);
                connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("POST");
                connection.setDoOutput(true);
                // Définir les headers pour envoyer des données encodées en formulaire
                connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
                connection.setRequestProperty("Accept", "application/json");

                // Envoyer les données POST
                OutputStream os = connection.getOutputStream();
                os.write(postData.getBytes("UTF-8"));
                os.flush();
                os.close();
                Log.d(TAG, "LoginTask: Données POST envoyées: " + postData);

                int responseCode = connection.getResponseCode();
                Log.d(TAG, "LoginTask: Code de réponse HTTP = " + responseCode);

                InputStream is = (responseCode == HttpURLConnection.HTTP_OK) ?
                        connection.getInputStream() : connection.getErrorStream();
                BufferedReader in = new BufferedReader(new InputStreamReader(is));
                StringBuilder responseSb = new StringBuilder();
                String line;
                while ((line = in.readLine()) != null) {
                    responseSb.append(line);
                }
                in.close();

                String response = responseSb.toString();
                Log.d(TAG, "LoginTask: Réponse brute du serveur = " + response);
                return response;
            } catch (IOException e) {
                Log.e(TAG, "LoginTask: Erreur réseau: " + e.getMessage());
                return null;
            } finally {
                if (connection != null) {
                    connection.disconnect();
                    Log.d(TAG, "LoginTask: Connexion fermée.");
                }
            }
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d(TAG, "LoginTask: onPostExecute résultat = " + result);
            if (result != null) {
                try {
                    JSONObject jsonResponse = new JSONObject(result);

                    // Vérifie si la réponse indique un succès
                    if (jsonResponse.optBoolean("success")) {
                        JSONObject userObj = jsonResponse.getJSONObject("user");
                        String userId = userObj.getString("ID");
                        Log.d(TAG, "LoginTask: Connexion réussie, userId = " + userId);
                        // Passage à l'activité Home en transmettant l'userId
                        Intent intent = new Intent(Login.this, Home.class);
                        intent.putExtra("userId", userId);
                        startActivity(intent);
                        overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
                        finish();
                    } else {
                        // Sinon, récupère le message d'erreur
                        String message = jsonResponse.optString("message", "Email ou mot de passe incorrect.");
                        Log.d(TAG, "LoginTask: Message d'erreur reçu = " + message);
                        Toast.makeText(Login.this, message, Toast.LENGTH_LONG).show();
                    }
                } catch (JSONException e) {
                    Log.e(TAG, "LoginTask: Erreur lors du parsing JSON: " + e.getMessage());
                    Toast.makeText(Login.this, "Erreur lors de la lecture de la réponse.", Toast.LENGTH_LONG).show();
                }
            } else {
                Toast.makeText(Login.this, "Erreur de connexion au serveur", Toast.LENGTH_LONG).show();
                Log.e(TAG, "LoginTask: Résultat NULL, connexion échouée.");
            }
        }

    }
}
