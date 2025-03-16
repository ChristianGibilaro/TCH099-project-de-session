package com.example.lab1;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;

public class DevinerSomme extends AppCompatActivity implements View.OnClickListener {

    public static final int PERMISSION_SMS= 100;
    private TextView identifiant, sommeTxt;
    private Button calculerSomme, valider, sms;
    private EditText entier1, entier2;
    private double somme;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_deviner_somme);

        identifiant = (TextView)findViewById(R.id.identifiant);
        sommeTxt = (TextView)findViewById(R.id.sommeTxts);
        entier1 =findViewById(R.id.entier1);
        entier2 =findViewById(R.id.entier2);

        calculerSomme = findViewById(R.id.doSomme);
        calculerSomme.setOnClickListener(this);

        valider = findViewById(R.id.valider);
        valider.setOnClickListener(this);

        sms = findViewById(R.id.sms);
        sms.setOnClickListener(this);

        Intent intent = getIntent();
        String nom = intent.getStringExtra("NOM");
        String prenom = intent.getStringExtra("PRENOM");

        identifiant.setText("Nom : "+nom+", Prénom : "+ prenom);
    }


    @Override
    public void onClick(View view) {
        if(view == calculerSomme) {
            double val1, val2;
            try {
                somme = Double.parseDouble(entier1.getText().toString())+Double.parseDouble(entier2.getText().toString());
                sommeTxt.setText(Double.toString(somme));
            }
            catch (NumberFormatException exp) {
                Toast.makeText(DevinerSomme.this, "Veuillez entrez des valeurs valides", Toast.LENGTH_SHORT).show();
            }
        } else if (view == valider) {
            somme = Double.parseDouble(entier1.getText().toString())+Double.parseDouble(entier2.getText().toString());
            Intent iSomme;
            iSomme = new Intent();
            iSomme.putExtra("RESULTAT", somme);
            Log.wtf("test","stf");
            setResult(RESULT_OK,iSomme);
            finish();
        }else if(view == sms){
            String[] permissionsAAccorder = {Manifest.permission.SEND_SMS};

            int resultat = ContextCompat.checkSelfPermission(this,
                    Manifest.permission.SEND_SMS);
            if (resultat == PackageManager.PERMISSION_GRANTED) {

                String strTel = "smsto:4388818281";
                Uri uri = Uri.parse(strTel);

                Intent compose = new Intent(Intent.ACTION_SENDTO, uri);
                compose.putExtra("sms_body", "Somme envoyé: " + Double.toString(somme));
                Toast.makeText(DevinerSomme.this, "Message Envoyé", Toast.LENGTH_SHORT).show();
                startActivity(compose);
            }
            else{
                requestPermissions(permissionsAAccorder,PERMISSION_SMS);
            }


        }
    }
}