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
}

?>
