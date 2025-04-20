package com.example.lab1;

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

    // Liste de Conversation; null indique une cellule vide
    private final List<Conversation> convoList = new ArrayList<>();

    public ConvoAdapter() {
        // Initialise 8 cellules vides
        for (int i = 0; i < 8; i++) {
            convoList.add(null);
        }
    }

    public interface OnConvoClickListener {
        void onConvoClick(Conversation conversation);
    }

    private OnConvoClickListener listener;

    public void setOnConvoClickListener(OnConvoClickListener listener) {
        this.listener = listener;
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
        if (convo == null) {
            // Cellule vide
            holder.imgConvoProfile.setImageDrawable(null);
            holder.tvConvoName.setText("");
        } else {
            // Charge l'avatar via Glide
            Glide.with(holder.imgConvoProfile.getContext())
                    .load(convo.getImgUrl())
                    .placeholder(R.drawable.defaultaccount)
                    .into(holder.imgConvoProfile);
            holder.tvConvoName.setText(convo.getName());
        }

        holder.itemView.setOnClickListener(v -> {
            if (convo != null && listener != null) {
                listener.onConvoClick(convo);
            }
        });
    }

    @Override
    public int getItemCount() {
        return convoList.size();
    }

    /**
     * Vide la liste et réinitialise à 8 cellules vides.
     */
    public void clear() {
        convoList.clear();
        for (int i = 0; i < 8; i++) {
            convoList.add(null);
        }
        notifyDataSetChanged();
    }

    /**
     * Ajoute une Conversation dans la prochaine cellule vide.
     * @return true si inséré, false sinon
     */
    public boolean addConversation(Conversation conversation) {
        for (int i = 0; i < convoList.size(); i++) {
            if (convoList.get(i) == null) {
                convoList.set(i, conversation);
                notifyItemChanged(i);
                return true;
            }
        }
        return false;
    }

    /** Ajoute une nouvelle cellule (colonne unique) */
    public void addRow() {
        convoList.add(null);
        notifyItemInserted(convoList.size() - 1);
    }

    /** Renvoie l'index de la prochaine cellule vide, ou -1 si aucune. */
    public int getNextEmptySquare() {
        for (int i = 0; i < convoList.size(); i++) {
            if (convoList.get(i) == null) return i;
        }
        return -1;
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
