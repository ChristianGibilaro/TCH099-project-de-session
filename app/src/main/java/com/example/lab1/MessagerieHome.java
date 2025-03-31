package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageButton;
import android.widget.Toast;

public class MessagerieHome extends AppCompatActivity {

    private static final int REQUEST_CREATE_CONVO = 1;
    private RecyclerView convoList;
    private ConvoAdapter adapter;
    private ImageButton btnCreateConvo;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_home);

        btnCreateConvo = findViewById(R.id.btnCreateConvo);
        convoList = findViewById(R.id.convoList);

        // Setup RecyclerView as a vertical list (1 column)
        convoList.setLayoutManager(new GridLayoutManager(this, 1));
        adapter = new ConvoAdapter();
        convoList.setAdapter(adapter);

        // Set conversation click listener
        adapter.setOnConvoClickListener(new ConvoAdapter.OnConvoClickListener() {
            @Override
            public void onConvoClick(Conversation conversation) {
                // Only react if a conversation exists (i.e. conversation is not null)
                Intent intent = new Intent(MessagerieHome.this, MessagerieChat.class);
                intent.putExtra("convoName", conversation.getName());
                intent.putExtra("convoMembers", conversation.getMembers());
                startActivity(intent);
            }
        });

        // When "Create Conversation" button is clicked, open MessagerieCreate.
        btnCreateConvo.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MessagerieHome.this, MessagerieCreate.class);
                startActivityForResult(intent, REQUEST_CREATE_CONVO);
            }
        });
        int marginInPixels = getResources().getDimensionPixelSize(R.dimen.item_margin);
        convoList.addItemDecoration(new MarginItemDecoration(marginInPixels));

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_CREATE_CONVO && resultCode == RESULT_OK) {
            String convoName = data.getStringExtra("convoName");
            String convoMembers = data.getStringExtra("convoMembers");
            if (convoName != null && !convoName.isEmpty() &&
                    convoMembers != null && !convoMembers.isEmpty()) {
                Conversation newConvo = new Conversation(convoName, convoMembers);
                // Add the conversation in the next empty cell.
                if (!adapter.addConversation(newConvo)) {
                    // If no empty cell, add a new row (one cell in this 1-column grid)
                    adapter.addRow();
                    adapter.addConversation(newConvo);
                }
            } else {
                Toast.makeText(this, "Invalid conversation data.", Toast.LENGTH_SHORT).show();
            }
        }
        super.onActivityResult(requestCode, resultCode, data);
    }
}
