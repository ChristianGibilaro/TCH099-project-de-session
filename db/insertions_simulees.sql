-- =========================
-- 1) Insertion des entités de base (sans dépendances)
-- =========================

-- Positions
INSERT INTO `Position` (`Name`, `Country`, `City`) 
VALUES
  ('Tour Eiffel', 'France', 'Paris'),
  ('Big Ben',     'England', 'London');

-- Types
INSERT INTO `Type` (`Name`)
VALUES
  ('Sport'),
  ('E-Sport');

-- Langues
INSERT INTO `Language` (`Name`)
VALUES
  ('French'),
  ('English');

-- Environments
INSERT INTO `Environment` (`Name`)
VALUES
  ('Indoor'),
  ('Outdoor');

-- Levels
INSERT INTO `Level` (`Name`)
VALUES
  ('Débutant');

-- Activity (Title, IsSport, Main_Img, Description sont obligatoires)
INSERT INTO `Activity`
  (`Title`, `IsSport`, `Main_Img`, `Description`)
VALUES
  (
    'Football',
    b'1',
    'https://api.lunarcovenant.com/ressources/images/profile/imgSportArerien.png',
    'Activity for playing football matches.'
  );

-- Team (référence Activity=1)
INSERT INTO `Team`
  (`ActivityID`, `Name`, `Description`)
VALUES
  (
    1,
    'ParisUnited',
    'Friendly local team'
  );

-- =========================
-- 2) Création des utilisateurs
-- =========================

INSERT INTO `User`
  (`Img`, `Pseudo`, `Name`, `Email`, `Password`, `Last_Login`, `LanguageID`, `PositionID`)
VALUES
  (
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    'Alice88',
    'Alice Martin',
    'alice@example.com',
    'hashedPwd1',
    '2025-01-10',
    1,
    1
  ),
  (
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    'Boby',
    'Bob Marley',
    'bob@example.com',
    'hashedPwd2',
    '2025-01-15',
    2,
    2
  ),
  (
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    'CharlieC',
    'Charlie Cole',
    'charlie@example.com',
    'hashedPwd3',
    '2025-02-01',
    1,
    1
  ),
  (
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    'DanaD',
    'Dana Davis',
    'dana@example.com',
    'hashedPwd4',
    '2025-03-01',
    2,
    2
  ),
  (
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    'EveE',
    'Eve Evans',
    'eve@example.com',
    'hashedPwd5',
    '2025-03-15',
    1,
    1
  );

-- UserFriend
INSERT INTO `UserFriend` (`UserID`, `FriendID`)
VALUES
  (1, 2);

-- =========================
-- 3) Création d'un Match
-- =========================

-- Match (référence : User=1, Team=1, Activity=1, Level=1)
INSERT INTO `Match`
  (`Is_Public`, `UserID`, `TeamID`, `ActivityID`, `LevelID`, `Description`, `End_Date`, `Opening_End`)
VALUES
  (
    b'1',
    1,
    1,
    1,
    1,
    'Friendly match in Paris',
    '2025-04-01 18:00:00',
    '2025-04-01 17:30:00'
  );

-- =========================
-- 4) Filters de base (PositionFilter, TypeFilter, etc.)
-- =========================

INSERT INTO `PositionFilter` (`Name`, `Count`)
VALUES
  ('FilterParis',  100);

INSERT INTO `TypeFilter` (`Name`, `Count`)
VALUES
  ('FilterSport',  200);

INSERT INTO `LanguageFilter` (`Name`, `Count`)
VALUES
  ('FilterFrench', 50);

INSERT INTO `EnvironmentFilter` (`Name`, `Count`)
VALUES
  ('FilterIndoor', 30);

-- =========================
-- 5) ActivityLevel (référence Activity=1, Level=1)
-- =========================

INSERT INTO `ActivityLevel` (`Name`, `ActivityID`, `LevelID`)
VALUES
  (
    'BeginnerLevel', 
    1,   -- l'Activity qu'on vient d'insérer
    1    -- Level = "Débutant"
  );

-- =========================
-- 6) Divers (UserBlocked, Ranks, etc.)
-- =========================

INSERT INTO `UserBlocked` (`UserID`, `BlockedID`)
VALUES
  (1, 3);

-- Ranks
INSERT INTO `Rank` (`Name`, `Img`, `Index`)
VALUES
  ('Captain',   'https://api.lunarcovenant.com/ressources/images/profile/home.png',   1),
  ('Lieutenant','https://api.lunarcovenant.com/ressources/images/profile/home.png',2),
  ('Member',    'https://api.lunarcovenant.com/ressources/images/profile/home.png',   3);

-- =========================
-- 7) Liaison UserTeam (User=2, Team=1, Rank=1)
-- =========================

INSERT INTO `UserTeam`
  (`UserID`, `TeamID`, `RankID`, `Game_Count`)
VALUES
  (
    2,  -- Bob
    1,  -- ParisUnited
    1,  -- Captain
    10
  );

-- =========================
-- 8) Liaison UserMatch (User=2, Match=1)
-- =========================

INSERT INTO `UserMatch`
  (`UserID`, `MatchID`, `Stats_Encoded`)
VALUES
  (
    2,  -- Bob
    1,  -- Match #1
    'No stats yet'
  );

-- =========================
-- 9) Invitation
-- =========================

INSERT INTO `Invitation`
  (`Name`, `TeamID`, `UserID`, `RankID`, `Expiration_date`)
VALUES
  (
    'Join our team!',
    1,  -- ParisUnited
    2,  -- Bob
    1,  -- Captain
    '2025-05-01'
  );

-- =========================
-- 10) TeamRank
-- =========================

INSERT INTO `TeamRank`
  (`ID`, `Name`, `RankID`, `TeamID`)
VALUES
  (
    1,
    'CaptainOfTeam',
    1,  -- Rank #1
    1   -- Team #1
  );

-- =========================
-- 11) Messages / Chat
-- =========================

INSERT INTO `Message`
  (`UserID`, `Content`)
VALUES
  (
    1,  -- Alice
    'Hello everyone, welcome!'
  );

INSERT INTO `Chat`
  (`Name`)
VALUES
  ('TeamChat');

INSERT INTO `ChatCreator`
  (`UserID`, `TeamID`, `ChatID`)
VALUES
  (
    1,  -- Alice
    1,  -- ParisUnited
    1   -- TeamChat
  );

INSERT INTO `ChatMessage` (`ChatID`, `MessageID`)
VALUES
  (1, 1);

-- =========================
-- 12) AdminActivity, AdminMatch, AdminTeam
-- =========================

INSERT INTO `AdminActivity`
  (`UserID`, `ActivityID`, `Password`)
VALUES
  (1, 1, 'secretForActivity');

INSERT INTO `AdminMatch`
  (`UserID`, `MatchID`, `Password`)
VALUES
  (1, 1, 'secretForMatch');

INSERT INTO `AdminTeam`
  (`UserID`, `TeamID`, `Password`)
VALUES
  (1, 1, 'secretForTeam');

-- =========================
-- 13) Notification
-- =========================

INSERT INTO `Notification`
  (`Content`, `Date`, `Time`, `Expiration_hours`)
VALUES
  (
    'Welcome to the new league!',
    '2025-03-30',
    '12:00:00',
    48
  );

INSERT INTO `UserNotification`
  (`UserID`, `TeamID`, `NotificationID`)
VALUES
  (1, 1, 1);

-- =========================
-- 14) UserActivity
-- =========================

INSERT INTO `UserActivity`
  (`UserID`, `ActivityID`, `Game_Count`, `Joined_Date`, `Encoded_Stats`)
VALUES
  (
    1,   -- Alice
    1,   -- Football
    5,
    CURRENT_TIMESTAMP(),
    'SomeStatsEncodedHere'
  );

-- =========================
-- 15) ImgLib
-- =========================

INSERT INTO `ImgLib`
  (`UserID`, `TeamID`, `MatchID`, `ActivityID`, `Url`, `Index`, `Img`)
VALUES
  (
    1,
    1,
    1,
    1,
    'https://api.lunarcovenant.com/ressources/images/profile/home.png',
    1,
    'souvenir.jpg'
  );

-- =========================
-- 16) ActivityFilter
-- =========================

INSERT INTO `ActivityFilter`
  (`PositionFilterID`, `TypeFilterID`, `LanguageFilterID`, `EnvironmentFilterID`)
VALUES
  (1, 1, 1, 1);

-- =========================
-- 17) ActivityData
-- =========================

INSERT INTO `ActivityData`
  (`ActivityFilterID`, `ActivityID`, `PositionID`, `TypeID`, `LanguageID`, `EnvironmentID`,
   `Team_Count`, `QuickTeam_Count`, `Player_Count`, `Active_Player_Count`, `Rating`)
VALUES
  (
    1,  -- activityFilter #1
    1,  -- activity #1
    1,  -- position #1
    1,  -- type #1 (Sport)
    1,  -- language #1 (French)
    1,  -- environment #1 (Indoor)
    2,
    1,
    10,
    5,
    9
  );

-- =========================
-- 18) TeamFilter
-- =========================

INSERT INTO `TeamFilter`
  (`PositionFilterID`, `TypeFilterID`, `LanguageFilterID`, `EnvironmentFilterID`)
VALUES
  (1, 1, 1, 1);

-- =========================
-- 19) TeamData
-- =========================

INSERT INTO `TeamData`
  (`TeamFilterID`, `TeamID`, `PositionID`, `TypeID`, `LanguageID`, `EnvironmentID`,
   `Match_Count`, `Max_Player`, `Player_Count`, `Active_Player_Count`, `Rating`)
VALUES
  (
    1,  -- teamFilter #1
    1,  -- team #1
    1,  -- position #1
    1,  -- type #1
    1,  -- language #1
    1,  -- environment #1
    5,
    11,
    9,
    8,
    10
  );

-- =========================
-- 20) MatchFilter
-- =========================

INSERT INTO `MatchFilter`
  (`PositionFilterID`, `TypeFilterID`, `LanguageFilterID`, `EnvironmentFilterID`)
VALUES
  (1, 1, 1, 1);

-- =========================
-- 21) MatchData
-- =========================

INSERT INTO `MatchData`
  (`MatchFilterID`, `MatchID`, `PositionID`, `TypeID`, `LanguageID`, `EnvironmentID`,
   `Team_Count`, `QuickTeam_Count`, `Player_Count`, `Active_Player_Count`, `Rating`)
VALUES
  (
    1,  -- matchFilter #1
    1,  -- match #1
    1,  -- position #1
    1,  -- type #1
    1,  -- language #1
    1,  -- environment #1
    2,
    1,
    10,
    5,
    7
  );