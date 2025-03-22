package com.example.lab1;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class Login extends AppCompatActivity {

    private EditText etEmail, etPassword;
    private TextView tvForgotPassword;
    private ImageView ivLoginIcon, ivSignupIcon;

    // JSON contenant uniquement le compte admin TEMPORAIRE!!!!!!!!!!!!!!!!!!
    private static final String JSON_DATA = "{\n" +
            "  \"clients\": [\n" +
            "      {\n" +
            "          \"id\": 1,\n" +
            "          \"nom\": \"Admin\",\n" +
            "          \"prenom\": \"Admin\",\n" +
            "          \"email\": \"1\",\n" +
            "          \"mdp\": \"1\",\n" +
            "          \"age\": 30,\n" +
            "          \"telephone\": \"0000000000\",\n" +
            "          \"adresse\": \"Admin Address\"\n" +
            "      }\n" +
            "  ]\n" +
            "}";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        // Initialisation des vues
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        tvForgotPassword = findViewById(R.id.tvForgotPassword);
        ivLoginIcon = findViewById(R.id.ivLoginIcon);
        ivSignupIcon = findViewById(R.id.ivSignupIcon);

        // Si un email a été transmis depuis Register/forgot, le préremplir
        String registeredEmail = getIntent().getStringExtra("registeredEmail");
        if (registeredEmail != null && !registeredEmail.isEmpty()) {
            etEmail.setText(registeredEmail);
        }

        // Clic sur le lien "Mot de passe oublié ?"
        tvForgotPassword.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(Login.this, Forgot.class);
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
            }
        });

        // Clic sur l'icône de login, vérification des identifiants
        ivLoginIcon.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                verifyCredentials();
            }
        });

        // Clic sur l'icône de signup
        ivSignupIcon.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(Login.this, Register.class);
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
            }
        });
    }

    private void verifyCredentials() {
        String email = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString().trim();

        if (email.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "Veuillez saisir email et mot de passe.", Toast.LENGTH_SHORT).show();
            return;
        }

        try {
            JSONObject jsonObject = new JSONObject(JSON_DATA);
            JSONArray clients = jsonObject.getJSONArray("clients");
            boolean found = false;
            for (int i = 0; i < clients.length(); i++) {
                JSONObject client = clients.getJSONObject(i);
                // Compare email (insensible à la casse)
                if (client.getString("email").equalsIgnoreCase(email)) {
                    // Vérifie que le mot de passe correspond
                    if (client.getString("mdp").equals(password)) {
                        found = true;
                        break;
                    }
                }
            }

            if (found) {
                // Identifiants valides : rediriger vers la page Home
                Intent intent = new Intent(Login.this, Home.class);
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
                finish();
            } else {
                Toast.makeText(this, "Email ou mot de passe incorrect.", Toast.LENGTH_SHORT).show();
            }
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(this, "Erreur lors de la vérification.", Toast.LENGTH_SHORT).show();
        }
    }
}
