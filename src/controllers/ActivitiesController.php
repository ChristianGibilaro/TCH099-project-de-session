<?php

include_once 'config.php';
class ActivitiesController
{
    public static function connexionUser() {
        global $pdo;
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifie que les données sont bien envoyées en POST
            if (isset($_POST['email'], $_POST['password'])) {
                $email_saisie = htmlspecialchars($_POST['email']);
                $password_saisie = htmlspecialchars($_POST['password']);
    
                try {
                    $stmtUser = $pdo->prepare('SELECT * FROM User WHERE Pseudo = :pseudo OR Email = :email');
                    $stmtUser->bindParam(':pseudo', $email_saisie);
                    $stmtUser->bindParam(':email', $email_saisie);
                    $stmtUser->execute();
    
                    $user_infos = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
                    if ($user_infos && password_verify($password_saisie, $user_infos['Password'])) {
                        // Met à jour la date de connexion
                        $stmtUpdate = $pdo->prepare('UPDATE User SET Last_login = :last_login WHERE Pseudo = :pseudo OR Email = :email');
                        $stmtUpdate->execute([
                            ':last_login' => date('Y-m-d H:i:s'),
                            ':pseudo'     => $email_saisie,
                            ':email'      => $email_saisie
                        ]);
    
                        // Stockage en session si nécessaire
                        if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION["user_id"])) {
                            $_SESSION["user_id"] = $user_infos['ID'];
                            $_SESSION["user_pseudo"] = $user_infos['Pseudo'];
                        }
    
                        // Renvoie une réponse JSON unifiée
                        echo json_encode([
                            'success' => true,
                            'message' => 'Connexion réussie ! Bienvenue, ' . htmlspecialchars($user_infos['Pseudo']) . '.',
                            'user'    => $user_infos
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Nom d\'utilisateur ou mot de passe incorrect.'
                        ]);
                    }
                } catch (PDOException $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur est survenue lors du traitement de la requete.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Champs d\'utilisateur ou du mot de passe est vide!'
                ]);
            }
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'OPERATION ECHOUEE: Base de donnees introuvable.'
            ]);
        }
    }
    
    public static function creerUser() {
        global $pdo;
        header('Content-Type: application/json');
    
        $response = ['success' => false, 'message' => '', 'errors' => []];
    
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new Exception('Méthode HTTP non autorisée.');
            }
    
            // === Validate fields ===
            $requiredFields = ['pseudonym', 'nom', 'email', 'password', 'password2', 'description', 'language_id', 'age', 'agreement'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $response['errors'][$field] = "Le champ '$field' est requis.";
                }
            }
    
            if (!empty($_POST['password']) && $_POST['password'] !== $_POST['password2']) {
                $response['errors']['password2'] = 'Les mots de passe ne correspondent pas.';
            }
    
            if ($_POST['agreement'] !== 'accepted') {
                $response['errors']['agreement'] = 'Vous devez accepter les conditions.';
            }
    
            if (!empty($response['errors'])) {
                throw new Exception('Champs manquants ou invalides.');
            }
    
            // === Prepare data ===
            $pseudonym = htmlspecialchars($_POST['pseudonym']);
            $nom = htmlspecialchars($_POST['nom']);
            $email = htmlspecialchars($_POST['email']);
            $password = $_POST['password'];
            $description = htmlspecialchars($_POST['description']);
            $age = htmlspecialchars($_POST['age']);
            $languageID = intval($_POST['language_id']);
            $imagePath = null;
            $originalFileName = null;
    
            // === Insert position first ===
            $stmtPosition = $pdo->prepare('INSERT INTO Position (Name) VALUES (:name)');
            $stmtPosition->execute([':name' => $nom]);
            $positionID = $pdo->lastInsertId();
    
            // === Temporary image path (will be renamed after user insert)
            $tempImagePath = null;
    
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $originalFileName = basename($_FILES['image']['name']);
                $fileType = mime_content_type($fileTmpPath);
    
                $imageFolder = 'ressources/images/profile/';
                $videoFolder = 'ressources/videos/';
    
                if (!file_exists($imageFolder)) mkdir($imageFolder, 0755, true);
                if (!file_exists($videoFolder)) mkdir($videoFolder, 0755, true);
    
                $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $allowedVideoTypes = ['video/mp4', 'video/x-msvideo', 'video/webm'];
    
                if (in_array($fileType, $allowedImageTypes)) {
                    $destinationPath = $imageFolder . uniqid('temp_', true) . '_' . $originalFileName;
                } elseif (in_array($fileType, $allowedVideoTypes)) {
                    $destinationPath = $videoFolder . uniqid('temp_', true) . '_' . $originalFileName;
                } else {
                    throw new Exception("Type de fichier non pris en charge: $fileType");
                }
    
                if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                    throw new Exception('Impossible de déplacer le fichier téléchargé.');
                }
    
                $tempImagePath = $destinationPath;
    
            } elseif (!empty($_POST['imageLink'])) {
                $imagePath = trim($_POST['imageLink']);
            } else {
                throw new Exception('Aucune image téléchargée ou lien fourni.');
            }
    
            // === Insert user with placeholder or imageLink ===
            $stmtUser = $pdo->prepare('INSERT INTO User (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                                       VALUES (:img, :pseudo, :nom, :email, :passwordd, :last_login, :language_id, :creation_date, :position_id, :description, :age)');
            $stmtUser->execute([
                ':img' => $imagePath ?? 'pending',
                ':pseudo' => $pseudonym,
                ':nom' => $nom,
                ':email' => $email,
                ':passwordd' => password_hash($password, PASSWORD_DEFAULT),
                ':last_login' => date('Y-m-d'),
                ':language_id' => $languageID,
                ':creation_date' => date('Y-m-d H:i:s'),
                ':position_id' => $positionID,
                ':description' => $description,
                ':age' => $age
            ]);
    
            $userID = $pdo->lastInsertId();
    
            // === If a file was uploaded, rename it and update user record ===
            if ($tempImagePath) {
                $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                $safePseudonym = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $pseudonym);
                $newFileName = "{$safePseudonym}#{$userID}." . $extension;
                $newPath = 'ressources/images/profile/' . $newFileName;
    
                if (!rename($tempImagePath, $newPath)) {
                    throw new Exception('Échec du renommage de l\'image.');
                }
    
                $imagePath = 'http://localhost:9999/' . $newPath;
    
                $stmtUpdate = $pdo->prepare('UPDATE User SET Img = :img WHERE ID = :id');
                $stmtUpdate->execute([':img' => $imagePath, ':id' => $userID]);
            }
    
            $response['success'] = true;
            $response['message'] = 'Compte créé avec succès !';
            $response['path'] = $imagePath;
    
        } catch (Exception $e) {
            if (empty($response['message'])) {
                $response['message'] = 'Une erreur est survenue.';
            }
            $response['exception'] = $e->getMessage();
        }
    
        echo json_encode($response);
    }
    
    
    

    /***************Tout le bloc de code en bas vient d'un autre projet***********/

    public static function translateIds($activities)
    {
        global $pdo;

        foreach ($activities as &$options) {
            $findcoach = $pdo->prepare("SELECT name FROM coaches WHERE id = :id");
            $findcoach->bindParam(':id', $options['coach_id']);
            $findcoach->execute();
            $options['coach_id'] = $findcoach->fetch(PDO::FETCH_ASSOC)['name'];

            $findlocation = $pdo->prepare("SELECT name FROM locations WHERE id = :id");
            $findlocation->bindParam(':id', $options['location_id']);
            $findlocation->execute();
            $options['location_id'] = $findlocation->fetch(PDO::FETCH_ASSOC)['name'];
        }
        return $activities;
    }
    public static function translateId($activity)
    {
        global $pdo;

        $findcoach = $pdo->prepare("SELECT name FROM coaches WHERE id = :id");
        $findcoach->bindParam(':id', $activity['coach_id']);
        $findcoach->execute();
        $activity['coach_id'] = $findcoach->fetch(PDO::FETCH_ASSOC)['name'];

        $findlocation = $pdo->prepare("SELECT name FROM locations WHERE id = :id");
        $findlocation->bindParam(':id', $activity['location_id']);
        $findlocation->execute();
        $activity['location_id'] = $findlocation->fetch(PDO::FETCH_ASSOC)['name'];
        return $activity;
    }

    public static function getAllActivities()
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities');
            $activities = $stmt->fetchAll();
            echo json_encode(ActivitiesController::translateIds($activities));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function getRandomActivities()
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities ORDER BY RAND() LIMIT 5');
            $randImages = $stmt->fetchAll();
            echo json_encode($randImages);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function getActivity($id)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->prepare('SELECT * FROM activities WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(ActivitiesController::translateId($activity));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getFilteredActivities($filter)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities');
            $a = $stmt->fetchAll();
            $activities = ActivitiesController::translateIds($a);
            $filtred = array();

            for ($i = 0; $i < count($activities); $i++) {
                if (($filter['niveau'] == $activities[$i]['level_id'] || $filter['niveau'] == "tous") &&
                    ($filter['lieu'] == $activities[$i]['location_id']  || $filter['lieu'] == "tous") &&
                    ($filter['coach'] == $activities[$i]['coach_id'] || $filter['coach'] == "tous") &&
                    ($filter['jour'] == $activities[$i]['schedule_day'] || $filter['jour'] == "tous")
                ) {
                    array_push($filtred, $activities[$i]);
                }
            }
            echo json_encode($filtred);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function addNewActivite($activity)
    {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON
    
        try {
            //Find a higher ID
            $stmt = $pdo->query('SELECT max(id) as max_id FROM activities');
            $highestId = $stmt->fetchColumn();
            $newId = 1+ $highestId ;

            //Find the corresponding id of the coach
            $findcoach = $pdo->prepare("SELECT id FROM coaches WHERE name = :name");
            $findcoach->bindParam(':name', $activity['coach_id']);
            $findcoach->execute();
            $coachID = $findcoach->fetchColumn();

            //Find the corresponding id of the location 
            $findlocation = $pdo->prepare("SELECT id FROM locations WHERE name = :name");
            $findlocation->bindParam(':name', $activity['location_id']);
            $findlocation->execute();
            $locationID = $findlocation->fetchColumn();

            $query = "INSERT INTO activities(id ,name, description, image, level_id, coach_id, schedule_day, schedule_time, location_id) 
                      VALUES (:id, :name, :description, :image, :level_id, :coach_id, :schedule_day, :schedule_time, :location_id);";
    
            $stmt = $pdo->prepare($query);

            $stmt->bindParam(":id", $newId);
            $stmt->bindParam(":name", $activity['name']);
            $stmt->bindParam(":description", $activity['description']);
            $stmt->bindParam(":image", $activity['image']);
            $stmt->bindParam(":level_id", $activity['level_id']);
            $stmt->bindParam(":coach_id", $coachID);
            $stmt->bindParam(":schedule_day", $activity['schedule_day']);
            $stmt->bindParam(":schedule_time", $activity['schedule_time']);
            $stmt->bindParam(":location_id",  $locationID);
    
            $stmt->execute();
    
            http_response_code(200);  // succès
            echo json_encode(["message" => "Activité ajoutée avec succès!"]);
        } catch (PDOException $e) {
            http_response_code(500);  // erreur serveur
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function modifierActivite($id, $data){
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {

            //Find the corresponding id of the coach
            $findcoach = $pdo->prepare("SELECT id FROM coaches WHERE name = :name");
            $findcoach->bindParam(':name', $data['coach_id']);
            $findcoach->execute();
            $coachID = $findcoach->fetchColumn();


            //Find the corresponding id of the location 
            $findlocation = $pdo->prepare("SELECT id FROM locations WHERE name = :name");
            $findlocation->bindParam(':name', $data['location_id']);
            $findlocation->execute();
            $locationID = $findlocation->fetchColumn();

            //$query = "UPDATE activities(name, description, image, level_id, coach_id, schedule_day, schedule_time, location_id) 
            //VALUES (:id, :name, :description, :image, :level_id, :coach_id, :schedule_day, :schedule_time, :location_id) WHERE id = :id";
            $query = "UPDATE activities SET name = :name ,description = :description ,image = :image ,
             level_id = :level_id ,coach_id = :coach_id,schedule_day = :schedule_day,schedule_time = :schedule_time,location_id = :location_id WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":image", $data['image']);
            $stmt->bindParam(":level_id", $data['level_id']);
            $stmt->bindParam(":coach_id", $coachID);
            $stmt->bindParam(":schedule_day", $data['schedule_day']);       
            $stmt->bindParam(":schedule_time", $data['schedule_time']);
            $stmt->bindParam(":location_id", $locationID);

            $stmt->execute();

            http_response_code(200);  // succès
            echo json_encode(["message" => "Activité ajoutée avec succès!"]);
        } catch (PDOException $e) {
            http_response_code(500);  // erreur serveur
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}