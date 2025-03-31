<?php

include_once(__DIR__ . '/../../config.php');

class TeamController
{
    public static function creerTeam()
{
    global $pdo;
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'JSON invalide']);
            return;
        }

        $activityID   = $data['activityID']   ?? null;
        $teamName     = $data['name']         ?? null;
        $description  = $data['description']  ?? null;
        $mainColor    = $data['main_color']   ?? null;
        $secondColor  = $data['second_color'] ?? null;
        // Ajouter l implementation d image plus tard
        $mainImg      = null;

        if (!$activityID || !$teamName || !$description) {
            echo json_encode(['success' => false, 'message' => "Champs 'activityID', 'name', 'description' requis"]);
            return;
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO Team
                 (ActivityID, Name, Description, Main_Img, Main_Color, Second_Color)
                 VALUES
                 (:actID, :nm, :descr, :mImg, :mColor, :sColor)'
            );
            $stmt->execute([
                ':actID'  => $activityID,
                ':nm'     => $teamName,
                ':descr'  => $description,
                ':mImg'   => $mainImg,   //va etre null pour l instant
                ':mColor' => $mainColor,
                ':sColor' => $secondColor
            ]);
            echo json_encode(['success' => true, 'message' => 'Équipe créée']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
}

public static function getTeam($id)
    {
        global $pdo;  
        header('Content-Type: application/json');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'id' manquant."]);
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM Team WHERE ID = :teamID");
            $stmt->execute([':teamID' => $id]);
            $team = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($team) {
                echo json_encode([
                    'success' => true,
                    'team' => $team
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Aucune équipe trouvée pour l'ID = $id"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => "Erreur DB : " . $e->getMessage()
            ]);
        }
    }
}
