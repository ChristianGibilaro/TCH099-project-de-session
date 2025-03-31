package com.example.lab1;

import android.graphics.Rect;
import android.view.View;

import androidx.recyclerview.widget.RecyclerView;

public class MarginItemDecoration extends RecyclerView.ItemDecoration {

    private final int margin;

    public MarginItemDecoration(int margin) {
        this.margin = margin;
    }

    @Override
    public void getItemOffsets(Rect outRect, View view, RecyclerView parent, RecyclerView.State state) {
        // Applique la marge à gauche, droite et en bas
        outRect.left = margin;
        outRect.right = margin;
        outRect.bottom = margin;
        // Optionnel : ajoute une marge en haut pour le premier élément
        if (parent.getChildAdapterPosition(view) == 0) {
            outRect.top = margin;
        } else {
            outRect.top = 0;
        }
    }
}
