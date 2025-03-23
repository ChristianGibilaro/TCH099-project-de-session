package com.example.lab1;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;

public class MessageAdapter extends RecyclerView.Adapter<MessageAdapter.MessageViewHolder> {

    // List to hold Message objects.
    private final List<Message> messages = new ArrayList<>();

    // Call this method to add a new message to the adapter.
    public void addMessage(Message message) {
        messages.add(message);
        notifyItemInserted(messages.size() - 1);
    }

    @NonNull
    @Override
    public MessageViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // Inflate our custom layout for each message item.
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_message, parent, false);
        return new MessageViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull MessageViewHolder holder, int position) {
        Message message = messages.get(position);
        // Set the message text.
        holder.tvMessageContent.setText(message.getContent());
        // The sender image is already set as default in the XML.
    }

    @Override
    public int getItemCount() {
        return messages.size();
    }

    static class MessageViewHolder extends RecyclerView.ViewHolder {
        ImageView imgSender;
        TextView tvMessageContent;

        public MessageViewHolder(@NonNull View itemView) {
            super(itemView);
            imgSender = itemView.findViewById(R.id.imgSender);
            tvMessageContent = itemView.findViewById(R.id.tvMessageContent);
        }
    }
}
