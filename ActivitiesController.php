<?php
include_once("config.php");
class ActivitiesController
{
    public static function connexionUser(){
        global $pdo;
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            // Extraction et verification/validation des informations ou des donnees recues depuis le formulaire(front-end).
            if (isset($_POST['email'], $_POST['password'])) {
                $email_saisie = htmlspecialchars($_POST['email']);
                $password_saisie = htmlspecialchars($_POST['password']);

                //Verification si l'utilisateur/email existe dans la basse de donnees.
                try {
                    $stmtUser = $pdo->prepare('SELECT * FROM User WHERE Pseudo = :pseudo OR  Email = :email');
                    //Ici on cree deux variables pour une meme saisie(user ou email) pour verifier si elle un user ou un email. Une seule variable entraine une erreur dans la requete.
                    $stmtUser->bindParam(':pseudo',$email_saisie);
                    $stmtUser->bindParam(':email',$email_saisie);

                    $stmtUser->execute();

                    $user_infos = $stmtUser->fetch(PDO::FETCH_ASSOC);

                    //Si un utilisateur existe, on verifie si le hashage du mot de passe saisi correspond au hashage de la BD.
                    if ($user_infos && password_verify($password_saisie, $user_infos['Password'])) {
                        //On met a jour la date du last_login si l'utilisateur se connecte avec succes
                        $stmtUser = $pdo->prepare('UPDATE User SET Last_login = :last_login 
                                                            WHERE Pseudo = :pseudo OR  Email = :email');
                        
                        // Stocker des informations dans la session
                        $_SESSION['user_id'] = $user_infos['ID'];
                        $_SESSION['user_pseudo'] = $user_infos['Pseudo'];

                        $stmtUser->execute([':last_login' => date('Y-m-d H:i:s'),':pseudo'=>$email_saisie, ':email'=>$email_saisie]);
                        echo json_encode('Connexion réussie ! Bienvenue, ' . htmlspecialchars($user_infos['Pseudo']) . '.');
                        echo json_encode($user_infos);
                    } else {
                        echo json_encode('Nom d\'utilisateur ou mot de passe incorrect.');
                    }

                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Erreur est survenue lors du traitement de la requete.']);
                }

            } else {
                echo json_encode(['success' => false, 'message' => 'Champs d\'utilisateur ou du mot de passe est vide!']);
            }
        } else {
            //session_destroy();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'OPERATION ECHOUEE: Base de donnees introuvable.']);
        }
    }
    public static function creerUser() {
        global $pdo;
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Verification si l'image est vide ou pas. Donc, il est obligatoire de televerser une image ou il va falloir reanger le code
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = basename($_FILES['image']['name']);
                $imageFolder = 'ressources/images/profile/';
                $videoFolder = 'ressources/videos/';

                // Création des dossiers s'ils n'existent pas. On a pas vraiment besoins puisqu'on predefinit les repertoires d,avance
                if (!file_exists($imageFolder)) {
                    mkdir($imageFolder, 0755, true);
                }
                if (!file_exists($videoFolder)) {
                    mkdir($videoFolder, 0755, true);
                }

                // Détection du type MIME(extention:png, jpeg, gif, avi...etc.)
                $fileType = mime_content_type($fileTmpPath);
                
                // Validation et répertoire cible
                $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $allowedVideoTypes = ['video/mp4', 'video/x-msvideo', 'video/webm'];
    
                // Déplacement du fichier dans le bon dossier selon son type(image ou video)
                if (in_array($fileType, $allowedImageTypes)) {
                    $destinationPath = $imageFolder . $fileName;
                } elseif (in_array($fileType, $allowedVideoTypes)) {
                    $destinationPath = $videoFolder . $fileName;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Type de fichier non pris en charge.']);
                    return;
                }

                if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                    echo json_encode(['success' => true, 'message' => 'Fichier téléchargé avec succès.', 'path' => $destinationPath]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du fichier.']);
                }
    
                // Extraction et verification/validation des informations ou des donnees recues depuis le formulaire(front-end) pour les inserer dans BD.
                if (isset($_POST['pseudonym'], $_POST['nom'], $_POST['email'], $_POST['password'], $_POST['description'], $_POST['age'])) {
                    $pseudonym = htmlspecialchars($_POST['pseudonym']);
                    $nom = htmlspecialchars($_POST['nom']);
                    $email = htmlspecialchars($_POST['email']);
                    $password = htmlspecialchars($_POST['password']);
                    $description = htmlspecialchars($_POST['description']);
                    $age = htmlspecialchars($_POST['age']);

                    //$position = 1;

                    //echo json_encode($nom);

                    /*if (!isset($_POST['description'])){
                        $description = null;
                    }
                    if (!isset($_POST['age'])){
                        $age = null;
                    }*/
                    
                    /*$stmt = $pdo->prepare('INSERT INTO Position (Name, Country, State, City, Street, Number, GPS, Local_Time)
                                        VALUES (:nom, :country, :state, :city, :street, :number, :gps, :local_time)');
                    $stmt->execute([
                        //':id' => null,
                        ':nom' => $nom,
                        ':country' => 'Canada',
                        ':state' => 'Quebec',
                        ':city' => 'Montreal',
                        ':street' => 'Centre Ville',
                        ':number' => 1230,
                        ':gps' => null,
                        ':local_time' => 17,
                    ]);

    
                    // Insert into database
                    $stmt = $pdo->prepare('INSERT INTO User (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                                           VALUES (:img, :pseudo, :nom, :email, :passwordd, :last_login, (SELECT ID FROM Language WHERE ID = :language_id), :creation_date, (SELECT id FROM Position WHERE name = :nom), :description, :age)');
                    $stmt->execute([
                        //':id' => null,
                        ':img' => 'http://localhost:9999/'.$destinationPath,
                        ':pseudo' => $pseudonym,
                        ':nom' => $nom,
                        ':email' => $email,
                        ':passwordd' => password_hash($password, PASSWORD_DEFAULT),//$password,
                        ':last_login' => '2025-04-01',
                        ':language_id' => 1,
                        ':creation_date' => date('Y-m-d H:i:s'),//null,
                        ':position_id' => 10,
                        ':description' => $description,
                        ':age' => '2025-04-01',//$age
                    ]);
    
                    echo json_encode(['success' => true, 'message' => 'Creation compte reussie !']);*/
                    
                    try {
                        $stmtPosition = $pdo->prepare('INSERT INTO Position (Name) VALUES (:name)');
                        $stmtPosition->execute([':name' => $nom]);
                        $positionID = $pdo->lastInsertId();
                    } catch (PDOException $e) {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la position.']);
                        return;
                    }

                    try {
                        $stmtUser = $pdo->prepare('INSERT INTO User (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                                                VALUES (:img, :pseudo, :nom, :email, :passwordd, :last_login, :language_id, :creation_date, :position_id, :description, :age)');
                        $stmtUser->execute([
                            ':img' => 'http://localhost:9999/'.$destinationPath,
                            ':pseudo' => $pseudonym,
                            ':nom' => $nom,
                            ':email' => $email,
                            ':passwordd' => password_hash($password, PASSWORD_DEFAULT),
                            ':last_login' => '2025-04-01',
                            ':language_id' => 1,
                            ':creation_date' => date('Y-m-d H:i:s'),
                            ':position_id' => $positionID,
                            ':description' => $description,
                            ':age' => $age
                        ]);
                        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès !']);
                    } catch (PDOException $e) {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
                    }

                } else {
                    echo json_encode(['success' => false, 'message' => 'La creation du compte a echouee!']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Image/Video n\'a pas pu etre telechargee.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'OPERATION ECHOUEE.']);
        }
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
