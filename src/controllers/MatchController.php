<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class MatchController {
    public static function creerMatch()
    {
        global $pdo;
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => "Utilisez POST pour créer un match."]);
            return;
        }

        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => "Corps de requête invalide (JSON attendu)."]);
            return;
        }

        $isPublic = $data['isPublic'] ?? null;  
        $userID   = $data['userID']   ?? null;  

        if ($isPublic === null || $userID === null) {
            echo json_encode([
                'success' => false,
                'message' => "Champs 'isPublic' et 'userID' sont obligatoires."
            ]);
            return;
        }

        $bitValue = $isPublic ? "b'1'" : "b'0'";

        $endDate      = $data['end_date']       ?? null;  
        $openingEnd   = $data['opening_end']    ?? null;  
        $description  = $data['description']    ?? null;  
        $teamID       = $data['teamID']         ?? null;
        $activityID   = $data['activityID']     ?? null;
        $levelID      = $data['levelID']        ?? null;

        try {

            $sql = "
                INSERT INTO `Match`
                  (Is_Public, UserID, End_Date, Opening_End, Description, TeamID, ActivityID, LevelID)
                VALUES
                  ($bitValue, :uID, :endD, :openE, :descr, :tID, :aID, :lID)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':uID',   $userID,       PDO::PARAM_INT);
            $stmt->bindValue(':endD',  $endDate);
            $stmt->bindValue(':openE', $openingEnd);
            $stmt->bindValue(':descr', $description);
            $stmt->bindValue(':tID',   $teamID,       PDO::PARAM_INT);
            $stmt->bindValue(':aID',   $activityID,   PDO::PARAM_INT);
            $stmt->bindValue(':lID',   $levelID,      PDO::PARAM_INT);

            $stmt->execute();
            $newId = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => "Match créé avec succès.",
                'matchID' => $newId
            ]);

        } catch (PDOException $e) {

            echo json_encode([
                'success' => false,
                'message' => "Erreur DB : " . $e->getMessage()
            ]);
        }
    }
}

?>