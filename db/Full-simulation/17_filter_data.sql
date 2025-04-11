-- --------------------------------------------------------
-- File: 17_filter_data.sql
-- Description: Sets up filter data for activities, teams, and matches
-- Dependencies: All filter tables, Activity, Team, Match, Position, Type, Language, Environment tables
-- Created: 2025-04-11 03:48:41
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Activity Filters
INSERT INTO ActivityFilter (PositionFilterID, TypeFilterID, LanguageFilterID, EnvironmentFilterID)
SELECT 
    pf.ID,
    tf.ID,
    lf.ID,
    ef.ID
FROM PositionFilter pf
CROSS JOIN TypeFilter tf
CROSS JOIN LanguageFilter lf
CROSS JOIN EnvironmentFilter ef
LIMIT 1;

-- Team Filters
INSERT INTO TeamFilter (PositionFilterID, TypeFilterID, LanguageFilterID, EnvironmentFilterID)
SELECT 
    pf.ID,
    tf.ID,
    lf.ID,
    ef.ID
FROM PositionFilter pf
CROSS JOIN TypeFilter tf
CROSS JOIN LanguageFilter lf
CROSS JOIN EnvironmentFilter ef
LIMIT 1;

-- Match Filters
INSERT INTO MatchFilter (PositionFilterID, TypeFilterID, LanguageFilterID, EnvironmentFilterID)
SELECT 
    pf.ID,
    tf.ID,
    lf.ID,
    ef.ID
FROM PositionFilter pf
CROSS JOIN TypeFilter tf
CROSS JOIN LanguageFilter lf
CROSS JOIN EnvironmentFilter ef
LIMIT 1;

-- Activity Data
INSERT INTO ActivityData (
    ActivityFilterID, ActivityID, PositionID, TypeID, 
    LanguageID, EnvironmentID, Team_Count, QuickTeam_Count,
    Player_Count, Active_Player_Count, Rating
)
SELECT 
    af.ID,
    a.ID,
    p.ID,
    t.ID,
    l.ID,
    e.ID,
    FLOOR(RAND() * 100),
    FLOOR(RAND() * 50),
    FLOOR(RAND() * 1000),
    FLOOR(RAND() * 500),
    FLOOR(RAND() * 5) + 1
FROM ActivityFilter af
CROSS JOIN Activity a
CROSS JOIN Position p
CROSS JOIN Type t
CROSS JOIN Language l
CROSS JOIN Environment e
WHERE RAND() < 0.1;

-- Team Data
INSERT INTO TeamData (
    TeamFilterID, TeamID, PositionID, TypeID,
    LanguageID, EnvironmentID, Match_Count, Max_Player,
    Player_Count, Active_Player_Count, Rating
)
SELECT 
    tf.ID,
    t.ID,
    p.ID,
    ty.ID,
    l.ID,
    e.ID,
    FLOOR(RAND() * 100),
    20,
    FLOOR(RAND() * 20),
    FLOOR(RAND() * 15),
    FLOOR(RAND() * 5) + 1
FROM TeamFilter tf
CROSS JOIN Team t
CROSS JOIN Position p
CROSS JOIN Type ty
CROSS JOIN Language l
CROSS JOIN Environment e
WHERE RAND() < 0.1;

-- Match Data
INSERT INTO MatchData (
    MatchFilterID, MatchID, PositionID, TypeID,
    LanguageID, EnvironmentID, Team_Count, QuickTeam_Count,
    Player_Count, Active_Player_Count, Rating
)
SELECT 
    mf.ID,
    m.ID,
    p.ID,
    t.ID,
    l.ID,
    e.ID,
    FLOOR(RAND() * 4) + 1,
    FLOOR(RAND() * 2),
    FLOOR(RAND() * 20),
    FLOOR(RAND() * 15),
    FLOOR(RAND() * 5) + 1
FROM MatchFilter mf
CROSS JOIN `Match` m
CROSS JOIN Position p
CROSS JOIN Type t
CROSS JOIN Language l
CROSS JOIN Environment e
WHERE RAND() < 0.1;

COMMIT;
SET foreign_key_checks = 1;