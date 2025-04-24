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
        header('Access-Control-Allow-Origin: *'); // Added for CORS consistency
        header('Content-Type: application/json; charset=utf-8'); // Added charset for consistency
    
        // Initialize response structure
        $response = ['success' => false, 'message' => '', 'errors' => []];
        $tempImagePath = null; // Initialize here for cleanup in catch blocks
        $tempBackgImagePath = null; // For background image
        $baseDir = __DIR__ . '/../../'; // Define base directory early
    
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new Exception('Méthode HTTP non autorisée.');
            }
    
            // === Validate fields ===
            // 'backg_image' and 'backg_imageLink' are optional, so not in requiredFields
            $requiredFields = ['pseudonym', 'nom', 'email', 'password', 'password2', 'description', 'language_name', 'age', 'agreement'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $response['errors'][$field] = "Le champ '$field' est requis.";
                }
            }
    
            if (!empty($_POST['password']) && $_POST['password'] !== $_POST['password2']) {
                $response['errors']['password2'] = 'Les mots de passe ne correspondent pas.';
            }
    
            if (empty($_POST['agreement']) || $_POST['agreement'] !== 'accepted') {
                $response['errors']['agreement'] = 'Vous devez accepter les conditions.';
            }
    
            // === Prepare data (only if basic validation passes) ===
            if (empty($response['errors'])) {
                $pseudonym = htmlspecialchars($_POST['pseudonym']);
                $nom = htmlspecialchars($_POST['nom']);
                $email = htmlspecialchars($_POST['email']);
                $password = $_POST['password'];
                $description = htmlspecialchars($_POST['description']);
                $age = htmlspecialchars($_POST['age']);
                $languageName = htmlspecialchars($_POST['language_name']);
                $positionName = isset($_POST['position_name']) ? htmlspecialchars($_POST['position_name']) : null;
                $imagePath = null;
                $backgImagePath = null; // Initialize background image path
                $originalFileName = null;
                $originalBackgFileName = null; // For background image
                $languageID = null;
                $positionID = null;

                // === Find Language ID ===
                $stmtLang = $pdo->prepare('SELECT ID FROM Language WHERE Name = :name');
                $stmtLang->execute([':name' => $languageName]);
                $languageResult = $stmtLang->fetch(PDO::FETCH_ASSOC);
                if (!$languageResult) {
                    $response['errors']['language_name'] = "Langue '$languageName' non trouvée.";
                } else {
                    $languageID = $languageResult['ID'];
                }

                // === Find Position ID (Optional) ===
                if (!empty($positionName)) {
                    $stmtPos = $pdo->prepare('SELECT ID FROM Position WHERE Name = :name');
                    $stmtPos->execute([':name' => $positionName]);
                    $positionResult = $stmtPos->fetch(PDO::FETCH_ASSOC);
                    if ($positionResult) {
                        $positionID = $positionResult['ID'];
                    } else {
                        $positionID = null;
                    }
                } else {
                    $positionID = null;
                }
            }

            // Check for errors accumulated so far (including language/position lookup)
            if (!empty($response['errors'])) {
                // Set message specifically for validation errors
                $response['message'] = 'Erreur de validation. Veuillez corriger les champs indiqués.';
                throw new Exception('Champs manquants ou invalides.'); // Throw to trigger catch block
            }
    
            // === Handle file upload for profile image ===
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $originalFileName = basename($_FILES['image']['name']);
                $fileType = mime_content_type($fileTmpPath);
    
                $imageFolder = $baseDir . 'ressources/images/profile/';
                $videoFolder = $baseDir . 'ressources/videos/'; // Consider if videos are profile or background
    
                if (!is_dir($imageFolder)) mkdir($imageFolder, 0775, true);
    
                $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
                $targetFolder = null;
                if (in_array($fileType, $allowedImageTypes)) {
                    $targetFolder = $imageFolder;
                } else {
                    throw new Exception("Type de fichier non pris en charge pour l'image de profil: $fileType");
                }
    
                $tempFileName = uniqid('temp_profile_', true) . '_' . preg_replace('/[^a-zA-Z0-9.\-]/', '_', $originalFileName);
                $destinationPath = $targetFolder . $tempFileName;
    
                if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                    throw new Exception('Impossible de déplacer le fichier image de profil téléchargé. Vérifiez les permissions.');
                }
                $tempImagePath = $destinationPath; // Store temp path for later rename/cleanup
    
            } elseif (!empty($_POST['imageLink'])) {
                $imagePath = filter_var(trim($_POST['imageLink']), FILTER_VALIDATE_URL) ? trim($_POST['imageLink']) : null;
                if (!$imagePath) {
                     throw new Exception('Lien d\'image de profil fourni invalide.');
                }
            } else {
                 $imagePath = null; // Or set a default path
            }

            // === Handle file upload for background image (Optional) ===
            if (isset($_FILES['backg_image']) && $_FILES['backg_image']['error'] === UPLOAD_ERR_OK) {
                $backgFileTmpPath = $_FILES['backg_image']['tmp_name'];
                $originalBackgFileName = basename($_FILES['backg_image']['name']);
                $backgFileType = mime_content_type($backgFileTmpPath);

                $backgImageFolder = $baseDir . 'ressources/images/background/'; // Separate folder recommended

                if (!is_dir($backgImageFolder)) mkdir($backgImageFolder, 0775, true);

                $allowedBackgImageTypes = ['image/jpeg', 'image/png', 'image/gif']; // Can be same or different

                if (!in_array($backgFileType, $allowedBackgImageTypes)) {
                    throw new Exception("Type de fichier non pris en charge pour l'image de fond: $backgFileType");
                }

                $tempBackgFileName = uniqid('temp_backg_', true) . '_' . preg_replace('/[^a-zA-Z0-9.\-]/', '_', $originalBackgFileName);
                $backgDestinationPath = $backgImageFolder . $tempBackgFileName;

                if (!move_uploaded_file($backgFileTmpPath, $backgDestinationPath)) {
                    throw new Exception('Impossible de déplacer le fichier image de fond téléchargé. Vérifiez les permissions.');
                }
                $tempBackgImagePath = $backgDestinationPath; // Store temp path

            } elseif (!empty($_POST['backg_imageLink'])) {
                $backgImagePath = filter_var(trim($_POST['backg_imageLink']), FILTER_VALIDATE_URL) ? trim($_POST['backg_imageLink']) : null;
                if (!$backgImagePath) {
                     error_log('Lien d\'image de fond fourni invalide.');
                     $backgImagePath = null;
                }
            } else {
                $backgImagePath = null;
            }
    
            // === Insert user ===
            // Use 'pending' or null placeholders for images that will be updated after getting the ID
            $stmtUser = $pdo->prepare('INSERT INTO User (Img, Backg_Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, Creation_Date, PositionID, Description, Birth)
                                       VALUES (:img, :backg_img, :pseudo, :nom, :email, :passwordd, :last_login, :language_id, :creation_date, :position_id, :description, :age)');
            $stmtUser->execute([
                ':img' => $imagePath ?? 'pending', // Use link if provided, else 'pending' if file upload
                ':backg_img' => $backgImagePath ?? ($tempBackgImagePath ? 'pending_backg' : null), // Use link, 'pending' for upload, or null
                ':pseudo' => $pseudonym,
                ':nom' => $nom,
                ':email' => $email,
                ':passwordd' => password_hash($password, PASSWORD_DEFAULT),
                ':last_login' => date('Y-m-d H:i:s'),
                ':language_id' => $languageID,
                ':creation_date' => date('Y-m-d H:i:s'),
                ':position_id' => $positionID,
                ':description' => $description,
                ':age' => $age
            ]);
    
            $userID = $pdo->lastInsertId();
            if (!$userID) {
                throw new Exception("Impossible de récupérer l'ID de l'utilisateur après l'insertion.");
            }
    
            // === Rename uploaded profile file and update user record ===
            if ($tempImagePath) {
                $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                $safePseudonym = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $pseudonym);
                $newFileName = "profile_{$safePseudonym}_{$userID}." . ($extension ? strtolower($extension) : 'jpg'); // Added 'profile_' prefix
                
                $finalRelativePath = 'ressources/images/profile/' . $newFileName;
                $finalAbsolutePath = $baseDir . $finalRelativePath;

                if (!rename($tempImagePath, $finalAbsolutePath)) {
                    error_log("Échec du renommage de '$tempImagePath' vers '$finalAbsolutePath'");
                    $imagePath = null; // Indicate failure
                    $tempImagePath = null; // Prevent deletion attempt in catch block
                } else {
                    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                    $imagePath = $baseUrl . '/' . str_replace('\\', '/', $finalRelativePath); // Ensure forward slashes for URL

                    $stmtUpdate = $pdo->prepare('UPDATE User SET Img = :img WHERE ID = :id');
                    $stmtUpdate->execute([':img' => $imagePath, ':id' => $userID]);
                    $tempImagePath = null; // Successfully renamed, don't delete temp file
                }
            }

            // === Rename uploaded background file and update user record ===
            if ($tempBackgImagePath) {
                $backgExtension = pathinfo($originalBackgFileName, PATHINFO_EXTENSION);
                $safePseudonym = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $pseudonym);
                $newBackgFileName = "backg_{$safePseudonym}_{$userID}." . ($backgExtension ? strtolower($backgExtension) : 'jpg'); // Added 'backg_' prefix

                $finalBackgRelativePath = 'ressources/images/background/' . $newBackgFileName;
                $finalBackgAbsolutePath = $baseDir . $finalBackgRelativePath;

                if (!rename($tempBackgImagePath, $finalBackgAbsolutePath)) {
                    error_log("Échec du renommage de '$tempBackgImagePath' vers '$finalBackgAbsolutePath'");
                    $backgImagePath = null; // Indicate failure
                    $tempBackgImagePath = null; // Prevent deletion attempt
                } else {
                    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                    $backgImagePath = $baseUrl . '/' . str_replace('\\', '/', $finalBackgRelativePath); // Ensure forward slashes for URL

                    $stmtUpdateBackg = $pdo->prepare('UPDATE User SET Backg_Img = :backg_img WHERE ID = :id');
                    $stmtUpdateBackg->execute([':backg_img' => $backgImagePath, ':id' => $userID]);
                    $tempBackgImagePath = null; // Successfully renamed
                }
            }
    
            $response['success'] = true;
            $response['message'] = 'Compte créé avec succès !';
            $response['userID'] = $userID;
            $response['imagePath'] = $imagePath; // Final profile image path/URL or null/link
            $response['backgImagePath'] = $backgImagePath; // Final background image path/URL or null/link
    
        } catch (PDOException $e) {
            http_response_code(500);
            $response['message'] = 'Erreur de base de données.';
            error_log("PDOException in creerUser: " . $e->getMessage() . " SQLSTATE: " . $e->getCode());
             if ($tempImagePath && file_exists($tempImagePath)) {
                 unlink($tempImagePath);
             }
             if ($tempBackgImagePath && file_exists($tempBackgImagePath)) { // Cleanup background temp file
                 unlink($tempBackgImagePath);
             }
        }
        catch (Exception $e) {
            $exceptionMessage = $e->getMessage();
            if ($exceptionMessage === 'Méthode HTTP non autorisée.') {
                http_response_code(405);
                $response['message'] = $exceptionMessage;
            } elseif ($exceptionMessage === 'Champs manquants ou invalides.') {
                http_response_code(400); // Bad Request
            } else {
                http_response_code(500); // Internal Server Error for other exceptions
                $response['message'] = $exceptionMessage; // Use the specific error message
                error_log("Exception in creerUser: " . $exceptionMessage); // Log other errors
            }
             if ($tempImagePath && file_exists($tempImagePath)) { // Cleanup temp file on any exception
                 unlink($tempImagePath);
             }
             if ($tempBackgImagePath && file_exists($tempBackgImagePath)) { // Cleanup background temp file on any exception
                 unlink($tempBackgImagePath);
             }
        }
    
        // Always encode the $response array, which contains success, message, and errors (if any)
        echo json_encode($response);
    }

   public static function getUserById($userID) {
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    if (!$userID) {
        http_response_code(400); // Bad request if ID is missing
        echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
        return;
    }

    try {
        // Parse the JSON body to get the desired fields
        $input = json_decode(file_get_contents('php://input'), true);
        $fields = isset($input['fields']) ? $input['fields'] : '*';

        // Validate fields input
        if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
            return;
        }

        // Basic validation against known columns (optional but recommended)
        $allowedFields = ['ID', 'Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
        $selectFields = '*';
        if (is_array($fields)) {
            $validFields = array_intersect($fields, $allowedFields);
            if (empty($validFields)) {
                 http_response_code(400);
                 echo json_encode(['success' => false, 'message' => "Aucun champ valide fourni dans 'fields'."]);
                 return;
            }
            // Sanitize field names (though PDO prepares values, good practice for identifiers if needed)
            $selectFields = implode(', ', array_map(function($field) { return "`" . str_replace("`", "``", $field) . "`"; }, $validFields));
        }


        $sql = "SELECT $selectFields FROM User WHERE User.ID = :userID";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['userID' => $userID]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            echo json_encode(['success' => true, 'data' => $userData]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'ID fourni."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("PDOException in getUserById: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
    } catch (Exception $e) {
         http_response_code(500);
         error_log("Exception in getUserById: " . $e->getMessage());
         echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
    }

    }

    public static function getUserByUsername($username) { // Removed $input, read from body
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$username) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'username' manquant dans l'URL."]);
            return;
        }
    
        try {
             // Parse the JSON body to get the desired fields
            $input = json_decode(file_get_contents('php://input'), true);
            $fields = isset($input['fields']) ? $input['fields'] : '*';
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' dans le body doit être '*' ou un tableau non vide."]);
                return;
            }
    
             // Basic validation against known columns (optional but recommended)
            $allowedFields = ['ID', 'Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
            $selectFields = '*';
            if (is_array($fields)) {
                $validFields = array_intersect($fields, $allowedFields);
                if (empty($validFields)) {
                     http_response_code(400);
                     echo json_encode(['success' => false, 'message' => "Aucun champ valide fourni dans 'fields'."]);
                     return;
                }
                $selectFields = implode(', ', array_map(function($field) { return "`" . str_replace("`", "``", $field) . "`"; }, $validFields));
            }
    
            $sql = "SELECT $selectFields FROM User WHERE User.Pseudo = :username";
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                echo json_encode(['success' => true, 'data' => $userData]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec le nom d'utilisateur fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("PDOException in getUserByUsername: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
        } catch (Exception $e) {
             http_response_code(500);
             error_log("Exception in getUserByUsername: " . $e->getMessage());
             echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
        }
    }

    public static function getUserByApiKey($apiKey) { // Removed $input, read from body
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$apiKey) {
             http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant dans l'URL."]);
            return;
        }
    
        try {
            // Parse the JSON body to get the desired fields
            $input = json_decode(file_get_contents('php://input'), true);
            $fields = isset($input['fields']) ? $input['fields'] : '*';
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                 http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' dans le body doit être '*' ou un tableau non vide."]);
                return;
            }
    
            // Basic validation against known columns (optional but recommended)
            $allowedFields = ['ID', 'Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
            $selectFields = '*';
            if (is_array($fields)) {
                $validFields = array_intersect($fields, $allowedFields);
                if (empty($validFields)) {
                     http_response_code(400);
                     echo json_encode(['success' => false, 'message' => "Aucun champ valide fourni dans 'fields'."]);
                     return;
                }
                $selectFields = implode(', ', array_map(function($field) { return "`" . str_replace("`", "``", $field) . "`"; }, $validFields));
            }
    
            $sql = "SELECT $selectFields FROM User WHERE User.ApiKey = :apiKey";
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                echo json_encode(['success' => true, 'data' => $userData]);
            } else {
                 http_response_code(404);
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'apiKey fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("PDOException in getUserByApiKey: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
        } catch (Exception $e) {
             http_response_code(500);
             error_log("Exception in getUserByApiKey: " . $e->getMessage());
             echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
        }
    }

    public static function searchUsersByUsername($username) { // Removed $input, read from body
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$username) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'username' manquant dans l'URL."]);
            return;
        }
    
        try {
            // Parse the JSON body to get the desired fields and limit
            $input = json_decode(file_get_contents('php://input'), true);
            $fields = isset($input['fields']) ? $input['fields'] : '*';
            $limit = isset($input['limit']) ? intval($input['limit']) : 10; // Default limit
    
            // Validate fields input
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' dans le body doit être '*' ou un tableau non vide."]);
                return;
            }
    
            // Validate limit
            if ($limit <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Paramètre 'limit' doit être un entier positif."]);
                return;
            }
    
            // Basic validation against known columns (optional but recommended)
            $allowedFields = ['ID', 'Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
            $selectFields = '*';
            if (is_array($fields)) {
                $validFields = array_intersect($fields, $allowedFields);
                if (empty($validFields)) {
                     http_response_code(400);
                     echo json_encode(['success' => false, 'message' => "Aucun champ valide fourni dans 'fields'."]);
                     return;
                }
                // Add similarity for ordering even if not explicitly requested in output fields
                $selectFields = implode(', ', array_map(function($field) { return "`User`.`" . str_replace("`", "``", $field) . "`"; }, $validFields));
            } else {
                 $selectFields = '`User`.*'; // Select all user fields if '*'
            }

    
            // Query to find users matching the username, ordered by similarity
            $sql = "SELECT $selectFields, LOCATE(:username, `User`.`Pseudo`) AS similarity
                    FROM User
                    WHERE `User`.`Pseudo` LIKE :usernamePattern
                    ORDER BY similarity ASC, `User`.`Pseudo` ASC
                    LIMIT :limit";
    
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':usernamePattern', '%' . $username . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
    
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Remove similarity field if '*' wasn't requested or if specific fields were requested without 'similarity'
             if ($fields !== '*' && is_array($fields) && !in_array('similarity', $fields)) {
                 foreach ($users as &$user) {
                     unset($user['similarity']);
                 }
             }

            if ($users) {
                echo json_encode(['success' => true, 'data' => $users]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => "Aucun utilisateur trouvé correspondant au nom d'utilisateur fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
             error_log("PDOException in searchUsersByUsername: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
        } catch (Exception $e) {
             http_response_code(500);
             error_log("Exception in searchUsersByUsername: " . $e->getMessage());
             echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
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
             http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant dans le body."]);
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
                 http_response_code(404); // Or 401 Unauthorized if API key is invalid
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable ou clé API invalide."]);
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
             error_log("PDOException in userDisconnect: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
        } catch (Exception $e) { // Catch potential general errors, though less likely here
            http_response_code(500);
             error_log("Exception in userDisconnect: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
        }
    }

    public static function modifyUserByApiKey() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Read the JSON body from the request
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Validate required fields in the body
        if (!isset($input['apiKey']) || !isset($input['data']) || !is_array($input['data']) || empty($input['data'])) {
             http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètres 'apiKey' et 'data' (tableau non vide) manquants ou invalides dans le body."]);
            return;
        }
    
        $apiKey = $input['apiKey'];
        $data = $input['data']; // Associative array of fields to update
    
        // --- Whitelist fields that can be modified ---
        $allowedUpdateFields = ['Pseudo', 'Name', 'Email', 'Description', 'Birth', 'LanguageID', 'PositionID', 'Img']; // Add/remove fields as needed
        // Password should likely have a separate endpoint/process
    
        try {
            // Check if the user exists with the provided API key
            $sql = "SELECT ID FROM User WHERE ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                 http_response_code(404); // Or 401 Unauthorized
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable ou clé API invalide."]);
                return;
            }
    
            // Build the SQL query dynamically based on the *allowed* provided data
            $fields = [];
            $params = ['userID' => $user['ID']];
            $updateErrors = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedUpdateFields)) {
                    // --- Add specific validation per field if necessary ---
                    if ($key === 'Email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $updateErrors['Email'] = 'Format d\'email invalide.';
                        continue; // Skip this field
                    }
                     if ($key === 'LanguageID' || $key === 'PositionID') {
                         // Optional: Check if the provided ID exists in the respective table
                         $tableName = ($key === 'LanguageID') ? 'Language' : 'Position';
                         $checkStmt = $pdo->prepare("SELECT 1 FROM `$tableName` WHERE ID = :id");
                         $checkStmt->execute(['id' => $value]);
                         if (!$checkStmt->fetchColumn() && $value !== null) { // Allow setting PositionID to null
                             $updateErrors[$key] = "ID invalide pour '$tableName'.";
                             continue;
                         }
                     }
                     if ($key === 'Img' && !filter_var($value, FILTER_VALIDATE_URL) && !empty($value)) { // Allow empty Img?
                         $updateErrors['Img'] = 'Format d\'URL invalide pour Img.';
                         continue;
                     }

                    // Sanitize key for SQL statement (basic backtick escaping)
                    $safeKey = "`" . str_replace("`", "``", $key) . "`";
                    $fields[] = "$safeKey = :$key";
                    // Sanitize value before adding to params (htmlspecialchars or type casting)
                    $params[$key] = is_string($value) ? htmlspecialchars($value) : $value;
                } else {
                    // Optionally log or ignore attempts to update disallowed fields
                     error_log("Attempt to update disallowed field '$key' by user ID {$user['ID']}");
                }
            }

             // If validation errors occurred during field processing
             if (!empty($updateErrors)) {
                 http_response_code(400);
                 echo json_encode(['success' => false, 'message' => 'Erreurs de validation des données fournies.', 'errors' => $updateErrors]);
                 return;
             }

            // Check if there are any valid fields to update
            if (empty($fields)) {
                 http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Aucun champ valide à mettre à jour n'a été fourni."]);
                return;
            }
    
            $fieldsSql = implode(', ', $fields);
    
            // Update the user data
            $sql = "UPDATE User SET $fieldsSql WHERE ID = :userID";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($params);
    
            if ($success) {
                echo json_encode(['success' => true, 'message' => "Les données de l'utilisateur ont été mises à jour avec succès."]);
            } else {
                 // This might indicate a more specific DB error caught by PDOException below
                 throw new Exception("La mise à jour de l'utilisateur a échoué sans PDOException.");
            }

        } catch (PDOException $e) {
            http_response_code(500);
             error_log("PDOException in modifyUserByApiKey: " . $e->getMessage() . " SQLSTATE: " . $e->getCode());
            // Check for specific constraint violations (e.g., unique email)
            if ($e->getCode() == '23000') { // Integrity constraint violation
                 http_response_code(409); // Conflict
                 echo json_encode(['success' => false, 'message' => 'Erreur de contrainte: L\'email ou le pseudonyme existe peut-être déjà.']);
            } else {
                 echo json_encode(['success' => false, 'message' => 'Erreur Database lors de la mise à jour.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
             error_log("Exception in modifyUserByApiKey: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Serveur: ' . $e->getMessage()]);
        }
    }
} // End of class UserController