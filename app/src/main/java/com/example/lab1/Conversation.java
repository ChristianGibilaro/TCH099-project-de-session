package com.example.lab1;

public class Conversation {
    private String id;
    private String name;
    private String imgUrl;

    public Conversation(String id, String name, String imgUrl) {
        this.id = id;
        this.name = name;
        this.imgUrl = imgUrl;
    }

    /**
     * Retourne l'ID unique de la conversation (chatID).
     */
    public String getId() {
        return id;
    }

    /**
     * Retourne le nom de la conversation.
     */
    public String getName() {
        return name;
    }

    /**
     * Retourne l'URL de l'avatar du créateur de la conversation.
     */
    public String getImgUrl() {
        return imgUrl;
    }
}
