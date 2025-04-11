-- Countries
INSERT INTO Position (Name, Country) VALUES
('Afghanistan', 'Afghanistan'),
('Albania', 'Albania'),
('Algeria', 'Algeria'),
-- ... (add all countries)
('Zimbabwe', 'Zimbabwe');

-- US States
INSERT INTO Position (Name, Country, State) VALUES
('Alabama, USA', 'United States', 'Alabama'),
('Alaska, USA', 'United States', 'Alaska'),
('Arizona, USA', 'United States', 'Arizona'),
-- ... (add all US states)
('Wyoming, USA', 'United States', 'Wyoming');

-- Canadian Provinces
INSERT INTO Position (Name, Country, State) VALUES
('Alberta, Canada', 'Canada', 'Alberta'),
('British Columbia, Canada', 'Canada', 'British Columbia'),
('Manitoba, Canada', 'Canada', 'Manitoba'),
-- ... (add all Canadian provinces)
('Yukon, Canada', 'Canada', 'Yukon');

-- Position Filters
INSERT INTO PositionFilter (Name, Count) VALUES
('All Countries', (SELECT COUNT(*) FROM Position WHERE State IS NULL)),
('US States', (SELECT COUNT(*) FROM Position WHERE Country = 'United States')),
('Canadian Provinces', (SELECT COUNT(*) FROM Position WHERE Country = 'Canada'));

-- Position Array for Countries
INSERT INTO PositionArray (PositionID, PositionFilterID)
SELECT p.ID, pf.ID 
FROM Position p, PositionFilter pf 
WHERE pf.Name = 'All Countries' AND p.State IS NULL;

-- Position Array for US States
INSERT INTO PositionArray (PositionID, PositionFilterID)
SELECT p.ID, pf.ID 
FROM Position p, PositionFilter pf 
WHERE pf.Name = 'US States' AND p.Country = 'United States';

-- Position Array for Canadian Provinces
INSERT INTO PositionArray (PositionID, PositionFilterID)
SELECT p.ID, pf.ID 
FROM Position p, PositionFilter pf 
WHERE pf.Name = 'Canadian Provinces' AND p.Country = 'Canada';