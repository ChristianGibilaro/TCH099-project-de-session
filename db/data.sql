-- =========================
-- 1) Positions (exemple)
-- =========================
INSERT INTO `Position` (`Name`, `Country`, `State`, `City`, `Street`, `Number`, `GPS`, `Local_Time`)
VALUES
  ('Tour Eiffel', 'France', 'Île-de-France', 'Paris',    'Champ de Mars', 5,   '48.8584,2.2945', 1),
  ('Big Ben',     'England', NULL,           'London',   'Westminster',   1,   '51.5007,-0.1246',1);

-- =========================
-- 2) Types (exemple)
-- =========================
INSERT INTO `Type` (`Name`)
VALUES
  ('Sport'),
  ('E-Sport');

-- =========================
-- 3) Languages
-- =========================
INSERT INTO `Language` (`Name`)
VALUES
  ('Français'),
  ('English');

-- =========================
-- 4) Environments
-- =========================
INSERT INTO `Environment` (`Name`)
VALUES
  ('Indoor'),
  ('Outdoor');

-- =========================
-- 5) Levels 
-- =========================
INSERT INTO `Level` (`Name`, `Img`, `Index`)
VALUES
  ('Débutant',  NULL, 1),
  ('Intermédiaire', NULL, 2),
  ('Expert',    NULL, 3);

-- =========================
-- 6) Ranks 
-- =========================
INSERT INTO `Rank` (`Name`, `Img`, `Index`)
VALUES
  ('Captain',   'https://exemple.com/captain.png',   1),
  ('Lieutenant','https://exemple.com/lieutenant.png',2),
  ('Member',    'https://exemple.com/member.png',    3);

-- =========================
-- 7) Activity
-- =========================
INSERT INTO `Activity`
  (`Title`, `IsSport`, `Main_Img`, `Description`)
VALUES
  ('Football', b'1', 'https://exemple.com/football.png','Activité Football'),
  ('Chess',    b'0', 'https://exemple.com/chess.png',   'Jeu d’échecs');

