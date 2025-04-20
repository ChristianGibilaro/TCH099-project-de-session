package com.example.lab1;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.Random;

public class Home extends AppCompatActivity {

    private static final String TAG = "HomeActivity";
    private ImageButton btnLogout, btnMessages;
    private ImageView btnAdd;
    private RecyclerView photoGrid;
    private PostAdapter adapter;
    private int[] randomImages = {R.drawable.image, R.drawable.logo};
    private String apiKey;  // récupère l'API key

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        // Récupère l'apiKey depuis l'Intent
        apiKey = getIntent().getStringExtra("apiKey");
        Log.d(TAG, "onCreate: apiKey reçu = " + apiKey);

        btnLogout = findViewById(R.id.btnLogout);
        btnMessages = findViewById(R.id.btnMessages);
        btnAdd = findViewById(R.id.btnAdd);
        RatingBar ratingBar = findViewById(R.id.ratingBar);
        photoGrid = findViewById(R.id.photoGrid);

        // Utiliser un LinearLayoutManager pour un affichage en "mur"
        LinearLayoutManager layoutManager = new LinearLayoutManager(this);
        photoGrid.setLayoutManager(layoutManager);

        // Création de l'adapter et affectation au RecyclerView
        adapter = new PostAdapter();
        photoGrid.setAdapter(adapter);

        // Ajout de quelques posts initiaux pour tester
        adapter.addPost(new Post(R.drawable.image, "Mon premier post"));
        adapter.addPost(new Post(R.drawable.logo, "Mon deuxième post"));

        // Bouton "Add content" : ajoute un nouveau post aléatoire
        btnAdd.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Random rand = new Random();
                int randomIndex = rand.nextInt(randomImages.length);
                int selectedImage = randomImages[randomIndex];
                String newDescription = "Post aléatoire #" + (adapter.getItemCount() + 1);
                adapter.addPost(new Post(selectedImage, newDescription));
                Log.d(TAG, "Nouveau post ajouté: " + newDescription);
            }
        });

        // Bouton Logout : retourne à la page Login
        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "Logout tapped, returning to Login");
                Intent intent = new Intent(Home.this, Login.class);
                startActivity(intent);
                finish();
            }
        });

        // Bouton Messages : ouvre l'activité de messagerie en passant l'apiKey
        btnMessages.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "Messages tapped, launching MessagerieHome with apiKey");
                Intent intent = new Intent(Home.this, MessagerieHome.class);
                intent.putExtra("apiKey", apiKey);
                startActivity(intent);
            }
        });
    }

    // Modèle de publication (Post)
    private static class Post {
        private int imageResId;
        private String description;

        public Post(int imageResId, String description) {
            this.imageResId = imageResId;
            this.description = description;
        }

        public int getImageResId() {
            return imageResId;
        }

        public String getDescription() {
            return description;
        }
    }

    // Adapter pour gérer le mur de publications
    private class PostAdapter extends RecyclerView.Adapter<PostAdapter.PostViewHolder> {

        private ArrayList<Post> postList = new ArrayList<>();

        public void addPost(Post post) {
            postList.add(post);
            notifyItemInserted(postList.size() - 1);
        }

        @NonNull
        @Override
        public PostViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_post, parent, false);
            return new PostViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull PostViewHolder holder, int position) {
            Post currentPost = postList.get(position);
            holder.postImage.setImageResource(currentPost.getImageResId());
            holder.postDescription.setText(currentPost.getDescription());
        }

        @Override
        public int getItemCount() {
            return postList.size();
        }

        class PostViewHolder extends RecyclerView.ViewHolder {
            ImageView postImage;
            TextView postDescription;

            public PostViewHolder(@NonNull View itemView) {
                super(itemView);
                postImage = itemView.findViewById(R.id.postImage);
                postDescription = itemView.findViewById(R.id.postDescription);
            }
        }
    }
}
