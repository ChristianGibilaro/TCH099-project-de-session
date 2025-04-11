-- Add users to teams
INSERT INTO UserTeam (UserID, TeamID, RankID, Game_Count)
SELECT 
    u.ID,
    t.ID,
    (SELECT ID FROM Rank ORDER BY RAND() LIMIT 1),
    FLOOR(RAND() * 100)
FROM User u
CROSS JOIN Team t
WHERE RAND() < 0.3;  -- 30% chance for each user to be in each team

-- Create some friend relationships
INSERT INTO UserFriend (UserID, FriendID)
SELECT u1.ID, u2.ID
FROM User u1
CROSS JOIN User u2
WHERE u1.ID < u2.ID
AND RAND() < 0.2;    -- 20% chance for any two users to be friends

-- Create some blocked relationships
INSERT INTO UserBlocked (UserID, BlockedID)
SELECT u1.ID, u2.ID
FROM User u1
CROSS JOIN User u2
WHERE u1.ID < u2.ID
AND RAND() < 0.05;   -- 5% chance for any two users to be blocked