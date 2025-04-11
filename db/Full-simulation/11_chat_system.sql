-- Initialize Chat system
INSERT INTO Chat (Name, Creation_Date) VALUES
('Helldivers II Global', CURRENT_TIMESTAMP),
('Elden Ring Community', CURRENT_TIMESTAMP),
('Dark Souls III Veterans', CURRENT_TIMESTAMP),
('CS:GO 2 Tactical', CURRENT_TIMESTAMP),
('Baldurs Gate 3 RPers', CURRENT_TIMESTAMP);

-- Link chats to creators
INSERT INTO ChatCreator (UserID, ChatID)
SELECT 
    (SELECT ID FROM User ORDER BY RAND() LIMIT 1),
    ID 
FROM Chat;

-- Sample messages
INSERT INTO Message (UserID, Content, Date) VALUES
((SELECT ID FROM User ORDER BY RAND() LIMIT 1), 'Welcome to Super Earth!', CURRENT_TIMESTAMP),
((SELECT ID FROM User ORDER BY RAND() LIMIT 1), 'For Democracy!', CURRENT_TIMESTAMP),
((SELECT ID FROM User ORDER BY RAND() LIMIT 1), 'Need help with Malenia', CURRENT_TIMESTAMP),
((SELECT ID FROM User ORDER BY RAND() LIMIT 1), 'Looking for team', CURRENT_TIMESTAMP);

-- Link messages to chats
INSERT INTO ChatMessage (ChatID, MessageID)
SELECT 
    (SELECT ID FROM Chat ORDER BY RAND() LIMIT 1),
    ID 
FROM Message;