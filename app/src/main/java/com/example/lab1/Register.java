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
import android.util.Log;
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

    private static final String TAG = "Register";
    private static final int PICK_IMAGE_REQUEST = 1;

    private EditText etPseudonyme,
            etNom,
            etPrenom,
            etEmail,
            etPassword,
            etConfirmPassword;
    private ImageView ivProfilePicker,
            ivProfilePreview;
    private CheckBox cbReglement;
    private Button btnRegister;
    private Uri imageUri;

    // Always send language_id = 1
    private static final String LANGUAGE_ID = "1";
    private final String REGISTER_URL = "http://10.0.2.2:9999/api/user/create";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        etPseudonyme      = findViewById(R.id.etPseudonyme);
        etNom             = findViewById(R.id.etNom);
        etPrenom          = findViewById(R.id.etPrenom);
        etEmail           = findViewById(R.id.etEmail);
        etPassword        = findViewById(R.id.etPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);
        ivProfilePicker   = findViewById(R.id.ivProfilePicker);
        ivProfilePreview  = findViewById(R.id.ivProfilePreview);
        cbReglement       = findViewById(R.id.cbReglement);
        btnRegister       = findViewById(R.id.btnRegister);

        ivProfilePicker.setOnClickListener(v -> {
            Log.d(TAG, "Profile picker clicked, opening gallery");
            openGallery();
        });

        btnRegister.setOnClickListener(v -> {
            Log.d(TAG, "Register button clicked");
            registerUser();
        });
    }

    private void openGallery() {
        Intent intent = new Intent(
                Intent.ACTION_PICK,
                MediaStore.Images.Media.EXTERNAL_CONTENT_URI
        );
        startActivityForResult(intent, PICK_IMAGE_REQUEST);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == PICK_IMAGE_REQUEST &&
                resultCode  == RESULT_OK &&
                data        != null) {
            imageUri = data.getData();
            Log.d(TAG, "Image URI selected: " + imageUri);
            try {
                Bitmap bitmap = MediaStore.Images.Media.getBitmap(
                        getContentResolver(), imageUri
                );
                ivProfilePreview.setImageBitmap(bitmap);
                ivProfilePreview.setVisibility(View.VISIBLE);
                Log.d(TAG, "Profile preview updated");
            } catch (IOException e) {
                Log.e(TAG, "Error loading selected image", e);
            }
        }
    }

    private void registerUser() {
        String pseudonym      = etPseudonyme.getText().toString().trim();
        String nomPart        = etNom.getText().toString().trim();
        String prenomPart     = etPrenom.getText().toString().trim();
        String email          = etEmail.getText().toString().trim();
        String password       = etPassword.getText().toString();
        String confirmPassword= etConfirmPassword.getText().toString();
        String agreement      = cbReglement.isChecked() ? "accepted" : "";

        Log.d(TAG, "Inputs: pseudonym=" + pseudonym +
                ", nom=" + nomPart +
                ", prenom=" + prenomPart +
                ", email=" + email +
                ", agreement=" + agreement +
                ", language_id=" + LANGUAGE_ID
        );

        if (TextUtils.isEmpty(pseudonym) ||
                TextUtils.isEmpty(nomPart)   ||
                TextUtils.isEmpty(prenomPart)||
                TextUtils.isEmpty(email)     ||
                TextUtils.isEmpty(password)  ||
                TextUtils.isEmpty(confirmPassword)
        ) {
            Toast.makeText(this,
                    "Veuillez remplir tous les champs obligatoires.",
                    Toast.LENGTH_SHORT).show();
            Log.w(TAG, "Validation failed: required field missing");
            return;
        }

        if (!password.equals(confirmPassword)) {
            Toast.makeText(this,
                    "Les mots de passe ne correspondent pas.",
                    Toast.LENGTH_SHORT).show();
            Log.w(TAG, "Validation failed: passwords do not match");
            return;
        }

        if (!"accepted".equals(agreement)) {
            Toast.makeText(this,
                    "Veuillez accepter le règlement.",
                    Toast.LENGTH_SHORT).show();
            Log.w(TAG, "Validation failed: agreement not accepted");
            return;
        }

        // Combine nom + prenom into single "nom" field for the API
        String fullName    = nomPart + " " + prenomPart;
        String description = "Aucune description fournie.";
        String age         = "1970-01-01";

        Log.d(TAG, "All validation passed, launching RegisterTask");
        new RegisterTask().execute(
                pseudonym,
                fullName,
                email,
                password,
                confirmPassword,
                description,
                age,
                agreement,
                LANGUAGE_ID
        );
    }

    private class RegisterTask extends AsyncTask<String, Void, String> {
        private static final String TASK_TAG = "RegisterTask";
        private final String boundary   =
                "----WebKitFormBoundary" + System.currentTimeMillis();
        private final String lineEnd    = "\r\n";
        private final String twoHyphens = "--";

        @Override
        protected String doInBackground(String... params) {
            String pseudonym   = params[0];
            String nom         = params[1];
            String email       = params[2];
            String password    = params[3];
            String password2   = params[4];
            String description = params[5];
            String age         = params[6];
            String agreement   = params[7];
            String languageId  = params[8];

            Log.d(TASK_TAG, "doInBackground start");
            HttpURLConnection conn = null;
            DataOutputStream dos   = null;

            try {
                URL url = new URL(REGISTER_URL);
                conn = (HttpURLConnection) url.openConnection();
                conn.setDoInput(true);
                conn.setDoOutput(true);
                conn.setUseCaches(false);
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Connection", "Keep-Alive");
                conn.setRequestProperty("Cache-Control", "no-cache");
                conn.setRequestProperty(
                        "Content-Type",
                        "multipart/form-data;boundary=" + boundary
                );
                Log.d(TASK_TAG, "Connection opened to " + REGISTER_URL);

                dos = new DataOutputStream(conn.getOutputStream());

                // Required form-data fields
                writeFormField(dos, "pseudonym",   pseudonym);
                writeFormField(dos, "nom",         nom);
                writeFormField(dos, "email",       email);
                writeFormField(dos, "password",    password);
                writeFormField(dos, "password2",   password2);
                writeFormField(dos, "description", description);
                writeFormField(dos, "language_id", languageId);
                writeFormField(dos, "age",         age);
                writeFormField(dos, "agreement",   agreement);

                // Image: use selected file if any, otherwise default drawable
                if (imageUri != null) {
                    Log.d(TASK_TAG, "Uploading selected image file");
                    InputStream iStream =
                            getContentResolver().openInputStream(imageUri);
                    byte[] fileData = getBytes(iStream);
                    String fileName = "img_" + System.currentTimeMillis() + ".png";
                    writeFileField(dos, "image", fileName, "image/png", fileData);
                } else {
                    Log.d(TASK_TAG, "No image selected, using default drawable");
                    Resources res = Register.this.getResources();
                    Bitmap bitmap = BitmapFactory.decodeResource(
                            res, R.drawable.defaultaccount
                    );
                    ByteArrayOutputStream baos = new ByteArrayOutputStream();
                    bitmap.compress(Bitmap.CompressFormat.PNG, 100, baos);
                    byte[] fileData = baos.toByteArray();
                    writeFileField(
                            dos, "image", "defaultaccount.png", "image/png", fileData
                    );
                }

                // Finish multipart request
                dos.writeBytes(twoHyphens + boundary + twoHyphens + lineEnd);
                dos.flush();
                dos.close();
                Log.d(TASK_TAG, "Request sent, awaiting response");

                int responseCode = conn.getResponseCode();
                Log.d(TASK_TAG, "Response code: " + responseCode);

                InputStream is = (responseCode == HttpURLConnection.HTTP_OK)
                        ? conn.getInputStream()
                        : conn.getErrorStream();

                BufferedReader reader = new BufferedReader(
                        new InputStreamReader(is)
                );
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();

                Log.d(TASK_TAG, "Response payload: " + response);
                return response.toString();

            } catch (Exception e) {
                Log.e(TASK_TAG, "Network error", e);
            } finally {
                if (conn != null) {
                    conn.disconnect();
                    Log.d(TASK_TAG, "Connection closed");
                }
            }
            return null;
        }

        private void writeFormField(
                DataOutputStream dos,
                String fieldName,
                String fieldValue
        ) throws IOException {
            Log.d(TASK_TAG,
                    "Writing field '" + fieldName + "' = '" + fieldValue + "'");
            dos.writeBytes(twoHyphens + boundary + lineEnd);
            dos.writeBytes(
                    "Content-Disposition: form-data; name=\"" +
                            fieldName + "\"" + lineEnd
            );
            dos.writeBytes("Content-Type: text/plain; charset=UTF-8" + lineEnd);
            dos.writeBytes(lineEnd);
            dos.writeBytes(fieldValue + lineEnd);
        }

        private void writeFileField(
                DataOutputStream dos,
                String fieldName,
                String fileName,
                String mimeType,
                byte[] fileData
        ) throws IOException {
            Log.d(TASK_TAG,
                    "Writing file '" + fieldName + "' filename='" + fileName + "'");
            dos.writeBytes(twoHyphens + boundary + lineEnd);
            dos.writeBytes(
                    "Content-Disposition: form-data; name=\"" +
                            fieldName +
                            "\"; filename=\"" +
                            fileName +
                            "\"" + lineEnd
            );
            dos.writeBytes("Content-Type: " + mimeType + lineEnd);
            dos.writeBytes("Content-Transfer-Encoding: binary" + lineEnd);
            dos.writeBytes(lineEnd);
            dos.write(fileData);
            dos.writeBytes(lineEnd);
        }

        private byte[] getBytes(InputStream is) throws IOException {
            ByteArrayOutputStream buffer = new ByteArrayOutputStream();
            int nRead;
            byte[] data = new byte[1024];
            while ((nRead = is.read(data)) != -1) {
                buffer.write(data, 0, nRead);
            }
            return buffer.toByteArray();
        }

        @Override
        protected void onPostExecute(String result) {
            Log.d(TASK_TAG, "onPostExecute: " + result);
            if (result != null) {
                Toast.makeText(
                        Register.this,
                        "Inscription réussie",
                        Toast.LENGTH_LONG
                ).show();
                Intent intent = new Intent(Register.this, Login.class);
                intent.putExtra(
                        "registeredEmail",
                        etEmail.getText().toString()
                );
                startActivity(intent);
                finish();
            } else {
                Toast.makeText(
                        Register.this,
                        "Erreur lors de l'inscription",
                        Toast.LENGTH_LONG
                ).show();
            }
        }
    }
}
