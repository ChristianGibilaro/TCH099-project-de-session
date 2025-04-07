<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class ActiviteController
{

    public static function creerActivite()
    {
        global $pdo;
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer une activité.']);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Corps de requête invalide (JSON attendu).']);
            return;
        }

        $title        = $data['title']        ?? null;
        $isSport      = $data['isSport']      ?? null;  
        $mainImg      = $data['main_img']     ?? null;
        $description  = $data['description']  ?? null;

        if (!$title || $isSport === null || !$mainImg || !$description) {
            echo json_encode([
                'success' => false,
                'message' => "Champs 'title', 'isSport', 'main_img', 'description' obligatoires."
            ]);
            return;
        }

        $bitValue = ($isSport) ? "b'1'" : "b'0'";

        $logoImg        = $data['logo_img']        ?? null;
        $pointValue     = $data['point_value']     ?? null;
        $word4player    = $data['word_4_player']   ?? null;
        $word4teammate  = $data['word_4_teammate'] ?? null;
        $word4playing   = $data['word_4_playing']  ?? null;
        $liveUrl        = $data['live_url']        ?? null;
        $liveDesc       = $data['live_desc']       ?? null;
        $mainColor      = $data['main_color']      ?? null;
        $secondColor    = $data['second_color']    ?? null;
        $friendMain     = $data['friend_main_color']   ?? null;
        $friendSecond   = $data['friend_second_color'] ?? null;

        try {
            // Préparer la requête INSERT
            $sql = "
                INSERT INTO Activity
                  (Title, IsSport, Main_Img, Description,
                   Logo_Img, Point_Value, Word_4_Player, Word_4_Teammate,
                   Word_4_Playing, Live_url, Live_Desc, Main_Color,
                   Second_Color, Friend_Main_Color, Friend_Second_Color)
                VALUES
                  (:title, $bitValue, :mainImg, :descr,
                   :logoImg, :pVal, :wPlayer, :wTeammate,
                   :wPlaying, :lUrl, :lDesc, :mColor,
                   :sColor, :fMain, :fSecond)
            ";
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':title',    $title);
            $stmt->bindValue(':mainImg',  $mainImg);
            $stmt->bindValue(':descr',    $description);
            $stmt->bindValue(':logoImg',  $logoImg);
            $stmt->bindValue(':pVal',     $pointValue);
            $stmt->bindValue(':wPlayer',  $word4player);
            $stmt->bindValue(':wTeammate',$word4teammate);
            $stmt->bindValue(':wPlaying', $word4playing);
            $stmt->bindValue(':lUrl',     $liveUrl);
            $stmt->bindValue(':lDesc',    $liveDesc);
            $stmt->bindValue(':mColor',   $mainColor);
            $stmt->bindValue(':sColor',   $secondColor);
            $stmt->bindValue(':fMain',    $friendMain);
            $stmt->bindValue(':fSecond',  $friendSecond);

            $stmt->execute();
            $newId = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => "Activité créée avec succès.",
                'activity_id' => $newId
            ]);

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => "Erreur DB : " . $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer une activité via son ID (GET)
     */
    public static function getActivite($id)
    {
        global $pdo;
        header('Content-Type: application/json');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'id' manquant"]);
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM Activity WHERE ID = :actID");
            $stmt->execute([':actID' => $id]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($activity) {
                echo json_encode(['success' => true, 'activity' => $activity]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Aucune activité trouvée pour l'ID = $id"
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => "Erreur DB : " . $e->getMessage()
            ]);
        }
    }
    /*
    public static function creerUser() {
        global $pdo;
        header('Content-Type: application/json');
    
        // Vérifier que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'OPERATION ECHOUEE.']);
            return;
        }
        
        // Traitement optionnel du fichier (image ou vidéo)
        $imageUrl = null; // Par défaut, aucun fichier
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $imageFolder = 'ressources/images/';
            $videoFolder = 'ressources/videos/';
    
            // Détection du type MIME
            $fileType = mime_content_type($fileTmpPath);
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $allowedVideoTypes = ['video/mp4', 'video/x-msvideo', 'video/webm'];
    
            // Choix du répertoire cible en fonction du type MIME
            if (in_array($fileType, $allowedImageTypes)) {
                $destinationPath = $imageFolder . $fileName;
            } elseif (in_array($fileType, $allowedVideoTypes)) {
                $destinationPath = $videoFolder . $fileName;
            } else {
                // Si le type de fichier n'est pas autorisé, on renvoie une erreur.
                echo json_encode(['success' => false, 'message' => 'Type de fichier non pris en charge.']);
                return;
            }
    
            // Tente de déplacer le fichier vers le dossier cible
            if (move_uploaded_file($fileTmpPath, $destinationPath)) {
                $imageUrl = 'http://localhost:8000/' . $destinationPath;
            } 
            // Sinon, on laisse $imageUrl à null (fichier non disponible)
        }
        
        // Vérification des champs obligatoires
        if (!isset($_POST['pseudonym'], $_POST['nom'], $_POST['email'], $_POST['password'])) {
            echo json_encode(['success' => false, 'message' => 'Les champs obligatoires ne sont pas remplis.']);
            return;
        }
        
        // Récupération et nettoyage des données
        $pseudonym = htmlspecialchars(trim($_POST['pseudonym']));
        $nom       = htmlspecialchars(trim($_POST['nom']));
        $email     = htmlspecialchars(trim($_POST['email']));
        $password  = htmlspecialchars(trim($_POST['password']));
        
        // Champs optionnels : description et age. Si non fournis ou vides, on met null.
        $description = (isset($_POST['description']) && trim($_POST['description']) !== '')
                            ? htmlspecialchars(trim($_POST['description']))
                            : null;
        $age = (isset($_POST['age']) && trim($_POST['age']) !== '')
                            ? htmlspecialchars(trim($_POST['age']))
                            : null;
        
        // Préparation de la requête d'insertion dans la base de données
        $stmt = $pdo->prepare('INSERT INTO User (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                               VALUES (:img, :pseudo, :nom, :email, :passwordd, :last_login, :language_id, :creation_date, :position_id, :description, :age)');
        
        $result = $stmt->execute([
            ':img'           => $imageUrl,  // Peut être null si aucun fichier n'est téléversé
            ':pseudo'        => $pseudonym,
            ':nom'           => $nom,
            ':email'         => $email,
            ':passwordd'     => password_hash($password, PASSWORD_DEFAULT),
            ':last_login'    => date('Y-m-d'),
            ':language_id'   => 1,
            ':creation_date' => '1990-01-01', // Vous pouvez adapter cette valeur
            ':position_id'   => 10,
            ':description'   => $description,
            ':age'           => $age
        ]);
        
        // Envoi de la réponse JSON
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Création compte réussie !',
                'nom'     => $nom
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'La création du compte a échoué!']);
        }
    }
        */
    
}

?>
