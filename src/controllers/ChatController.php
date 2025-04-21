<?php
include_once(__DIR__ . '/../../config.php');

class ChatController
{
    public static function getMessageOnly($msgID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$msgID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'msgID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT Message.*
                    FROM Message 
                    WHERE Message.ID = :msgID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['msgID' => $msgID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database Message:', 'details' => $e->getMessage()]);
        }
    }

    // ... autres méthodes getMessageSelonUserID, getMessageSelonChatID, getChatOnly, getChatSelonMessageID ...

    public static function creerChat(){
        global $pdo;
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer un chat.']);
            return;
        }

        $apiKey = $_POST['apiKey'] ?? null;
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey manquante.']);
            return;
        }
        $stmtKey = $pdo->prepare('SELECT ID FROM User WHERE ApiKey = :key');
        $stmtKey->execute(['key' => $apiKey]);
        $userRow = $stmtKey->fetch(PDO::FETCH_ASSOC);
        if (!$userRow) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey invalide.']);
            return;
        }
        $userID = $userRow['ID'];

        if (empty($_POST['chat_name'])) {
            echo json_encode(['success' => false, 'message' => 'Le nom du chat est obligatoire.']);
            return;
        }
        $chat_name = htmlspecialchars($_POST['chat_name']);

        $pseudoList = json_decode($_POST['pseudos'] ?? '[]', true);
        if (!is_array($pseudoList) || empty($pseudoList)) {
            echo json_encode(['success' => false, 'message' => 'Liste des pseudos manquante ou invalide.']);
            return;
        }

        // Traduire chaque pseudo en userID
        $stmtU = $pdo->prepare('SELECT ID FROM User WHERE Pseudo = :pseudo');
        $userIDs = [];
        foreach ($pseudoList as $pseudo) {
            $stmtU->execute(['pseudo' => htmlspecialchars($pseudo)]);
            $row = $stmtU->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => "Pseudo introuvable : $pseudo"]);
                return;
            }
            $userIDs[] = $row['ID'];
        }

        try {
            $stmtChat = $pdo->prepare('INSERT INTO Chat(name) VALUES (:chat_name)');
            $stmtChat->execute(['chat_name' => $chat_name]);
            $chat_id = $pdo->lastInsertId();

            $stmtCC = $pdo->prepare(
                'INSERT INTO ChatCreator(UserID, TeamID, ChatID) VALUES (:user_id, NULL, :chat_id)'
            );
            foreach ($userIDs as $uid) {
                $stmtCC->execute(['user_id' => $uid, 'chat_id' => $chat_id]);
            }
            if (!in_array($userID, $userIDs)) {
                $stmtCC->execute(['user_id' => $userID, 'chat_id' => $chat_id]);
            }

            echo json_encode([
                'success' => true,
                'message' => "Chat créé (#{$chat_id}) avec " . count($userIDs) . " membres.",
                'chatID'  => $chat_id
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur création chat : ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère tous les chats d'un utilisateur (apiKey), renvoyant chatID, chatName et avatar du créateur
     */
    public static function getChatsForUser() {
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $apiKey = $_POST['apiKey'] ?? null;
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey manquante.']);
            return;
        }
        $stmtKey = $pdo->prepare('SELECT ID FROM User WHERE ApiKey = :key');
        $stmtKey->execute(['key' => $apiKey]);
        $u = $stmtKey->fetch(PDO::FETCH_ASSOC);
        if (!$u) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey invalide.']);
            return;
        }
        $userID = $u['ID'];

        try {
            $sql = "
                SELECT
                  c.ID   AS chatID,
                  c.Name AS chatName,
                  u2.Img AS creatorImg
                FROM Chat c
                JOIN ChatCreator cc_me
                  ON cc_me.ChatID = c.ID AND cc_me.UserID = :userID
                JOIN ChatCreator cc_creator
                  ON cc_creator.ChatID = c.ID
                 AND cc_creator.UserID = (
                    SELECT MIN(UserID)
                    FROM ChatCreator
                    WHERE ChatID = c.ID
                  )
                JOIN User u2
                  ON u2.ID = cc_creator.UserID
                ORDER BY c.Creation_Date DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($chats);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
        }
    }

    public static function createMessage() {
        global $pdo;
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer un message.']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Corps invalide (JSON attendu).']);
            return;
        }

        $userID  = $data['userID']  ?? null;
        $content = $data['content'] ?? null;
        if (!$userID || !$content) {
            echo json_encode(['success' => false, 'message' => "Champs 'userID' et 'content' obligatoires."]);
            return;
        }

        $file = $data['file'] ?? null;
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO Message(UserID, Content, File) VALUES (:uID, :cont, :file)'
            );
            $stmt->bindValue(':uID',  $userID,  PDO::PARAM_INT);
            $stmt->bindValue(':cont', $content, PDO::PARAM_STR);
            $stmt->bindValue(':file', $file);
            $stmt->execute();
            echo json_encode(['success'=>true,'message'=>'Message créé','messageID'=>$pdo->lastInsertId()]);
        } catch (PDOException $e) {
            echo json_encode(['success'=>false,'message'=>'Erreur DB : '.$e->getMessage()]);
        }
    }
}

/*
 Exemple Postman pour créer un chat :
 POST http://localhost:9999/api/creerChat
 Body (form-data):
   apiKey    : 008054c333eab54585dc53668a81fffa
   chat_name : Ma discussion secrète
   pseudos   : ["Alice","Bob","Charlie"]
*/
