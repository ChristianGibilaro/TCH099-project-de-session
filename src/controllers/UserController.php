<?php
include_once __DIR__ . '/../../config.php';

class UserController
{
    public static function createUser()
    {
        global $pdo;
        header('Content-Type: application/json');

        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        $pseudo   = $data['Pseudo']  ?? null;
        $name     = $data['Name']    ?? null;
        $email    = $data['Email']   ?? null;
        $password = $data['Password']?? null;

        $img         = $data['Img']           ?? 'https://exemple.com/default.png';
        $lastLogin   = $data['Last_Login']    ?? date('Y-m-d');
        $languageID  = $data['LanguageID']    ?? 1;
        $positionID  = $data['PositionID']    ?? null;
        $description = $data['Description']   ?? null;
        $birth       = $data['Birth']         ?? null;

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
            $stmt->bindValue(':pwd',       $password);
            $stmt->bindValue(':lastLogin', $lastLogin);
            $stmt->bindValue(':langID',    $languageID,  \PDO::PARAM_INT);
            $stmt->bindValue(':posID',     $positionID,  \PDO::PARAM_INT);
            $stmt->bindValue(':descr',     $description);
            $stmt->bindValue(':birth',     $birth);

            $stmt->execute();
            $newUserId = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'userID'  => $newUserId
            ]);
        } catch (\PDOException $e) {
        
            echo json_encode([
                'success' => false,
                'message' => "Erreur DB : " . $e->getMessage()
            ]);
        }
    }
}
