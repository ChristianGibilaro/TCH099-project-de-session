-- --------------------------------------------------------
-- File: 14_image_library.sql
-- Description: Populates image library for users, teams, activities
-- Dependencies: User, Team, Activity tables
-- Created: 2025-04-11 03:47:07
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Sample images for users
INSERT INTO ImgLib (UserID, Url, `Index`, Img)
SELECT 
    ID,
    CONCAT('https://example.com/user/', ID, '/profile.jpg'),
    1,
    CONCAT('user_', ID, '_profile.jpg')
FROM User;

-- Sample images for teams
INSERT INTO ImgLib (TeamID, Url, `Index`, Img)
SELECT 
    ID,
    CONCAT('https://example.com/team/', ID, '/logo.jpg'),
    1,
    CONCAT('team_', ID, '_logo.jpg')
FROM Team;

-- Sample images for activities
INSERT INTO ImgLib (ActivityID, Url, `Index`, Img)
SELECT 
    ID,
    CONCAT('https://example.com/activity/', ID, '/banner.jpg'),
    1,
    CONCAT('activity_', ID, '_banner.jpg')
FROM Activity;

COMMIT;
SET foreign_key_checks = 1;