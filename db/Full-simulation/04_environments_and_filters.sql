-- Environments
INSERT INTO Environment (Name) VALUES
('Windows'),
('macOS'),
('Linux'),
('PlayStation 5'),
('Xbox Series X/S'),
('Nintendo Switch'),
('Android'),
('iOS'),
('Web Browser'),
('VR');

-- Environment Filter
INSERT INTO EnvironmentFilter (Name, Count) VALUES
('All Platforms', (SELECT COUNT(*) FROM Environment));

-- Environment Array
INSERT INTO EnvironmentArray (EnvironmentID, EnvironmentFilterID)
SELECT e.ID, ef.ID 
FROM Environment e, EnvironmentFilter ef;