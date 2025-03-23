package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

public class MessagerieCreate extends AppCompatActivity {

    private EditText etConvoName, etParticipants;
    private Button btnCreate;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_create);

        etConvoName = findViewById(R.id.etConvoName);
        etParticipants = findViewById(R.id.etParticipants);
        btnCreate = findViewById(R.id.btnCreate);

        btnCreate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String name = etConvoName.getText().toString().trim();
                String members = etParticipants.getText().toString().trim();

                if(name.isEmpty() || members.isEmpty()){
                    Toast.makeText(MessagerieCreate.this, "Please fill in both conversation name and members.", Toast.LENGTH_SHORT).show();
                    return;
                }
                // Return the conversation details
                Intent resultIntent = new Intent();
                resultIntent.putExtra("convoName", name);
                resultIntent.putExtra("convoMembers", members);
                setResult(RESULT_OK, resultIntent);
                finish();
            }
        });
    }
}
