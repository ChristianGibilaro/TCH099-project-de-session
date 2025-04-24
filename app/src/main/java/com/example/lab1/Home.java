package com.example.lab1;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;

public class Home extends AppCompatActivity {
    private static final String TAG = "HomeActivity";

    private ImageButton btnLogout, btnMessages;
    private RecyclerView publicationGrid;
    private TextView tvFriendsValue, tvUsernameValue, tvDescriptionValue;
    private ImageView imgProfile;
    private PublicationAdapter pubAdapter;
    private String apiKey;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);

        // Récupère l’apiKey depuis l’Intent
        apiKey = getIntent().getStringExtra("apiKey");
        Log.d(TAG, "onCreate: apiKey reçu = " + apiKey);

        // bind views
        btnLogout          = findViewById(R.id.btnLogout);
        btnMessages        = findViewById(R.id.btnMessages);
        publicationGrid    = findViewById(R.id.photoGrid);
        tvFriendsValue     = findViewById(R.id.tvFriendsValue);
        tvUsernameValue    = findViewById(R.id.tvUsernameValue);
        tvDescriptionValue = findViewById(R.id.tvDescriptionValue);
        imgProfile         = findViewById(R.id.imgProfile);

        // charger profil, amis et publications
        new LoadProfileTask().execute(apiKey);
        new GetFriendsCountTask().execute(apiKey);
        new LoadPublicationsTask().execute();

        // setup RecyclerView
        publicationGrid.setLayoutManager(new LinearLayoutManager(this));
        pubAdapter = new PublicationAdapter();
        publicationGrid.setAdapter(pubAdapter);

        // logout
        btnLogout.setOnClickListener(v -> {
            Log.d(TAG, "Logout tapped, returning to Login");
            startActivity(new Intent(Home.this, Login.class));
            finish();
        });

        // messages
        btnMessages.setOnClickListener(v -> {
            Log.d(TAG, "Messages tapped, launching MessagerieHome");
            startActivity(new Intent(Home.this, MessagerieHome.class)
                    .putExtra("apiKey", apiKey));
        });
    }

    // --- Load user profile ---
    private class LoadProfileTask extends AsyncTask<String, Void, JSONObject> {
        @Override
        protected JSONObject doInBackground(String... params) {
            try {
                String endpoint = "http://10.0.2.2:9999/api/profile_android/"
                        + URLEncoder.encode(params[0], "UTF-8");
                Log.d(TAG, "LoadProfileTask: requesting URL = " + endpoint);

                HttpURLConnection conn = (HttpURLConnection)
                        new URL(endpoint).openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");

                int code = conn.getResponseCode();
                Log.d(TAG, "LoadProfileTask: HTTP response code = " + code);
                if (code != HttpURLConnection.HTTP_OK) {
                    conn.disconnect();
                    return null;
                }

                BufferedReader rd = new BufferedReader(
                        new InputStreamReader(conn.getInputStream())
                );
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = rd.readLine()) != null) sb.append(line);
                rd.close();
                conn.disconnect();

                Log.d(TAG, "LoadProfileTask: raw response = " + sb);
                return new JSONObject(sb.toString());
            } catch (Exception e) {
                Log.e(TAG, "Erreur LoadProfileTask", e);
                return null;
            }
        }

        @Override
        protected void onPostExecute(JSONObject json) {
            if (json == null) {
                Log.w(TAG, "LoadProfileTask: aucune donnée reçue");
                return;
            }
            String pseudo = json.optString("Pseudo", "N/A");
            String desc   = json.optString("Description", "");
            String imgUrl = json.optString("Img", "");
            Log.d(TAG, "Profil récupéré → pseudo=" + pseudo
                    + " | desc=" + desc
                    + " | imgUrl=" + imgUrl);

            tvUsernameValue.setText(pseudo);
            tvDescriptionValue.setText(desc);
            if (!imgUrl.isEmpty()) {
                Glide.with(Home.this)
                        .load(imgUrl)
                        .placeholder(R.drawable.defaultaccount)
                        .error(R.drawable.defaultaccount)
                        .circleCrop()
                        .into(imgProfile);
            }
        }
    }

    // --- Load friends count ---
    private class GetFriendsCountTask extends AsyncTask<String, Void, Integer> {
        @Override
        protected Integer doInBackground(String... params) {
            try {
                String urlStr = "http://10.0.2.2:9999/AndroidController.php"
                        + "?action=getUserTotalFriendsForAndroid"
                        + "&apiKey=" + URLEncoder.encode(params[0], "UTF-8");
                HttpURLConnection conn = (HttpURLConnection)
                        new URL(urlStr).openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");

                if (conn.getResponseCode() != HttpURLConnection.HTTP_OK) {
                    conn.disconnect();
                    return 0;
                }

                BufferedReader rd = new BufferedReader(
                        new InputStreamReader(conn.getInputStream())
                );
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = rd.readLine()) != null) sb.append(line);
                rd.close();
                conn.disconnect();

                JSONObject json = new JSONObject(sb.toString());
                return json.optInt("total_friends", 0);
            } catch (Exception e) {
                Log.e(TAG, "Erreur GetFriendsCountTask", e);
                return 0;
            }
        }

        @Override
        protected void onPostExecute(Integer count) {
            Log.d(TAG, "Nombre d'amis = " + count);
            tvFriendsValue.setText(String.valueOf(Math.max(0, count)));
        }
    }

    // --- Load publications (activity images) ---
    private class LoadPublicationsTask extends AsyncTask<Void, Void, List<Publication>> {
        @Override
        protected List<Publication> doInBackground(Void... unused) {
            List<Publication> out = new ArrayList<>();
            try {
                String endpoint = "http://10.0.2.2:9999/api/activity/images";
                Log.d(TAG, "LoadPublicationsTask: requesting URL = " + endpoint);

                HttpURLConnection conn = (HttpURLConnection)
                        new URL(endpoint).openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");

                if (conn.getResponseCode() != HttpURLConnection.HTTP_OK) {
                    conn.disconnect();
                    return out;
                }

                BufferedReader rd = new BufferedReader(
                        new InputStreamReader(conn.getInputStream())
                );
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = rd.readLine()) != null) sb.append(line);
                rd.close();
                conn.disconnect();

                JSONObject root = new JSONObject(sb.toString());
                if (root.optBoolean("success", false)) {
                    JSONArray arr = root.optJSONArray("images");
                    for (int i = 0; i < arr.length(); i++) {
                        String url = arr.getString(i);
                        String name = url.substring(url.lastIndexOf('/') + 1);
                        out.add(new Publication(url, name));
                    }
                }
            } catch (Exception e) {
                Log.e(TAG, "Erreur LoadPublicationsTask", e);
            }
            return out;
        }

        @Override
        protected void onPostExecute(List<Publication> pubs) {
            pubAdapter.setPublications(pubs);
        }
    }

    // --- Publication model ---
    private static class Publication {
        final String imageUrl;
        final String name;
        Publication(String imageUrl, String name) {
            this.imageUrl = imageUrl;
            this.name     = name;
        }
    }

    // --- Adapter for publications ---
    private class PublicationAdapter extends RecyclerView.Adapter<PublicationAdapter.ViewHolder> {
        private final List<Publication> list = new ArrayList<>();

        void setPublications(List<Publication> pubs) {
            list.clear();
            list.addAll(pubs);
            notifyDataSetChanged();
        }

        @NonNull @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View v = getLayoutInflater().inflate(R.layout.item_post, parent, false);
            return new ViewHolder(v);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int pos) {
            Publication p = list.get(pos);
            holder.description.setText(p.name);
            Glide.with(Home.this)
                    .load(p.imageUrl)
                    .placeholder(R.drawable.image)
                    .error(R.drawable.image)
                    .into(holder.image);
        }

        @Override
        public int getItemCount() {
            return list.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {
            ImageView image;
            TextView  description;
            ViewHolder(@NonNull View itemView) {
                super(itemView);
                image       = itemView.findViewById(R.id.postImage);
                description = itemView.findViewById(R.id.postDescription);
            }
        }
    }
}
