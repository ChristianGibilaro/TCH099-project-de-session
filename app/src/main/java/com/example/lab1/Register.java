package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.provider.MediaStore;
import android.text.TextUtils;
import android.util.Log; // Import Log for debugging
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
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

public class Register extends AppCompatActivity {

    private static final int PICK_IMAGE_REQUEST = 1;
    private EditText etPseudonyme, etNom, etPrenom, etEmail, etPassword, etConfirmPassword;
    private ImageView ivProfilePicker, ivProfilePreview;
    private CheckBox cbReglement;
    private Button btnRegister;
    private Uri imageUri; // To store the selected image URI

    // Replace with your actual register endpoint URL
    private final String REGISTER_URL = "http://10.0.2.2:9999/api/createUser";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        etPseudonyme = findViewById(R.id.etPseudonyme);
        etNom = findViewById(R.id.etNom);
        etPrenom = findViewById(R.id.etPrenom);
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);
        ivProfilePicker = findViewById(R.id.ivProfilePicker);
        ivProfilePreview = findViewById(R.id.ivProfilePreview);
        cbReglement = findViewById(R.id.cbReglement);
        btnRegister = findViewById(R.id.btnRegister);

        // Open gallery to select an image (optional)
        ivProfilePicker.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                openGallery();
            }
        });

        // Set register button click listener
        btnRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                registerUser();
            }
        });
    }

    // Open the gallery to select an image
    private void openGallery() {
        Intent intent = new Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
        startActivityForResult(intent, PICK_IMAGE_REQUEST);
    }

    // Handle the result from the gallery
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == PICK_IMAGE_REQUEST && resultCode == RESULT_OK && data != null) {
            imageUri = data.getData();
            if (imageUri != null) {
                try {
                    Bitmap bitmap = MediaStore.Images.Media.getBitmap(getContentResolver(), imageUri);
                    ivProfilePreview.setImageBitmap(bitmap);
                    ivProfilePreview.setVisibility(View.VISIBLE);
                    Log.d("Register", "Image selected: " + imageUri.toString());
                } catch (IOException e) {
                    e.printStackTrace();
                    Log.e("Register", "Error loading image: " + e.getMessage());
                }
            }
        }
    }

    // Validate the input fields and send the registration request
    private void registerUser() {
        String pseudo = etPseudonyme.getText().toString().trim();
        String nom = etNom.getText().toString().trim();
        String prenom = etPrenom.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString().trim();
        String confirmPassword = etConfirmPassword.getText().toString().trim();

        if (TextUtils.isEmpty(pseudo) || TextUtils.isEmpty(nom) || TextUtils.isEmpty(prenom)
                || TextUtils.isEmpty(email) || TextUtils.isEmpty(password) || TextUtils.isEmpty(confirmPassword)) {
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

        // Combine nom and prenom to create full name
        String fullName = nom + " " + prenom;

        // Build JSON object with the required fields in the correct order
        JSONObject json = new JSONObject();
        try {
            json.put("Pseudo", pseudo);
            json.put("Name", fullName);
            json.put("Email", email);
            json.put("Password", password);
        } catch (JSONException e) {
            e.printStackTrace();
            Log.e("Register", "JSON Error: " + e.getMessage());
        }

        // Debug print: log the JSON payload before sending
        Log.d("Register", "Sending JSON: " + json.toString());

        // Execute the POST request asynchronously
        new RegisterTask().execute(json.toString());
    }

    // AsyncTask to perform the registration network request on a background thread
    private class RegisterTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... params) {
            String jsonString = params[0];
            HttpURLConnection conn = null;
            try {
                URL url = new URL(REGISTER_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Content-Type", "application/json");
                conn.setDoOutput(true);

                // Write JSON data to the request body
                OutputStream os = conn.getOutputStream();
                os.write(jsonString.getBytes("UTF-8"));
                os.close();

                // Log response code for debugging
                int responseCode = conn.getResponseCode();
                Log.d("RegisterTask", "Response Code: " + responseCode);

                // Read the response
                InputStream is = (responseCode == HttpURLConnection.HTTP_OK) ? conn.getInputStream() : conn.getErrorStream();
                BufferedReader in = new BufferedReader(new InputStreamReader(is));
                StringBuilder response = new StringBuilder();
                String inputLine;
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                // Log the response for debugging
                Log.d("RegisterTask", "Response: " + response.toString());

                return response.toString();
            } catch (IOException e) {
                e.printStackTrace();
                Log.e("RegisterTask", "Network Error: " + e.getMessage());
            } finally {
                if (conn != null) {
                    conn.disconnect();
                }
            }
            return null;
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d("RegisterTask", "onPostExecute result: " + result);
            if (result != null) {
                Toast.makeText(Register.this, "Inscription réussie", Toast.LENGTH_LONG).show();
                Intent intent = new Intent(Register.this, Login.class);
                intent.putExtra("registeredEmail", etEmail.getText().toString().trim());
                startActivity(intent);
                overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_left);
                finish();
            } else {
                Toast.makeText(Register.this, "Erreur lors de l'inscription", Toast.LENGTH_LONG).show();
            }
        }
    }
}
