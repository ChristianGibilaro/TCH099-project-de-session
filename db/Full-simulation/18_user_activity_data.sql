-- --------------------------------------------------------
-- File: 18_user_activity_data.sql
-- Description: Links users to activities and matches with their stats
-- Dependencies: User, Activity, Match tables
-- Created: 2025-04-11 03:50:51
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Link users to activities with stats
INSERT INTO UserActivity (UserID, ActivityID, Game_Count, Encoded_Stats)
SELECT 
    u.ID,
    a.ID,
    FLOOR(RAND() * 100),
    CONCAT('{"wins":', FLOOR(RAND() * 50), ',"losses":', FLOOR(RAND() * 50), ',"score":', FLOOR(RAND() * 1000), '}')
FROM User u
CROSS JOIN Activity a
WHERE RAND() < 0.3;  -- 30% chance for a user to be involved in an activity

-- Link users to matches with stats
INSERT INTO UserMatch (UserID, MatchID, Stats_Encoded)
SELECT 
    u.ID,
    m.ID,
    CONCAT('{"kills":', FLOOR(RAND() * 20), ',"deaths":', FLOOR(RAND() * 10), ',"score":', FLOOR(RAND() * 100), '}')
FROM User u
CROSS JOIN `Match` m
WHERE RAND() < 0.2;  -- 20% chance for a user to be in a match

COMMIT;
SET foreign_key_checks = 1;