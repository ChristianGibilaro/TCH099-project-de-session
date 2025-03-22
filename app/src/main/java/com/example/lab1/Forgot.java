package com.example.lab1;  // Adapte en fonction de ton package

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

public class Forgot extends AppCompatActivity {

    private EditText etForgotEmail;
    private ImageView ivSendIcon;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_forgot); // Assure-toi que le nom correspond à ton fichier XML

        // Initialisation des vues
        etForgotEmail = findViewById(R.id.etForgotEmail);
        ivSendIcon = findViewById(R.id.ivSendIcon);

        // Clic sur l'icône d’envoi
        ivSendIcon.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String email = etForgotEmail.getText().toString().trim();

                if (!email.isEmpty()) {
                    // Affiche un toast indiquant que l'email a été envoyé
                    Toast.makeText(Forgot.this,
                            "Un email a été envoyé à " + email,
                            Toast.LENGTH_SHORT).show();

                    // Redirection vers Login en préremplissant le champ email
                    Intent intent = new Intent(Forgot.this, Login.class);
                    intent.putExtra("registeredEmail", email);
                    startActivity(intent);
                    finish();
                } else {
                    Toast.makeText(Forgot.this,
                            "Veuillez saisir votre email.",
                            Toast.LENGTH_SHORT).show();
                }
            }
        });
    }
}
