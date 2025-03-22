package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;
import java.io.IOException;

public class Register extends AppCompatActivity {

    private static final int PICK_IMAGE_REQUEST = 1;

    private EditText etPseudonyme, etNom, etPrenom, etEmail, etAge, etTelephone, etAdresse,
            etPassword, etConfirmPassword, etDescription;
    private ImageView ivProfilePicker, ivProfilePreview;
    private CheckBox cbReglement;
    private Button btnRegister;

    private Uri imageUri; // Pour stocker l'URI de l'image sélectionnée

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // Initialisation des vues
        etPseudonyme = findViewById(R.id.etPseudonyme);
        etNom = findViewById(R.id.etNom);
        etPrenom = findViewById(R.id.etPrenom);
        etEmail = findViewById(R.id.etEmail);
        etAge = findViewById(R.id.etAge);
        etTelephone = findViewById(R.id.etTelephone);
        etAdresse = findViewById(R.id.etAdresse);
        etPassword = findViewById(R.id.etPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);
        etDescription = findViewById(R.id.etDescription);
        ivProfilePicker = findViewById(R.id.ivProfilePicker);
        ivProfilePreview = findViewById(R.id.ivProfilePreview);
        cbReglement = findViewById(R.id.cbReglement);
        btnRegister = findViewById(R.id.btnRegister);

        // Ouvrir la galerie pour sélectionner une image
        ivProfilePicker.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                openGallery();
            }
        });

        // Bouton d’inscription
        btnRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                registerUser();
            }
        });
    }

    // Méthode pour ouvrir la galerie
    private void openGallery() {
        Intent intent = new Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
        startActivityForResult(intent, PICK_IMAGE_REQUEST);
    }

    // Récupération du résultat de la sélection d'image
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == PICK_IMAGE_REQUEST && resultCode == RESULT_OK && data != null) {
            imageUri = data.getData();
            if (imageUri != null) {
                // Affiche un aperçu de l'image sélectionnée
                try {
                    Bitmap bitmap = MediaStore.Images.Media.getBitmap(getContentResolver(), imageUri);
                    ivProfilePreview.setImageBitmap(bitmap);
                    ivProfilePreview.setVisibility(View.VISIBLE);
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        }
    }

    // Méthode pour valider et récupérer toutes les informations
    private void registerUser() {
        String pseudonyme = etPseudonyme.getText().toString().trim();
        String nom = etNom.getText().toString().trim();
        String prenom = etPrenom.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String age = etAge.getText().toString().trim();
        String telephone = etTelephone.getText().toString().trim();
        String adresse = etAdresse.getText().toString().trim();
        String password = etPassword.getText().toString().trim();
        String confirmPassword = etConfirmPassword.getText().toString().trim();
        String description = etDescription.getText().toString().trim();

        // Vérification simple (champs obligatoires, mot de passe identique, case à cocher…)
        if (TextUtils.isEmpty(pseudonyme) || TextUtils.isEmpty(nom) || TextUtils.isEmpty(prenom)
                || TextUtils.isEmpty(email) || TextUtils.isEmpty(age) || TextUtils.isEmpty(telephone)
                || TextUtils.isEmpty(adresse) || TextUtils.isEmpty(password) || TextUtils.isEmpty(confirmPassword)) {
            Toast.makeText(this, "Veuillez remplir tous les champs obligatoires.", Toast.LENGTH_SHORT).show();
            return;
        }

        if (!password.equals(confirmPassword)) {
            Toast.makeText(this, "Les mots de passe ne correspondent pas.", Toast.LENGTH_SHORT).show();
            return;
        }

        if (!cbReglement.isChecked()) {
            Toast.makeText(this, "Veuillez accepter le règlement.", Toast.LENGTH_SHORT).show();
            return;
        }

        // Limiter la description à 50 mots (optionnel)
        int wordCount = description.split("\\s+").length;
        if (wordCount > 50) {
            Toast.makeText(this, "La description ne doit pas dépasser 50 mots.", Toast.LENGTH_SHORT).show();
            return;
        }

        // À ce stade, tout est valide, le compte est "créé"
        Toast.makeText(this,
                "Inscription réussie pour " + pseudonyme + " (" + email + ")",
                Toast.LENGTH_LONG).show();

        // Rediriger vers la page Login en pré-remplissant le champ email
        Intent intent = new Intent(Register.this, Login.class);
        intent.putExtra("registeredEmail", email);
        startActivity(intent);

        overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
        finish();
    }
}
