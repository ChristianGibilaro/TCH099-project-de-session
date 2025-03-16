package com.example.lab1;

import androidx.activity.result.ActivityResultCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

public class MainActivity extends AppCompatActivity implements View.OnClickListener {

    private Button devinerSomme;
    private EditText nom, prenom;
    private TextView resultat;
    private ActivityResultLauncher<Intent> devinerSommeLauncher;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        nom = findViewById(R.id.nom);
        prenom = findViewById(R.id.prenom);
        devinerSomme = findViewById(R.id.devinerSomme);
        resultat = findViewById(R.id.resultat);

        devinerSomme.setOnClickListener(this);

        devinerSommeLauncher = registerForActivityResult(

                new ActivityResultContracts.StartActivityForResult(),
                new ActivityResultCallback<androidx.activity.result.ActivityResult>() {
                    @Override
                    public void onActivityResult(androidx.activity.result.ActivityResult result) {

                        if (result.getResultCode() == RESULT_OK && result.getData() != null) {

                            double sommeRtr = result.getData().getDoubleExtra("RESULTAT", 0);
                            resultat.setText(Double.toString(sommeRtr));
                        } else {
                            resultat.setText("Aucun résultat !");
                        }
                    }
                }
        );
    }

    @Override
    public void onClick(View view) {

        String nom = this.nom.getText().toString(), prenom = this.prenom.getText().toString(), regex = "^[A-Za-zÀ-ÿ\\s]+$";

        if(nom.matches("") || prenom.matches("")){
            Toast.makeText(MainActivity.this, "Veuillez rentre le nom et le prénom", Toast.LENGTH_SHORT).show();
            return;
        }else if (!nom.matches(regex) || !prenom.matches(regex)) {
            Toast.makeText(MainActivity.this, "Veuillez entrer uniquement des lettres pour le nom et le prénom", Toast.LENGTH_SHORT).show();
            return;
        }

        Intent idevinerSomme = new Intent(this, DevinerSomme.class);

        idevinerSomme.putExtra("NOM", nom);
        idevinerSomme.putExtra("PRENOM", prenom);

        devinerSommeLauncher.launch(idevinerSomme);
    }
}