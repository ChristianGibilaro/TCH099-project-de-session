-- Base Tables (Fewest Dependencies)
CREATE TABLE `Position` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL,
  `Country` VARCHAR(70),
  `State` VARCHAR(70),
  `City` VARCHAR(70),
  `Street` VARCHAR(70),
  `Number` INT(16),
  `GPS` TEXT,
  `Local_Time` INT(8),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Type` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Language` (
  `ID` INT(10) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Environment` (
  `ID` INT(10) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Rank` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(35) NOT NULL,
  `Img` VARCHAR(255),
  `Index` INT(8),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Level` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(35) NOT NULL,
  `Img` VARCHAR(255),
  `Index` INT(10),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Notification` (
  `ID` INT(24) UNIQUE NOT NULL AUTO_INCREMENT,
  `Content` VARCHAR(512) NOT NULL,
  `Date` DATE NOT NULL,
  `Time` TIME NOT NULL,
  `Expiration_hours` INT(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Chat` (
  `ID` INT(32) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(32) NOT NULL,
  `Creation_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- User Table (Depends on Position, Language)
CREATE TABLE `User` (
  `ID` INT(32) UNIQUE NOT NULL AUTO_INCREMENT,
  `ApiKey` VARCHAR(64) UNIQUE,
  `Img` VARCHAR(255) NOT NULL,
  `Backg_Img` VARCHAR(255),
  `Pseudo` VARCHAR(35) UNIQUE NOT NULL,
  `Name` VARCHAR(70) UNIQUE NOT NULL,
  `Email` VARCHAR(320) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `Last_Login` DATE NOT NULL,
  `LanguageID` INT(10) NOT NULL,
  `Creation_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `PositionID` INT(16),
  `Description` TEXT,
  `Birth` DATE,
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`PositionID`) REFERENCES `Position` (`ID`),
  FOREIGN KEY (`LanguageID`) REFERENCES `Language` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Filter Definition Tables
CREATE TABLE `PositionFilter` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL UNIQUE,
  `Count` INT(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TypeFilter` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL UNIQUE,
  `Count` INT(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `LanguageFilter` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(255) NOT NULL UNIQUE,
  `Count` INT(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `EnvironmentFilter` (
  `ID` INT(16) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(255) NOT NULL UNIQUE,
  `Count` INT(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Filter Linking Tables
CREATE TABLE `PositionArray` (
  `PositionID` INT(16) NOT NULL,
  `PositionFilterID` INT(16) NOT NULL,
  FOREIGN KEY (`PositionID`) REFERENCES `Position` (`ID`),
  FOREIGN KEY (`PositionFilterID`) REFERENCES `PositionFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TypeArray` (
  `TypeID` INT(16) NOT NULL,
  `TypeFilterID` INT(16) NOT NULL,
  FOREIGN KEY (`TypeID`) REFERENCES `Type` (`ID`),
  FOREIGN KEY (`TypeFilterID`) REFERENCES `TypeFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `LanguageArray` (
  `LanguageID` INT(10) NOT NULL,
  `LanguageFilterID` INT(16) NOT NULL,
  FOREIGN KEY (`LanguageID`) REFERENCES `Language` (`ID`),
  FOREIGN KEY (`LanguageFilterID`) REFERENCES `LanguageFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `EnvironmentArray` (
  `EnvironmentID` INT(10) NOT NULL,
  `EnvironmentFilterID` INT(16) NOT NULL,
  FOREIGN KEY (`EnvironmentID`) REFERENCES `Environment` (`ID`),
  FOREIGN KEY (`EnvironmentFilterID`) REFERENCES `EnvironmentFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Composite Filter Tables (Depend on Base or Filter Definition Tables)
CREATE TABLE `ActivityFilter` (
  `ID` INT(8) UNIQUE NOT NULL AUTO_INCREMENT,
  `PositionFilterID` INT(16) NOT NULL,
  `TypeFilterID` INT(16) NOT NULL,
  `LanguageFilterID` INT(10) NOT NULL, -- Corrected data type
  `EnvironmentFilterID` INT(10) NOT NULL, -- Corrected data type
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`PositionFilterID`) REFERENCES `PositionFilter` (`ID`),
  FOREIGN KEY (`TypeFilterID`) REFERENCES `TypeFilter` (`ID`),
  FOREIGN KEY (`LanguageFilterID`) REFERENCES `LanguageFilter` (`ID`),
  FOREIGN KEY (`EnvironmentFilterID`) REFERENCES `EnvironmentFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TeamFilter` (
  `ID` INT(8) UNIQUE NOT NULL AUTO_INCREMENT,
  `PositionFilterID` INT(16) NOT NULL,
  `TypeFilterID` INT(16) NOT NULL,
  `LanguageFilterID` INT(16) NOT NULL,
  `EnvironmentFilterID` INT(16) NOT NULL,
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`PositionFilterID`) REFERENCES `PositionFilter` (`ID`),
  FOREIGN KEY (`TypeFilterID`) REFERENCES `TypeFilter` (`ID`),
  FOREIGN KEY (`LanguageFilterID`) REFERENCES `LanguageFilter` (`ID`),
  FOREIGN KEY (`EnvironmentFilterID`) REFERENCES `EnvironmentFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `MatchFilter` (
  `ID` INT(8) UNIQUE NOT NULL AUTO_INCREMENT,
  `PositionFilterID` INT(16) NOT NULL,
  `TypeFilterID` INT(16) NOT NULL,
  `LanguageFilterID` INT(16) NOT NULL, -- Adjusted type to match TeamFilter/LanguageFilter
  `EnvironmentFilterID` INT(16) NOT NULL, -- Adjusted type to match TeamFilter/EnvironmentFilter
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`PositionFilterID`) REFERENCES `PositionFilter` (`ID`), -- Corrected reference
  FOREIGN KEY (`TypeFilterID`) REFERENCES `TypeFilter` (`ID`),       -- Corrected reference
  FOREIGN KEY (`LanguageFilterID`) REFERENCES `LanguageFilter` (`ID`), -- Corrected reference
  FOREIGN KEY (`EnvironmentFilterID`) REFERENCES `EnvironmentFilter` (`ID`) -- Corrected reference
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Main Entity Tables (Depend on Filters, Base Tables)
CREATE TABLE `Activity` (
  `ID` INT(32) UNIQUE NOT NULL AUTO_INCREMENT,
  `Title` VARCHAR(70) NOT NULL UNIQUE,
  `IsSport` BIT NOT NULL,
  `Main_Img` VARCHAR(255) NOT NULL,
  `Description` TEXT NOT NULL,
  `ActivityFilterID` INT(8) NOT NULL,
  `MatchFilterID` INT(8) NOT NULL,
  `TeamFilterID` INT(8) NOT NULL,
  `Creation_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `Logo_Img` VARCHAR(255),
  `Point_Value` VARCHAR(35),
  `Word_4_Player` VARCHAR(35),
  `Word_4_Teammate` VARCHAR(35),
  `Word_4_Playing` VARCHAR(35),
  `Live_url` TEXT,
  `Live_Desc` TEXT,
  `Main_Color` VARCHAR(32),
  `Second_Color` VARCHAR(32),
  `Friend_Main_Color` VARCHAR(32),
  `Friend_Second_Color` VARCHAR(32),
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`ActivityFilterID`) REFERENCES `ActivityFilter` (`ID`),
  FOREIGN KEY (`TeamFilterID`) REFERENCES `TeamFilter` (`ID`),
  FOREIGN KEY (`MatchFilterID`) REFERENCES `MatchFilter` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Team` (
  `ID` INT(64) UNIQUE NOT NULL AUTO_INCREMENT,
  `ActivityID` INT(32) NOT NULL,
  `Name` VARCHAR(35) NOT NULL,
  `Description` TEXT NOT NULL,
  `Creation_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `Main_Img` VARCHAR(255),
  `Logo_Img` VARCHAR(255),
  `Main_Color` VARCHAR(32),
  `Second_Color` VARCHAR(32),
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Match` (
  `ID` INT(64) UNIQUE NOT NULL AUTO_INCREMENT,
  `Is_Public` TINYINT(1) NOT NULL DEFAULT 1, -- Corrected comma
  `UserID` INT(32) NOT NULL,
  `Creation_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `End_Date` DATETIME,
  `Opening_End` DATETIME,
  `Description` TEXT,
  `TeamID` INT(64),
  `ActivityID` INT(32),
  `LevelID` INT(16),
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`LevelID`) REFERENCES `Level` (`ID`),
  FOREIGN KEY (`UserID`) REFERENCES `User`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Junction/Linking/Data Tables (Depend on Main Entities, Base Tables, etc.)
CREATE TABLE `ActivityLevel` (
  `Name` VARCHAR(35) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  `LevelID` INT(16) NOT NULL,
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`LevelID`) REFERENCES `Level` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserFriend` (
  `UserID` INT(32) NOT NULL,
  `FriendID` INT(32) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`FriendID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserBlocked` (
  `UserID` INT(32) NOT NULL,
  `BlockedID` INT(32) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`BlockedID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserNotification` (
  `UserID` INT(32) NOT NULL,
  `TeamID` INT(64),
  `NotificationID` INT(24),
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`NotificationID`) REFERENCES `Notification` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserActivity` (
  `UserID` INT(32) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  `Game_Count` INT(16) NOT NULL,
  `Joined_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `Encoded_Stats` text,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `AdminActivity` (
  `UserID` INT(32) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserTeam` (
  `UserID` INT(32) NOT NULL,
  `TeamID` INT(64) NOT NULL,
  `RankID` INT(16) NOT NULL,
  `Game_Count` INT(16) NOT NULL,
  `Joined_Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`RankID`) REFERENCES `Rank` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `AdminTeam` (
  `UserID` INT(32) NOT NULL,
  `TeamID` INT(64) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `UserMatch` (
  `UserID` INT(32) NOT NULL,
  `MatchID` INT(64) NOT NULL,
  `Stats_Encoded` text,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `AdminMatch` (
  `UserID` INT(32) NOT NULL,
  `MatchID` INT(64) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ImgLib` (
  `UserID` INT(32),
  `TeamID` INT(64),
  `MatchID` INT(64),
  `ActivityID` INT(32),
  `Url` VARCHAR(2048),
  `Index` INT(8),
  `Img` VARCHAR(255),
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Invitation` (
  `Name` VARCHAR(70) NOT NULL,
  `TeamID` INT(64) NOT NULL,
  `UserID` INT(32) NOT NULL,
  `RankID` INT(16) NOT NULL,
  `Expiration_date` DATE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`RankID`) REFERENCES `Rank` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TeamRank` (
  `ID` INT(32) UNIQUE NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(70) NOT NULL,
  `RankID` INT(16) NOT NULL,
  `TeamID` INT(64) NOT NULL,
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`RankID`) REFERENCES `Rank` (`ID`),
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ChatCreator` (
  `UserID` INT(32) NOT NULL,
  `TeamID` INT(64),
  `ChatID` INT(32) NOT NULL,
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`ChatID`) REFERENCES `Chat` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Message` (
  `ID` INT(64) UNIQUE NOT NULL AUTO_INCREMENT,
  `UserID` INT(32) NOT NULL,
  `Content` text NOT NULL,
  `Date` TIMESTAMP NULL DEFAULT current_timestamp(),
  `File` VARCHAR(255),
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ChatMessage` (
  `ChatID` INT(32) NOT NULL,
  `MessageID` INT(64) NOT NULL,
  FOREIGN KEY (`ChatID`) REFERENCES `Chat` (`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`MessageID`) REFERENCES `Message` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ActivityData` (
  `ActivityID` INT(32) NOT NULL,
  `Team_Count` INT(16) NOT NULL,
  `QuickTeam_Count` INT(16) NOT NULL,
  `Player_Count` INT(32) NOT NULL,
  `Active_Player_Count` INT(32) NOT NULL,
  `Rating` INT(8),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TeamData` (
  `TeamID` INT(64) NOT NULL,
  `Match_Count` INT(32) NOT NULL,
  `Max_Player` INT(16) NOT NULL,
  `Player_Count` INT(32) NOT NULL,
  `Score` INT(8) NOT NULL DEFAULT 0,
  `Age_min` INT(8),
  `Microphone` BIT,
  FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `MatchData` (
  `MatchID` INT(64) NOT NULL,
  `Player_Count` INT(32) NOT NULL,
  `Max_Player` INT(16) NOT NULL,
  `Age_min` INT(8),
  `Microphone` BIT,
  FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Admins` (
  `ID` INT(32) UNIQUE NOT NULL AUTO_INCREMENT,
  `UserID` INT(32) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`UserID`) REFERENCES `User` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `PositionArrayActivity` (
  `PositionID` INT(16) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  FOREIGN KEY (`PositionID`) REFERENCES `Position` (`ID`),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `TypeArrayActivity` (
  `TypeID` INT(16) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  FOREIGN KEY (`TypeID`) REFERENCES `Type` (`ID`),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `LanguageArrayActivity` (
  `LanguageID` INT(10) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  FOREIGN KEY (`LanguageID`) REFERENCES `Language` (`ID`),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `EnvironmentArrayActivity` (
  `EnvironmentID` INT(10) NOT NULL,
  `ActivityID` INT(32) NOT NULL,
  FOREIGN KEY (`EnvironmentID`) REFERENCES `Environment` (`ID`),
  FOREIGN KEY (`ActivityID`) REFERENCES `Activity` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `InvitationMatch` (
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `Name` VARCHAR(255) NOT NULL,
  `MatchID` INT(64) NOT NULL, -- Matched type with Match.ID
  `UserID` INT(32) NOT NULL,
  `Expiration_date` DATE,
  FOREIGN KEY (`MatchID`) REFERENCES `Match`(`ID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `User`(`ID`) ON DELETE CASCADE
);