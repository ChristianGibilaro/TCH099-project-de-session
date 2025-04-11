-- --------------------------------------------------------
-- File: 06_levels_and_ranks.sql
-- Description: Initializes game levels and ranks
-- Dependencies: Activity table
-- Created: 2025-04-11 03:42:50
-- Author: ChristianGibilaro
-- --------------------------------------------------------

SET foreign_key_checks = 0;
START TRANSACTION;

-- Levels for Helldivers II
INSERT INTO Level (Name, `Index`) VALUES
('Trivial', 1),
('Easy', 2),
('Medium', 3),
('Challenging', 4),
('Hard', 5),
('Extreme', 6),
('Suicide Mission', 7),
('Impossible', 8),
('Helldive', 9),
('Super Helldive', 10);

-- Link levels to activities
INSERT INTO ActivityLevel (Name, ActivityID, LevelID)
SELECT l.Name, a.ID, l.ID
FROM Level l, Activity a
WHERE a.Title = 'Helldivers II';

-- Generic Ranks for all activities
INSERT INTO Rank (Name, `Index`) VALUES
('Recruit', 1),
('Veteran', 2),
('Elite', 3),
('Legend', 4),
('Master', 5);

COMMIT;
SET foreign_key_checks = 1;