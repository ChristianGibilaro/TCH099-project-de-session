<?php
include_once __DIR__ . '/../../config.php';

class UserController
{
    public static function createUser()
    {
        global $pdo;
        header('Content-Type: application/json');

        // Read raw JSON input
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        // Log the raw input for debugging
        error_log("createUser received JSON: " . $inputJSON);

        $pseudo   = $data['Pseudo']  ?? null;
        $name     = $data['Name']    ?? null;
        $email    = $data['Email']   ?? null;
        $password = $data['Password']?? null;

        // Debug: Log received data
        error_log("createUser: Pseudo={$pseudo}, Name={$name}, Email={$email}");

        // Optional parameters with defaults:
        $img         = $data['Img']           ?? 'https://exemple.com/default.png';
        $lastLogin   = $data['Last_Login']    ?? date('Y-m-d');
        $languageID  = $data['LanguageID']    ?? 1;
        $positionID  = $data['PositionID']    ?? null;
        $description = $data['Description']   ?? null;
        $birth       = $data['Birth']         ?? null;

        // Check for required fields
        if (!$pseudo || !$name || !$email || !$password) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            error_log("createUser error: Missing required fields");
            return;
        }

        try {
            $sql = "
                INSERT INTO `User`
                  (Img, Pseudo, Name, Email, Password, Last_Login, LanguageID, PositionID, Description, Birth)
                VALUES
                  (:img, :pseudo, :name, :email, :pwd, :lastLogin, :langID, :posID, :descr, :birth)
            ";
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':img',       $img);
            $stmt->bindValue(':pseudo',    $pseudo);
            $stmt->bindValue(':name',      $name);
            $stmt->bindValue(':email',     $email);
            // In production, make sure to hash the password!
            $stmt->bindValue(':pwd',       $password);
            $stmt->bindValue(':lastLogin', $lastLogin);
            $stmt->bindValue(':langID',    $languageID, \PDO::PARAM_INT);
            $stmt->bindValue(':posID',     $positionID, \PDO::PARAM_INT);
            $stmt->bindValue(':descr',     $description);
            $stmt->bindValue(':birth',     $birth);

            $stmt->execute();
            $newUserId = $pdo->lastInsertId();

            $response = [
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'userID'  => $newUserId
            ];
            echo json_encode($response);
            error_log("createUser success: " . json_encode($response));
        } catch (\PDOException $e) {
            $errorMsg = "Erreur DB : " . $e->getMessage();
            echo json_encode([
                'success' => false,
                'message' => $errorMsg
            ]);
            error_log("createUser PDOException: " . $errorMsg);
        }
    }
}
