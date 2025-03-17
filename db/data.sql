-- dumping data for table `coaches`
INSERT INTO `coaches` (`name`) VALUES
('Sensei Hiro'),
('Amélie'),
('Lucas'),
('Nina'),
('Paul');

-- dumping data for table `locations`
INSERT INTO `locations` (`name`, `logo`) VALUES
('Extérieur', 'ressources/park.png'),
('Intérieur', 'ressources/architecture-and-city.png');

-- dumping data for table `coaches`
INSERT INTO `levels` (`name`) VALUES
('Expert'),
('Intermédiaire'),
('Débutant'),
('Tous les niveaux');

-- dumping data for table `activities` with updated foreign keys
INSERT INTO `activities` (`id`, `name`, `description`, `image`, `level_id`, `coach_id`, `schedule_day`, `schedule_time`, `location_id`) VALUES
(1, 'Course à pied', 'Améliorez votre endurance et découvrez le plaisir de courir en plein air.', 'ressources/running.png', 'Tous niveaux', 1, 'Dimanche', '8h - 9h', 1),
(2, 'Yoga', 'Retrouver votre équilibre intérieur avec nos séances de yoga apaisantes', 'ressources/yoga.png', 'Débutant', 2, 'Lundi', '10h - 11h', 2),
(3, 'Calisthenic', 'Renforcez votre corps avec des exercices au poids du corps puissants et dynamiques.', 'ressources/calisthenic.png', 'Tous niveaux', 3, 'Mardi', '18h - 19h', 1),
(4, 'Natation', 'Plongez dans la santé et la vitalité grâce à nos cours de natation adaptés à tous les niveaux.', 'ressources/natation.png', 'Intermédiaire', 4, 'Mercredi', '14h - 15h', 2),
(5, 'Sports Aériens', 'Volez vers de nouvelles sensations avec nos sports aériens exaltants.', 'ressources/aerien.png', 'Expert', 5, 'Jeudi', '16h - 17h', 1),
(6, 'Karaté', 'Développez votre discipline et vos compétences en arts martiaux.', 'ressources/karate.png', 'Expert', 1, 'Mardi', '18h - 19h30', 2);

-- dumping data for table `users`
INSERT INTO `users` (`first_name`, `last_name`, `username`, `password`) 
VALUES ('Test', 'User', 'testuser', '$2y$10$E6Y0YWY2NzMwMDEzMjZidHOplRl25gNcBznF1XZgdcdkaFUYOpim');
