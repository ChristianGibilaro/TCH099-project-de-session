-- --------------------------------------------------------
-- File: 13_admin_data.sql
-- Description: Sets up admin permissions for activities, teams, and matches
-- Dependencies: User, Activity, Team, Match tables
-- Created: 2025-04-11 03:45:59
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Admin Activity assignments
INSERT INTO AdminActivity (UserID, ActivityID, Password)
SELECT 
    u.ID,
    a.ID,
    CONCAT('admin_hash_', a.ID) -- In real application, use proper password hashing
FROM User u
CROSS JOIN Activity a
WHERE RAND() < 0.2;  -- 20% chance for a user to be admin of an activity

-- Admin Team assignments
INSERT INTO AdminTeam (UserID, TeamID, Password)
SELECT 
    u.ID,
    t.ID,
    CONCAT('admin_hash_', t.ID) -- In real application, use proper password hashing
FROM User u
CROSS JOIN Team t
WHERE RAND() < 0.2;  -- 20% chance for a user to be admin of a team

-- Admin Match assignments
INSERT INTO AdminMatch (UserID, MatchID, Password)
SELECT 
    u.ID,
    m.ID,
    CONCAT('admin_hash_', m.ID) -- In real application, use proper password hashing
FROM User u
CROSS JOIN `Match` m
WHERE RAND() < 0.1;  -- 10% chance for a user to be admin of a match

COMMIT;
SET foreign_key_checks = 1;