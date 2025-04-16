<?php
class AdminController{

    public static function adminConnect() {
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
    
            // Check if the user is an admin
            $sql = "SELECT 1 FROM Admins WHERE UserID = :userID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $user['ID']]);
            $isAdmin = $stmt->fetchColumn();
    
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => "Accès refusé: l'utilisateur n'est pas un administrateur."]);
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

    public static function generatePasswordHash($input) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Check if the password is provided
        if (!isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'password' manquant."]);
            return;
        }
    
        $password = $input['password'];
    
        try {
            // Generate the hash of the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
            // Return the hashed password
            echo json_encode(['success' => true, 'hashedPassword' => $hashedPassword]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public static function modifyUserByAdmin($userID) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Read the JSON body from the request
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Validate required fields
        if (!isset($input['apiKey']) || !isset($input['password']) || !isset($input['data'])) {
            echo json_encode(['success' => false, 'message' => "Paramètres 'apiKey', 'password', et 'data' manquants."]);
            return;
        }
    
        $apiKey = $input['apiKey'];
        $password = $input['password'];
        $data = $input['data']; // This should be an associative array of fields to update
    
        try {
            // Verify the admin's API key and password
            $sql = "SELECT Admins.Password, Admins.UserID 
                    FROM Admins 
                    INNER JOIN User ON Admins.UserID = User.ID 
                    WHERE User.ApiKey = :apiKey";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$admin) {
                echo json_encode(['success' => false, 'message' => "Clé API invalide ou utilisateur non administrateur."]);
                return;
            }
    
            // Verify the password
            if (!password_verify($password, $admin['Password'])) {
                echo json_encode(['success' => false, 'message' => "Mot de passe administrateur incorrect."]);
                return;
            }
    
            // Build the SQL query dynamically based on the provided data
            $fields = [];
            $params = ['userID' => $userID];
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