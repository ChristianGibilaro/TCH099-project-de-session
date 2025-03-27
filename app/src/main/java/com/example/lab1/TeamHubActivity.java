package com.example.lab1;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;

public class TeamHubActivity extends AppCompatActivity {

    private TextView tvActivityTitle, tvWelcome, tvDescription;
    private LinearLayout btnQuickTeams, btnLegions, btnQuickTeams2;
    private TextView tvQuickTeams, tvLegions, tvQuickTeams2Text, tvPlayersReady;
    private TextView tvName, tvDifficulty, tvPartyCount;
    private ImageView imgBanner, imgPlayerIcon;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_team_hub); // Updated XML file

        // Banner
        imgBanner = findViewById(R.id.imgBanner);
        tvActivityTitle = findViewById(R.id.tvActivityTitle);

        // Welcome and description
        tvWelcome = findViewById(R.id.tvWelcome);
        tvDescription = findViewById(R.id.tvDescription);

        // Top buttons
        btnQuickTeams = findViewById(R.id.btnQuickTeams);
        tvQuickTeams = findViewById(R.id.tvQuickTeams);
        btnLegions = findViewById(R.id.btnLegions);
        tvLegions = findViewById(R.id.tvLegions);

        // Bottom section
        btnQuickTeams2 = findViewById(R.id.btnQuickTeams2);
        tvQuickTeams2Text = findViewById(R.id.tvQuickTeams2);
        tvPlayersReady = findViewById(R.id.tvPlayersReady);

        // Header row
        tvName = findViewById(R.id.tvName);
        tvDifficulty = findViewById(R.id.tvDifficulty);
        tvPartyCount = findViewById(R.id.tvPartyCount);
        imgPlayerIcon = findViewById(R.id.imgPlayerIcon);

        // Example usage: set some text programmatically
        tvActivityTitle.setText("ACTIVITY TITLE");
        tvWelcome.setText("Welcome to the ACTIVITY TITLE team hub,");
        tvDescription.setText("Description-text-not-int\n-----------\n------");

        // Example onClick for Quick Teams button
        btnQuickTeams.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // TODO: handle Quick Teams
            }
        });

        // Example onClick for Legions button
        btnLegions.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // TODO: handle Legions
            }
        });

        // Example onClick for second Quick Teams button
        btnQuickTeams2.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // TODO: handle second Quick Teams
            }
        });
    }
}
