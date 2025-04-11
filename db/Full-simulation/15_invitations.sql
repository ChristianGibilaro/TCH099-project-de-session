-- Sample team invitations
INSERT INTO Invitation (Name, TeamID, UserID, RankID, Expiration_date)
SELECT 
    CONCAT('Invitation to join ', t.Name),
    t.ID,
    u.ID,
    r.ID,
    DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
FROM Team t
CROSS JOIN User u
CROSS JOIN Rank r
WHERE RAND() < 0.1  -- 10% chance for each combination
AND NOT EXISTS (
    SELECT 1 FROM UserTeam ut 
    WHERE ut.UserID = u.ID 
    AND ut.TeamID = t.ID
);