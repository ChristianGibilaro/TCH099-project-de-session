package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.Toast;

import java.util.Random;

public class Home extends AppCompatActivity {

    private ImageButton btnLogout, btnMessages;
    private ImageView btnAdd; // The add button (ImageView)
    private RecyclerView photoGrid;
    private PhotoAdapter adapter;

    // Array of random image resource IDs in res/drawable temporaire pis pour tester les features
    private int[] randomImages = {R.drawable.image, R.drawable.logo};

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        btnLogout = findViewById(R.id.btnLogout);
        btnMessages = findViewById(R.id.btnMessages);
        btnAdd = findViewById(R.id.btnAdd);
        RatingBar ratingBar = findViewById(R.id.ratingBar);
        photoGrid = findViewById(R.id.photoGrid);

        // Setup RecyclerView with a 3-column grid
        GridLayoutManager gridLayoutManager = new GridLayoutManager(this, 3);
        photoGrid.setLayoutManager(gridLayoutManager);

        // Create adapter
        adapter = new PhotoAdapter();
        photoGrid.setAdapter(adapter);

        // When "Add" is clicked, place a random image in the next empty square.
        btnAdd.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                int nextEmpty = adapter.getNextEmptySquare();
                if (nextEmpty == -1) {
                    // Grid is full: add another row (3 more empty cells)
                    adapter.addRow();
                    // Now re-check the next empty
                    nextEmpty = adapter.getNextEmptySquare();
                }

                if (nextEmpty != -1) {
                    // Pick a random image from the array
                    Random rand = new Random();
                    int randomIndex = rand.nextInt(randomImages.length);
                    int selectedImage = randomImages[randomIndex];

                    // Place the image in the next empty cell
                    adapter.setImageAtPosition(nextEmpty, selectedImage);
                } else {
                    Toast.makeText(Home.this, "No empty cell, even after adding a row!", Toast.LENGTH_SHORT).show();
                }
            }
        });

        // Logout button -> go back to Login
        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(Home.this, Login.class);
                startActivity(intent);
                finish();
            }
        });

        // Messages button -> open Messagerie activity
        btnMessages.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(Home.this, MessagerieHome.class);
                startActivity(intent);
            }
        });
    }
}
