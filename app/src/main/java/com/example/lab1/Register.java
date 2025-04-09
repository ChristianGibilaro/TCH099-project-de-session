package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.content.res.Resources;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
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
import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
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
    private final String REGISTER_URL = "http://10.0.2.2:9999/api/creerUser";

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

        ivProfilePicker.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                openGallery();
            }
        });

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
        String pseudonym = etPseudonyme.getText().toString().trim();
        String nomPart = etNom.getText().toString().trim();
        String prenomPart = etPrenom.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString().trim();
        String confirmPassword = etConfirmPassword.getText().toString().trim();

        if (TextUtils.isEmpty(pseudonym) || TextUtils.isEmpty(nomPart) || TextUtils.isEmpty(prenomPart)
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
        String fullName = nomPart + " " + prenomPart;
        // Use default values for description and age as the backend expects these fields
        String description = "Aucune description fournie.";
        String age = "1970-01-01"; // You can change this default or add another field in your UI

        // Start the asynchronous registration task with all parameters
        new RegisterTask().execute(pseudonym, fullName, email, password, description, age);
    }

    // AsyncTask to perform the multipart/form-data registration request
    private class RegisterTask extends AsyncTask<String, Void, String> {

        private final String boundary = "----WebKitFormBoundary" + System.currentTimeMillis();
        private final String lineEnd = "\r\n";
        private final String twoHyphens = "--";

        @Override
        protected String doInBackground(String... params) {
            String pseudonym = params[0];
            String fullName = params[1];
            String email = params[2];
            String password = params[3];
            String description = params[4];
            String age = params[5];

            HttpURLConnection conn = null;
            DataOutputStream dos = null;
            try {
                URL url = new URL(REGISTER_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setDoInput(true);
                conn.setDoOutput(true);
                conn.setUseCaches(false);
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Connection", "Keep-Alive");
                conn.setRequestProperty("Cache-Control", "no-cache");
                conn.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);

                dos = new DataOutputStream(conn.getOutputStream());

                // Write text parts:
                writeFormField(dos, "pseudonym", pseudonym);
                writeFormField(dos, "nom", fullName);
                writeFormField(dos, "email", email);
                writeFormField(dos, "password", password);
                writeFormField(dos, "description", description);
                writeFormField(dos, "age", age);

                // Write the file part for "image".
                // If the user selected an image use it; otherwise, use the default image from resources.
                byte[] fileData;
                String fileName;
                if (imageUri != null) {
                    // Read the file from the selected image URI.
                    InputStream iStream = getContentResolver().openInputStream(imageUri);
                    fileData = getBytes(iStream);
                    fileName = "uploaded_" + System.currentTimeMillis() + ".png"; // or use the original file name
                    Log.d("RegisterTask", "Using selected image for upload");
                } else {
                    // Load the default image from resources.
                    Resources res = Register.this.getResources();
                    // Convert drawable resource to bitmap.
                    Bitmap bitmap = BitmapFactory.decodeResource(res, R.drawable.defaultaccount);
                    // Convert bitmap to byte array.
                    ByteArrayOutputStream baos = new ByteArrayOutputStream();
                    bitmap.compress(Bitmap.CompressFormat.PNG, 100, baos);
                    fileData = baos.toByteArray();
                    fileName = "defaultaccount.png";
                    Log.d("RegisterTask", "Using default image from resources");
                }

                // Write the file part.
                writeFileField(dos, "image", fileName, "image/png", fileData);

                // End of multipart/form-data.
                dos.writeBytes(twoHyphens + boundary + twoHyphens + lineEnd);
                dos.flush();
                dos.close();

                int responseCode = conn.getResponseCode();
                Log.d("RegisterTask", "Response Code: " + responseCode);

                InputStream is = (responseCode == HttpURLConnection.HTTP_OK) ? conn.getInputStream() : conn.getErrorStream();
                BufferedReader reader = new BufferedReader(new InputStreamReader(is));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();
                Log.d("RegisterTask", "Response: " + response.toString());
                return response.toString();
            } catch (Exception e) {
                Log.e("RegisterTask", "Network Error: " + e.getMessage());
                e.printStackTrace();
            } finally {
                if (conn != null) {
                    conn.disconnect();
                }
            }
            return null;
        }

        // Helper method to write form field
        private void writeFormField(DataOutputStream dos, String fieldName, String fieldValue) throws IOException {
            dos.writeBytes(twoHyphens + boundary + lineEnd);
            dos.writeBytes("Content-Disposition: form-data; name=\"" + fieldName + "\"" + lineEnd);
            dos.writeBytes("Content-Type: text/plain; charset=UTF-8" + lineEnd);
            dos.writeBytes(lineEnd);
            dos.writeBytes(fieldValue + lineEnd);
        }

        // Helper method to write file field
        private void writeFileField(DataOutputStream dos, String fieldName, String fileName, String mimeType, byte[] fileData) throws IOException {
            dos.writeBytes(twoHyphens + boundary + lineEnd);
            dos.writeBytes("Content-Disposition: form-data; name=\"" + fieldName + "\"; filename=\"" + fileName + "\"" + lineEnd);
            dos.writeBytes("Content-Type: " + mimeType + lineEnd);
            dos.writeBytes("Content-Transfer-Encoding: binary" + lineEnd);
            dos.writeBytes(lineEnd);
            dos.write(fileData);
            dos.writeBytes(lineEnd);
        }

        // Helper method to read an InputStream and return its bytes.
        private byte[] getBytes(InputStream inputStream) throws IOException {
            ByteArrayOutputStream byteBuffer = new ByteArrayOutputStream();
            int bufferSize = 1024;
            byte[] buffer = new byte[bufferSize];
            int len;
            while ((len = inputStream.read(buffer)) != -1) {
                byteBuffer.write(buffer, 0, len);
            }
            return byteBuffer.toByteArray();
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d("RegisterTask", "onPostExecute result: " + result);
            if (result != null) {
                Toast.makeText(Register.this, "Inscription réussie", Toast.LENGTH_LONG).show();
                // Optionally, navigate to Login activity
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
