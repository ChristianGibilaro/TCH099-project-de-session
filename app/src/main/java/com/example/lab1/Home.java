package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageButton;
import android.widget.RatingBar;

public class Home extends AppCompatActivity {

    private ImageButton btnLogout, btnMessages;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home); // or your layout's name

        // Find bottom bar buttons
        btnLogout = findViewById(R.id.btnLogout);
        btnMessages = findViewById(R.id.btnMessages);

        // Example: If you also want to reference the RatingBar
        RatingBar ratingBar = findViewById(R.id.ratingBar);
        // ratingBar.setRating(...);


        // Logout button -> go back to LoginActivity
        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Do any logout logic (clear session, etc.)
                Intent intent = new Intent(Home.this, Login.class);
                startActivity(intent);
                finish();
            }
        });

        // Messages button -> open Messagerie activity
        btnMessages.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(Home.this, Messagerie.class);
                startActivity(intent);
            }
        });
    }
}
