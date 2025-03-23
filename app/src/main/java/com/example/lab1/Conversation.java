package com.example.lab1;

public class Conversation {
    private String name;
    private String members; // Comma-separated emails

    public Conversation(String name, String members) {
        this.name = name;
        this.members = members;
    }

    public String getName() {
        return name;
    }

    public String getMembers() {
        return members;
    }
}
