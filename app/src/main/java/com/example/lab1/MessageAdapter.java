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
    private final List<Message> messages    = new ArrayList<>();
    private final List<String> senderNames  = new ArrayList<>();



    /** New API: pass in the name too */
    public void addMessage(Message message, String senderName) {
        messages.add(message);
        senderNames.add(senderName);
        notifyItemInserted(messages.size()-1);
    }

    @NonNull @Override
    public MessageViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_message, parent, false);
        return new MessageViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull MessageViewHolder holder, int pos) {
        holder.tvSenderName.setText(senderNames.get(pos));
        holder.tvMessageContent.setText(messages.get(pos).getContent());
        // imgSender left as-is
    }

    @Override
    public int getItemCount() {
        return messages.size();
    }

    static class MessageViewHolder extends RecyclerView.ViewHolder {
        ImageView imgSender;
        TextView tvSenderName, tvMessageContent;

        public MessageViewHolder(@NonNull View itemView) {
            super(itemView);
            imgSender         = itemView.findViewById(R.id.imgSender);
            tvSenderName      = itemView.findViewById(R.id.tvSenderName);
            tvMessageContent  = itemView.findViewById(R.id.tvMessageContent);
        }
    }
}
