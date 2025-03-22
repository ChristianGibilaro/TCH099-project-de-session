package com.example.lab1;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;

public class PhotoAdapter extends RecyclerView.Adapter<PhotoAdapter.PhotoViewHolder> {

    // By default, 3 wide x 4 high = 12 squares
    private static final int DEFAULT_ROWS = 4;
    private static final int COLS = 3;

    // photoList holds an integer resource ID for each cell.
    // If the value is 0, the cell is considered empty.
    private final List<Integer> photoList = new ArrayList<>();

    public PhotoAdapter() {
        // Initialize with 12 empty cells
        int totalCells = DEFAULT_ROWS * COLS; // 4 rows * 3 columns = 12
        for (int i = 0; i < totalCells; i++) {
            photoList.add(0); // 0 means empty.
        }
    }

    @NonNull
    @Override
    public PhotoViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_photo, parent, false);
        return new PhotoViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull PhotoViewHolder holder, int position) {
        int resourceId = photoList.get(position);
        if (resourceId == 0) {
            // Empty cell
            holder.imgPhoto.setImageDrawable(null);
        } else {
            holder.imgPhoto.setImageResource(resourceId);
        }

        // Click events if needed
        holder.imgPhoto.setOnClickListener(v -> {
            if (resourceId == 0) {
                // The cell is empty
            } else {
                // The cell has an image
            }
        });
    }

    @Override
    public int getItemCount() {
        return photoList.size();
    }

    // Returns the first empty cell index, or -1 if full
    public int getNextEmptySquare() {
        for (int i = 0; i < photoList.size(); i++) {
            if (photoList.get(i) == 0) {
                return i;
            }
        }
        return -1;
    }

    // If the grid is full, we can add a new row of 3 empty cells
    public void addRow() {
        for (int i = 0; i < COLS; i++) {
            photoList.add(0); // 0 => empty
        }
        // Notify the adapter that 3 new items were inserted
        notifyItemRangeInserted(photoList.size() - COLS, COLS);
    }

    // Helper method to set an image in a specific cell
    public void setImageAtPosition(int position, int resourceId) {
        if (position >= 0 && position < photoList.size()) {
            photoList.set(position, resourceId);
            notifyItemChanged(position);
        }
    }

    // Helper method to clear an image (make cell empty)
    public void clearImageAtPosition(int position) {
        if (position >= 0 && position < photoList.size()) {
            photoList.set(position, 0);
            notifyItemChanged(position);
        }
    }

    static class PhotoViewHolder extends RecyclerView.ViewHolder {
        ImageView imgPhoto;
        public PhotoViewHolder(@NonNull View itemView) {
            super(itemView);
            imgPhoto = itemView.findViewById(R.id.imgPhoto);
        }
    }
}
