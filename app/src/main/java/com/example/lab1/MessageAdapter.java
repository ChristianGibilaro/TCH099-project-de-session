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

public class MessageAdapter extends RecyclerView.Adapter<MessageAdapter.MessageViewHolder> {
    private final List<Message> messages      = new ArrayList<>();
    private final List<String>  senderNames   = new ArrayList<>();
    private final List<String>  senderImgUrls = new ArrayList<>();

    /** add a brand-new message */
    public void addMessage(Message message, String senderName, String senderImgUrl) {
        messages.add(message);
        senderNames.add(senderName);
        senderImgUrls.add(senderImgUrl);
        notifyItemInserted(messages.size() - 1);
    }

    /** update the name+image for an existing message (e.g. placeholder) */
    public void updateMessageInfo(int position, String senderName, String senderImgUrl) {
        senderNames.set(position, senderName);
        senderImgUrls.set(position, senderImgUrl);
        notifyItemChanged(position);
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

        String url = senderImgUrls.get(pos);
        Glide.with(holder.imgSender.getContext())
                .load(url)
                .placeholder(R.drawable.defaultaccount)
                .error(R.drawable.defaultaccount)
                .circleCrop()
                .into(holder.imgSender);
    }

    @Override
    public int getItemCount() {
        return messages.size();
    }

    static class MessageViewHolder extends RecyclerView.ViewHolder {
        ImageView imgSender;
        TextView  tvSenderName, tvMessageContent;

        public MessageViewHolder(@NonNull View itemView) {
            super(itemView);
            imgSender        = itemView.findViewById(R.id.imgSender);
            tvSenderName     = itemView.findViewById(R.id.tvSenderName);
            tvMessageContent = itemView.findViewById(R.id.tvMessageContent);
        }
    }
}
