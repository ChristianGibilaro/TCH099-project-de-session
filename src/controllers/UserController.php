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
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $response = ['success' => false, 'message' => '', 'errors' => []];
        $tempImagePath = null;
        $baseDir = __DIR__ . '/../../';
    
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new Exception('Méthode HTTP non autorisée.');
            }
    
            // Les champs obligatoires (on retire language_name)
            $requiredFields = ['pseudonym', 'nom', 'email', 'password', 'password2', 'description', 'age', 'agreement'];
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
    
            if (!empty($response['errors'])) {
                $response['message'] = 'Erreur de validation. Veuillez corriger les champs indiqués.';
                http_response_code(400);
                echo json_encode($response);
                return;
            }
    
            // Récupération des valeurs
            $pseudonym   = htmlspecialchars($_POST['pseudonym']);
            $nom         = htmlspecialchars($_POST['nom']);
            $email       = htmlspecialchars($_POST['email']);
            $password    = $_POST['password'];
            $description = htmlspecialchars($_POST['description']);
            $age         = htmlspecialchars($_POST['age']);
    
            // === Gestion de la langue (optionnelle) ===
            if (!empty($_POST['language_name'])) {
                $languageName = htmlspecialchars($_POST['language_name']);
            } else {
                $languageName = 'Français';          // valeur par défaut
            }
            // Recherche de l'ID de langue
            $stmtLang = $pdo->prepare('SELECT ID FROM Language WHERE Name = :name');
            $stmtLang->execute([':name' => $languageName]);
            $langRow = $stmtLang->fetch(PDO::FETCH_ASSOC);
            $languageID = $langRow ? $langRow['ID'] : 1; // fallback à 1
    
            // === (Optionnel) Position ===
            $positionID = null;
            if (!empty($_POST['position_name'])) {
                $posName = htmlspecialchars($_POST['position_name']);
                $stmtPos = $pdo->prepare('SELECT ID FROM Position WHERE Name = :name');
                $stmtPos->execute([':name' => $posName]);
                $posRow = $stmtPos->fetch(PDO::FETCH_ASSOC);
                if ($posRow) {
                    $positionID = $posRow['ID'];
                }
            }
    
            // ... reste de la gestion du fichier image comme avant ...
    
            // Insertion de l'utilisateur
            $stmtUser = $pdo->prepare(
                'INSERT INTO User
                 (Img, Pseudo, Name, Email, Password, Last_Login,
                  LanguageID, Creation_Date, PositionID, Description, Birth)
                 VALUES
                 (:img, :pseudo, :nom, :email, :passwordd, :last_login,
                  :language_id, :creation_date, :position_id, :description, :age)'
            );
            $stmtUser->execute([
                ':img'            => $imagePath ?? 'pending',
                ':pseudo'         => $pseudonym,
                ':nom'            => $nom,
                ':email'          => $email,
                ':passwordd'      => password_hash($password, PASSWORD_DEFAULT),
                ':last_login'     => date('Y-m-d H:i:s'),
                ':language_id'    => $languageID,
                ':creation_date'  => date('Y-m-d H:i:s'),
                ':position_id'    => $positionID,
                ':description'    => $description,
                ':age'            => $age
            ]);
    
            // ... renommage du fichier et réponse finale ...
    
            $response['success'] = true;
            $response['message'] = 'Compte créé avec succès !';
            echo json_encode($response);
    
        } catch (PDOException $e) {
            http_response_code(500);
            $response['message'] = 'Erreur de base de données.';
            echo json_encode($response);
        } catch (Exception $e) {
            // Gère les autres exceptions éventuelles
            http_response_code(http_response_code() ?: 500);
            $response['message'] = $e->getMessage();
            echo json_encode($response);
        }
    }
    
   public static function getUserById($userID) {
    global $pdo;

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
        $allowedFields = ['ID', 'Img', 'Backg_Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
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
        error_log("PDOException in getUserById (UserID: $userID): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur Database.']);
    } catch (Exception $e) {
         http_response_code(500);
         error_log("Exception in getUserById (UserID: $userID): " . $e->getMessage());
         echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
    }

    }

    public static function getUserByUsername($username) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$username) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'username' manquant dans l'URL."]);
            return;
        }
    
        try {
            $sql = "SELECT ID FROM User WHERE User.Pseudo = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user && isset($user['ID'])) {
                self::getUserById($user['ID']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec le nom d'utilisateur fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("PDOException finding user ID in getUserByUsername (Username: $username): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database lors de la recherche de l\'utilisateur.']);
        } catch (Exception $e) {
             http_response_code(500);
             error_log("Exception in getUserByUsername (Username: $username): " . $e->getMessage());
             echo json_encode(['success' => false, 'message' => 'Erreur Serveur.']);
        }
    }

    public static function getUserByApiKey($apiKey) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$apiKey) {
             http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant dans l'URL."]);
            return;
        }
    
        try {
            $sql = "SELECT ID FROM User WHERE User.ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user && isset($user['ID'])) {
                self::getUserById($user['ID']);
            } else {
                 http_response_code(404);
                echo json_encode(['success' => false, 'message' => "Utilisateur introuvable avec l'apiKey fourni."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("PDOException finding user ID in getUserByApiKey: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur Database lors de la recherche de l\'utilisateur.']);
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
            $allowedFields = ['ID', 'Img', 'Backg_Img', 'Pseudo', 'Name', 'Email', /* add other valid columns */ 'Last_Login', 'LanguageID', 'Creation_Date', 'PositionID', 'Description', 'Birth', 'ApiKey'];
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
        $allowedUpdateFields = ['Pseudo', 'Name', 'Email', 'Description', 'Birth', 'LanguageID', 'PositionID', 'Img', 'Backg_Img']; // Added Backg_Img
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
                     // Validate Img and Backg_Img as URLs (allow empty/null to remove)
                     if (($key === 'Img' || $key === 'Backg_Img') && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                         $updateErrors[$key] = "Format d'URL invalide pour $key.";
                         continue;
                     }

                    // Sanitize key for SQL statement (basic backtick escaping)
                    $safeKey = "`" . str_replace("`", "``", $key) . "`";
                    $fields[] = "$safeKey = :$key";
                    // Sanitize value before adding to params (htmlspecialchars or type casting)
                    // Allow null values
                    $params[$key] = ($value === null) ? null : (is_string($value) ? htmlspecialchars($value) : $value);
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