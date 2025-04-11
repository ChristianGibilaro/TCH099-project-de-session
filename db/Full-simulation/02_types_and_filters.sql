-- Game Types
INSERT INTO Type (Name) VALUES
('Action'),
('Adventure'),
('RPG'),
('FPS'),
('Strategy'),
('Simulation'),
('Sports'),
('Racing'),
('Puzzle'),
('Platform'),
('MMORPG'),
('Fighting'),
('Survival'),
('Horror'),
('Battle Royale'),
('MOBA'),
('Card Game'),
('Visual Novel'),
('Roguelike'),
('Stealth'),
('Tower Defense'),
('Music/Rhythm'),
('Educational'),
('VR Action'),
('VR Adventure'),
('VR Simulation'),
('VR Sports'),
('VR Horror'),
('VR Social'),
('VR Puzzle'),
('Soulsborne'),
('Open World'),
('Sandbox'),
('City Builder'),
('Turn-based Strategy'),
('Real-time Strategy');

-- Type Filter
INSERT INTO TypeFilter (Name, Count) VALUES
('Video Games', (SELECT COUNT(*) FROM Type));

-- Type Array
INSERT INTO TypeArray (TypeID, TypeFilterID)
SELECT t.ID, tf.ID 
FROM Type t, TypeFilter tf;