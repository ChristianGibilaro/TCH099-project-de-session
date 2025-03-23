package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.TextView;

public class MessagerieChat extends AppCompatActivity {

    private TextView tvConvoName;
    private RecyclerView recyclerMessages;
    private ImageButton btnBack, btnSend;
    private EditText etMessage;
    private MessageAdapter messageAdapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_chat);

        tvConvoName = findViewById(R.id.tvConvoName);
        recyclerMessages = findViewById(R.id.recyclerMessages);
        btnBack = findViewById(R.id.btnBack);
        btnSend = findViewById(R.id.btnSend);
        etMessage = findViewById(R.id.etMessage);

        // Get conversation name from intent
        String convoName = getIntent().getStringExtra("convoName");
        if (convoName != null && !convoName.isEmpty()) {
            tvConvoName.setText(convoName);
        }

        // Setup RecyclerView for messages
        recyclerMessages.setLayoutManager(new LinearLayoutManager(this));
        messageAdapter = new MessageAdapter();
        recyclerMessages.setAdapter(messageAdapter);

        // Back button finishes activity
        btnBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });

        // Send button: add message to chat and clear input
        btnSend.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String text = etMessage.getText().toString().trim();
                if (!text.isEmpty()) {
                    messageAdapter.addMessage(new Message(text));
                    etMessage.setText("");
                    // Scroll to the bottom after adding the message
                    recyclerMessages.scrollToPosition(messageAdapter.getItemCount() - 1);
                }
            }
        });
    }
}
