-- Languages (Common web languages)
INSERT INTO Language (Name) VALUES
('English'),
('Spanish'),
('French'),
('German'),
('Italian'),
('Portuguese'),
('Dutch'),
('Russian'),
('Chinese (Simplified)'),
('Chinese (Traditional)'),
('Japanese'),
('Korean'),
('Arabic'),
('Hindi'),
('Turkish'),
('Polish'),
('Vietnamese'),
('Thai'),
('Indonesian'),
('Greek');

-- Language Filter
INSERT INTO LanguageFilter (Name, Count) VALUES
('All Languages', (SELECT COUNT(*) FROM Language));

-- Language Array
INSERT INTO LanguageArray (LanguageID, LanguageFilterID)
SELECT l.ID, lf.ID 
FROM Language l, LanguageFilter lf;