<?php

include_once(__DIR__ . '/../../config.php');

class TeamController
{
   
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
