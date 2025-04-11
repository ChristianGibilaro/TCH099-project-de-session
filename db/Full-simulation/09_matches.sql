-- --------------------------------------------------------
-- File: 09_matches.sql
-- Description: Creates match data
-- Dependencies: Team, User, Level tables
-- Created: 2025-04-11 03:42:50
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Generate 10 matches for each team
INSERT INTO `Match` (Is_Public, UserID, TeamID, ActivityID, LevelID)
SELECT 
    1,
    (SELECT ID FROM User ORDER BY RAND() LIMIT 1),
    t.ID,
    t.ActivityID,
    (SELECT ID FROM Level ORDER BY RAND() LIMIT 1)
FROM Team t
CROSS JOIN (
    SELECT 1 AS n UNION ALL 
    SELECT 2 UNION ALL 
    SELECT 3 UNION ALL 
    SELECT 4 UNION ALL 
    SELECT 5 UNION ALL 
    SELECT 6 UNION ALL 
    SELECT 7 UNION ALL 
    SELECT 8 UNION ALL 
    SELECT 9 UNION ALL 
    SELECT 10
) numbers;

COMMIT;
SET foreign_key_checks = 1;