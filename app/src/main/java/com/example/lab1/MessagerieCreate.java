package com.example.lab1;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Toast;

public class MessagerieCreate extends AppCompatActivity {

    private EditText etConversationNameCreateMessage, etMemberPseudoCreateMessage, etAdditionalInfoCreateMessage;
    private LinearLayout btnAddMemberCreateMessageContainer, layoutAddButtonCreateMessage;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messagerie_create);

        etConversationNameCreateMessage = findViewById(R.id.etConversationNameCreateMessage);
        etMemberPseudoCreateMessage = findViewById(R.id.etMemberPseudoCreateMessage);
        etAdditionalInfoCreateMessage = findViewById(R.id.etAdditionalInfoCreateMessage);
        btnAddMemberCreateMessageContainer = findViewById(R.id.btnAddMemberCreateMessageContainer);
        layoutAddButtonCreateMessage = findViewById(R.id.layoutAddButtonCreateMessage);

        // Le champ d'affichage des membres ne doit pas être éditable
        etAdditionalInfoCreateMessage.setEnabled(false);

        // Ajout du pseudo lorsqu'on clique sur le bouton d'ajout de membre
        btnAddMemberCreateMessageContainer.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String newMember = etMemberPseudoCreateMessage.getText().toString().trim();
                if (newMember.isEmpty()) {
                    Toast.makeText(MessagerieCreate.this, "Veuillez saisir un pseudo.", Toast.LENGTH_SHORT).show();
                    return;
                }
                String currentMembers = etAdditionalInfoCreateMessage.getText().toString().trim();
                String updatedMembers;
                if (currentMembers.isEmpty()) {
                    updatedMembers = newMember;
                } else {
                    updatedMembers = currentMembers + ", " + newMember;
                }
                etAdditionalInfoCreateMessage.setText(updatedMembers);
                etMemberPseudoCreateMessage.setText("");
            }
        });

        // Bouton "Ajouter" pour renvoyer les informations de la conversation
        layoutAddButtonCreateMessage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String convoName = etConversationNameCreateMessage.getText().toString().trim();
                String members = etAdditionalInfoCreateMessage.getText().toString().trim();
                if (convoName.isEmpty() || members.isEmpty()) {
                    Toast.makeText(MessagerieCreate.this, "Veuillez remplir le nom de la conversation et ajouter au moins un membre.", Toast.LENGTH_SHORT).show();
                    return;
                }
                Intent resultIntent = new Intent();
                resultIntent.putExtra("convoName", convoName);
                resultIntent.putExtra("convoMembers", members);
                setResult(RESULT_OK, resultIntent);
                finish();
            }
        });
    }
}
