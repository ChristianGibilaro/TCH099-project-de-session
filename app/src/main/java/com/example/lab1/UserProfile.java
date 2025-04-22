package com.example.lab1;

public class UserProfile {
    private String name;
    private String pseudo;
    private String description;
    private String image;

    public UserProfile() {}

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getPseudo() { return pseudo; }
    public void setPseudo(String pseudo) { this.pseudo = pseudo; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getImage() { return image; }
    public void setImage(String image) { this.image = image; }
}