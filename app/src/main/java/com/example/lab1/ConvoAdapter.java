package com.example.lab1;

import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;

public class ConvoAdapter extends RecyclerView.Adapter<ConvoAdapter.ConvoViewHolder> {

    // We use a list of Conversation objects; a null value indicates an empty (blank) cell.
    private final List<Conversation> convoList = new ArrayList<>();

    public ConvoAdapter() {
        // Initialize with 8 blank cells (1 column x 8 rows)
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
            // Blank cell: show default or nothing.
            holder.imgConvoProfile.setImageDrawable(null);
            holder.tvConvoName.setText("");
        } else {
            // A conversation exists: display default image and conversation name.
            holder.imgConvoProfile.setImageResource(R.drawable.defaultaccount);
            holder.tvConvoName.setText(convo.getName());
        }

        holder.itemView.setOnClickListener(v -> {
            if (convo != null) {
                // Debug log to see which conversation is clicked.
                Log.d("ConvoAdapter", "Conversation clicked: " + convo.getName());
                if (listener != null) {
                    listener.onConvoClick(convo);
                }
            } else {
                Log.d("ConvoAdapter", "Blank cell clicked; no conversation.");
            }
        });

    }


    @Override
    public int getItemCount() {
        return convoList.size();
    }

    // Adds a conversation to the next empty cell; returns true if added.
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

    // Optionally add a new row (since our grid is 1 column, one new cell)
    public void addRow() {
        convoList.add(null);
        notifyItemInserted(convoList.size() - 1);
    }

    // Returns the index of the next empty cell, or -1 if none.
    public int getNextEmptySquare() {
        for (int i = 0; i < convoList.size(); i++) {
            if (convoList.get(i) == null) {
                return i;
            }
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
