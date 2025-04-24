<?php
include_once(__DIR__ . '/../../config.php');

class ChatController
{
    public static function getMessageOnly($msgID)
    {
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

    public static function getMessageSelonChatID($chatID)
    {
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$chatID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'chatID' manquant."]);
            return;
        }

        try {
            $sql = <<<SQL
SELECT
    m.ID       AS messageID,
    m.UserID   AS senderID,
    m.Content  AS content,
    m.Date     AS sentAt
FROM ChatMessage cm
JOIN Message     m  ON m.ID = cm.MessageID
WHERE cm.ChatID = :chatID
ORDER BY m.Date ASC
SQL;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['chatID' => $chatID]);
            $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($msgs);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB getMessageSelonChatID', 'details' => $e->getMessage()]);
        }
    }

    public static function envoyerMessage()
    {
        global $pdo;
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Utilisez POST.']);
            return;
        }

        $chatID  = $_POST['chatID']  ?? null;
        $apiKey  = $_POST['apiKey']  ?? null;
        $content = $_POST['message'] ?? null;
        if (!$chatID || !$apiKey || !$content) {
            echo json_encode(['success' => false, 'message' => 'chatID, apiKey et message obligatoires.']);
            return;
        }

        // validate apiKey and get userID
        $stmt = $pdo->prepare('SELECT ID FROM `User` WHERE ApiKey = :k');
        $stmt->execute(['k' => $apiKey]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) {
            echo json_encode(['success' => false, 'message' => 'apiKey invalide']);
            return;
        }
        $userID = $u['ID'];

        try {
            $pdo->beginTransaction();

            // 1) insert into Message
            $ins1 = $pdo->prepare(
                'INSERT INTO Message (UserID, Content, Date)
                 VALUES (:user, :cont, NOW())'
            );
            $ins1->execute([
                'user' => $userID,
                'cont' => $content
            ]);
            $messageID = $pdo->lastInsertId();

            // 2) link into ChatMessage
            $ins2 = $pdo->prepare(
                'INSERT INTO ChatMessage (ChatID, MessageID)
                 VALUES (:chat, :msg)'
            );
            $ins2->execute([
                'chat' => $chatID,
                'msg'  => $messageID
            ]);

            $pdo->commit();
            echo json_encode(['success' => true, 'messageID' => $messageID]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
        }
    }

    public static function creerChat()
    {
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
        $stmtKey = $pdo->prepare('SELECT ID FROM `User` WHERE ApiKey = :key');
        $stmtKey->execute(['key' => $apiKey]);
        $userRow = $stmtKey->fetch(PDO::FETCH_ASSOC);
        if (!$userRow) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey invalide.']);
            return;
        }
        $creatorID = $userRow['ID'];

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
        $stmtU = $pdo->prepare('SELECT ID FROM `User` WHERE Pseudo = :pseudo');
        $memberIDs = [];
        foreach ($pseudoList as $pseudo) {
            $stmtU->execute(['pseudo' => htmlspecialchars($pseudo)]);
            $row = $stmtU->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => "Pseudo introuvable : $pseudo"]);
                return;
            }
            $memberIDs[] = $row['ID'];
        }

        try {
            $stmtChat = $pdo->prepare('INSERT INTO `Chat` (`Name`) VALUES (:chat_name)');
            $stmtChat->execute(['chat_name' => $chat_name]);
            $chat_id = $pdo->lastInsertId();

            $stmtCC = $pdo->prepare(
                'INSERT INTO `ChatCreator` (UserID, TeamID, ChatID, isCreator)
                 VALUES (:user_id, NULL, :chat_id, :isCreator)'
            );
            $stmtCC->execute([
                'user_id'   => $creatorID,
                'chat_id'   => $chat_id,
                'isCreator' => 1
            ]);
            foreach ($memberIDs as $uid) {
                if ($uid === $creatorID) continue;
                $stmtCC->execute([
                    'user_id'   => $uid,
                    'chat_id'   => $chat_id,
                    'isCreator' => 0
                ]);
            }

            echo json_encode([
                'success' => true,
                'message' => "Chat créé (#{$chat_id}) avec " . count($memberIDs) . " membres.",
                'chatID'  => $chat_id
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur création chat : ' . $e->getMessage()]);
        }
    }

    public static function getChatsForUser()
    {
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $apiKey = $_POST['apiKey'] ?? null;
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey manquante.']);
            return;
        }
        $stmtKey = $pdo->prepare('SELECT ID FROM `User` WHERE ApiKey = :key');
        $stmtKey->execute(['key' => $apiKey]);
        $user = $stmtKey->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Clé apiKey invalide.']);
            return;
        }
        $userID = $user['ID'];

        try {
            $sql = "
                SELECT
                    ch.ID      AS chatID,
                    ch.Name    AS chatName,
                    COALESCE(
                        (
                            SELECT Il.Img
                              FROM ImgLib AS Il
                             WHERE Il.UserID = cc_creator.UserID
                             ORDER BY Il.`Index` DESC
                             LIMIT 1
                        ),
                        u2.Img
                    ) AS creatorImg
                FROM Chat AS ch
                JOIN ChatCreator AS cc_me
                  ON cc_me.ChatID = ch.ID
                 AND cc_me.UserID = :userID
                JOIN ChatCreator AS cc_creator
                  ON cc_creator.ChatID = ch.ID
                 AND cc_creator.isCreator = 1
                JOIN `User` AS u2
                  ON u2.ID = cc_creator.UserID
                ORDER BY ch.Creation_Date DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($chats, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
        }
    }

    public static function getMessageSelonUserID($userID)
    {
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT Message.*
                    FROM Message
                    WHERE Message.UserID = :userID
                    ORDER BY Message.Date DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($messages);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database Message:', 'details' => $e->getMessage()]);
        }
    }


    public static function createMessage()
    {
        global $pdo;
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer un message.']);
            return;
        }

        $input = file_get_contents('php://input');
        $data  = json_decode($input, true);
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
            echo json_encode(['success' => true, 'message' => 'Message créé', 'messageID' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
        }
    }
}
