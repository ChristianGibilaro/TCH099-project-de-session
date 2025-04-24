package com.example.lab1;

import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import java.util.ArrayList;
import java.util.List;

public class ConvoAdapter extends RecyclerView.Adapter<ConvoAdapter.ConvoViewHolder> {

    // Liste de Conversation
    private final List<Conversation> convoList = new ArrayList<>();

    public interface OnConvoClickListener {
        void onConvoClick(Conversation conversation);
    }

    private OnConvoClickListener listener;

    public ConvoAdapter() {
        // aucun pré-remplissage : on n'affiche que les conversations existantes
    }

    public void setOnConvoClickListener(OnConvoClickListener listener) {
        this.listener = listener;
    }

    /**
     * Charge la liste complète des conversations à afficher
     */
    public void setConversations(List<Conversation> list) {
        convoList.clear();
        convoList.addAll(list);
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ConvoViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_convo, parent, false);
        return new ConvoViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ConvoViewHolder holder, int position) {
        Conversation convo = convoList.get(position);
        String url = convo.getImgUrl();  // from JSON “creatorImg”
        Log.d("ConvoAdapter", "Loading image for “" + convo.getName() + "”: " + url);

        if (url == null || url.trim().isEmpty() || url.equals("pending")) {
            // no valid URL → default avatar
            holder.imgConvoProfile.setImageResource(R.drawable.defaultaccount);
        } else {
            // build full URL if needed
            String fullUrl = url.startsWith("http")
                    ? url
                    : "http://10.0.2.2:9999" + (url.startsWith("/") ? url : "/" + url);

            Glide.with(holder.imgConvoProfile.getContext())
                    .load(fullUrl)
                    .placeholder(R.drawable.defaultaccount)
                    .error(R.drawable.defaultaccount)
                    .into(holder.imgConvoProfile);
        }

        holder.tvConvoName.setText(convo.getName());
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) listener.onConvoClick(convo);
        });
    }


    @Override
    public int getItemCount() {
        return convoList.size();
    }

    static class ConvoViewHolder extends RecyclerView.ViewHolder {
        ImageView imgConvoProfile;
        TextView tvConvoName;

        public ConvoViewHolder(@NonNull View itemView) {
            super(itemView);
            imgConvoProfile = itemView.findViewById(R.id.imgConvoProfile);
            tvConvoName = itemView.findViewById(R.id.tvConvoName);
        }
    }
}
