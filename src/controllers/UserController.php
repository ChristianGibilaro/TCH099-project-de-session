<?php
class UserController{

    public static function userConnect() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Check if email and password are provided in form data
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            echo json_encode(['success' => false, 'message' => "Paramètres 'email' et 'password' manquants."]);
            return;
        }
    
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        try {
            // Check if the user exists and retrieve their ID and hashed password
            $sql = "SELECT ID, Password FROM User WHERE Email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable."]);
                return;
            }
    
            // Verify the password
            if (!password_verify($password, $user['Password'])) {
                echo json_encode(['success' => false, 'message' => "Mot de passe incorrect."]);
                return;
            }
    
            // Generate a unique 32-character API key
            $apiKey = bin2hex(random_bytes(16));
    
            // Ensure the API key is unique
            $sql = "SELECT 1 FROM User WHERE ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            while ($stmt->fetchColumn()) {
                $apiKey = bin2hex(random_bytes(16)); // Regenerate if not unique
                $stmt->execute(['apiKey' => $apiKey]);
            }
    
            // Update the user's API key in the database
            $sql = "UPDATE User SET ApiKey = :apiKey WHERE ID = :userID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey, 'userID' => $user['ID']]);
    
            // Return the API key to the client
            echo json_encode(['success' => true, 'apiKey' => $apiKey]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
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

   public static function getUserById($userID) {
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    if (!$userID) {
        echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
        return;
    }

    try {
        // Parse the JSON body to get the desired fields
        $input = json_decode(file_get_contents('php://input'), true);
        $fields = isset($input['fields']) ? $input['fields'] : '*';

        // Validate fields input
        if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
            return;
        }

        // Build the SELECT clause
        $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));

        $sql = "SELECT $selectFields FROM User WHERE User.ID = :userID";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['userID' => $userID]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            echo json_encode(['success' => true, 'data' => $userData]);
        } else {
            echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'ID fourni."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
    }

    }

    public static function getUserByUsername($username, $input) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$username) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'username' manquant."]);
            return;
        }
    
        try {
            // Extract fields from the input
            $fields = isset($input['fields']) ? $input['fields'] : '*';
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
                return;
            }
    
            // Build the SELECT clause
            $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));
    
            $sql = "SELECT $selectFields FROM User WHERE User.Pseudo = :username";
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                echo json_encode(['success' => true, 'data' => $userData]);
            } else {
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec le nom d'utilisateur fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        }
    }

    public static function getUserByApiKey($apiKey, $input) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant."]);
            return;
        }
    
        try {
            // Extract fields from the input
            $fields = isset($input['fields']) ? $input['fields'] : '*';
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
                return;
            }
    
            // Build the SELECT clause
            $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));
    
            $sql = "SELECT $selectFields FROM User WHERE User.ApiKey = :apiKey";
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                echo json_encode(['success' => true, 'data' => $userData]);
            } else {
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'apiKey fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        }
    }

    public static function searchUsersByUsername($username, $input) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$username) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'username' manquant."]);
            return;
        }
    
        try {
            // Extract fields and limit from the input
            $fields = isset($input['fields']) ? $input['fields'] : '*';
            $limit = isset($input['limit']) ? intval($input['limit']) : 10; // Default to 10 users if not specified
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
                return;
            }
    
            // Validate limit
            if ($limit <= 0) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'limit' doit être un entier positif."]);
                return;
            }
    
            // Build the SELECT clause
            $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));
    
            // Query to find users matching the username, ordered by similarity
            $sql = "SELECT $selectFields, LOCATE(:username, User.Pseudo) AS similarity
                    FROM User
                    WHERE User.Pseudo LIKE :usernamePattern
                    ORDER BY similarity ASC, User.Pseudo ASC
                    LIMIT :limit";
    
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':usernamePattern', '%' . $username . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
    
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($users) {
                echo json_encode(['success' => true, 'data' => $users]);
            } else {
                echo json_encode(['success' => false, 'message' => "Aucun utilisateur trouvé correspondant au nom d'utilisateur fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        }
    }

    public static function userDisconnect() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Read the JSON body from the request
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Check if the API key is provided in the body
        if (!isset($input['apiKey'])) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant."]);
            return;
        }
    
        $apiKey = $input['apiKey'];
    
        try {
            // Check if the user exists with the provided API key
            $sql = "SELECT ID FROM User WHERE ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'API key fourni."]);
                return;
            }
    
            // Set the ApiKey to NULL in the database
            $sql = "UPDATE User SET ApiKey = NULL WHERE ID = :userID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $user['ID']]);
    
            // Return success response
            echo json_encode(['success' => true, 'message' => "Déconnexion réussie."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public static function modifyUserByApiKey() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Read the JSON body from the request
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Validate required fields
        if (!isset($input['apiKey']) || !isset($input['data'])) {
            echo json_encode(['success' => false, 'message' => "Paramètres 'apiKey' et 'data' manquants."]);
            return;
        }
    
        $apiKey = $input['apiKey'];
        $data = $input['data']; // This should be an associative array of fields to update
    
        try {
            // Check if the user exists with the provided API key
            $sql = "SELECT ID FROM User WHERE ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'API key fourni."]);
                return;
            }
    
            // Build the SQL query dynamically based on the provided data
            $fields = [];
            $params = ['userID' => $user['ID']];
            foreach ($data as $key => $value) {
                $fields[] = "`$key` = :$key";
                $params[$key] = $value;
            }
            $fieldsSql = implode(', ', $fields);
    
            // Update the user data
            $sql = "UPDATE User SET $fieldsSql WHERE ID = :userID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
    
            // Return success response
            echo json_encode(['success' => true, 'message' => "Les données de l'utilisateur ont été mises à jour avec succès."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}