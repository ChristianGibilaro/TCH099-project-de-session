<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class ActivitiesControllerO
{

    public static function creerUser() {
        global $pdo;
        header('Content-Type: application/json');
        /*header('Access-Control-Allow-Origin: *');  
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); */ 
        

    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Verification si l'image est vide ou pas. Donc, il est obligatoire de televerser une image ou il va falloir reanger le code
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = basename($_FILES['image']['name']);
                $imageFolder = 'ressources/images/';
                $videoFolder = 'ressources/videos/';

                // Création des dossiers s'ils n'existent pas. On a pas vraiment besoins puisqu'on predefinit les repertoires d,avance
                /*if (!file_exists($imageFolder)) {
                    mkdir($imageFolder, 0755, true);
                }
                if (!file_exists($videoFolder)) {
                    mkdir($videoFolder, 0755, true);
                }*/

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

                    echo json_encode($nom);

                    /*if (!isset($_POST['description'])){
                        $description = null;
                    }
                    if (!isset($_POST['age'])){
                        $age = null;
                    }*/

    
                    // Insert into database
                    $stmt = $pdo->prepare('INSERT INTO User (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                                           VALUES (:img, :pseudo, :nom, :email, :passwordd, :last_login, :language_id, :creation_date, :position_id, :description, :age)');
                    $stmt->execute([
                        //':id' => null,
                        ':img' => 'http://localhost:8000/'.$destinationPath,
                        ':pseudo' => $pseudonym,
                        ':nom' => $nom,
                        ':email' => $email,
                        ':passwordd' => password_hash($password, PASSWORD_DEFAULT),//$password,
                        ':last_login' => date('Y-m-d'),
                        ':language_id' => 1,
                        ':creation_date' => '1990-01-01',//null,
                        ':position_id' => 10,
                        ':description' => $description,
                        ':age' => $age
                    ]);
    
                    echo json_encode(['success' => true, 'message' => 'Creation compte reussie !'].$nom);
                } else {
                    echo json_encode(['success' => false, 'message' => 'La creation du compte a echouee!']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Image/Video n\'a pas pu etre telechargee.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'OPERATION ECHOUEE.']);
        }
    }

}
