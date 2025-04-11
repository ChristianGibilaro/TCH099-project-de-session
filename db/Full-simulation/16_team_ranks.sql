-- Create team-specific ranks
INSERT INTO TeamRank (ID, Name, RankID, TeamID)
SELECT 
    ROW_NUMBER() OVER () as ID,
    CONCAT(r.Name, ' of ', t.Name),
    r.ID,
    t.ID
FROM Rank r
CROSS JOIN Team t;