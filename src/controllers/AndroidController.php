<?php

include_once(__DIR__ . '/../../config.php');
class AndroidController
{
    //Collecter les donnees de plusieurs tabes d'un coup des tables suivantes: User, Message, Chat et ChatMessage
    public static function getAllDatAndroid($userID){
        global $pdo;
        //$userID = 8;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            //Certainnes colonnes sont renommees pour plus de clarite vu qu'elles portent exactement le meme nom(ID dand table 'User' et dans table 'Message')
           /*$stmt = $pdo->query('SELECT User.ID, User.Pseudo, User.Img,
                                        Message.ID AS MessageID, Message.Content, Message.Date,
                                        Chat.ID AS ChatID, Chat.Name
                FROM User
                INNER JOIN Message ON Message.UserID=User.ID
                INNER JOIN ChatMessage ON Message.ID = ChatMessage.MessageID
                INNER JOIN Chat ON ChatMessage.ChatID = Chat.ID
                WHERE User.ID = ');
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);*/
            $sql = 'SELECT U.UserID AS UserID, U.Pseudo, U.Img, 
               Message.ID AS MessageID, Message.Content, Message.Date, 
               Chat.ID AS ChatID, Chat.Name
                FROM (SELECT User.ID AS UserID, User.Pseudo, User.Img FROM User WHERE User.ID LIKE :userID)AS U
                INNER JOIN Message ON Message.UserID = U.UserID
                INNER JOIN ChatMessage ON Message.ID = ChatMessage.MessageID
                INNER JOIN Chat ON ChatMessage.ChatID = Chat.ID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, Chat et MEssage"):' => $e->getMessage()]);
        }
    }

    //Requetes individuelles independantes les unes des autres memes si il sont logiquement reliees
    //Les requetes ci-dessous permettent d'interoger les tables une a la fois mais selon une ID ou les contraintes qui les relient

    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID d'utilisateur saisi
    public static function getUserOnly($userID){
        global $pdo;
        //$userID = 8;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT User.ID AS UserID, User.Pseudo, User.Img
                    FROM User 
                    WHERE User.ID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "User"):' => $e->getMessage()]);
        }

    }
    
    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID du message saisi
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
                    WHERE Message.ID LIKE :msgID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['msgID' => $msgID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "Message"):' => $e->getMessage()]);
        }

    }

    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID de l'utilisateur(cle etrangere) saisi
    public static function getMessageSelonUserID($userID){
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
                    WHERE Message.UserID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "Message"):' => $e->getMessage()]);
        }

    }

    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID du chat(cle etrangere) saisi
    public static function getMessageSelonChatID($chatID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$chatID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'chatID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT Message.*
                    FROM Message , ChatMessage
                    WHERE Message.ID LIKE ChatMessage.MessageID AND ChatMessage.ChatID LIKE :chatID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['chatID' => $chatID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "Message"):' => $e->getMessage()]);
        }

    }

    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID du chat saisi
    public static function getChatOnly($chatID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$chatID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'chatID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT Chat.ID, Chat.Name
                    FROM Chat 
                    WHERE Chat.ID LIKE :chatID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['chatID' => $chatID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "Chat"):' => $e->getMessage()]);
        }

    }

    //Requete pour obtenir les donnees requises dans l'interogation de base de donnees selon l'ID du message(cle etrangere) saisi
    public static function getChatSelonMessageID($msgID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$msgID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'msgID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT Chat.ID, Chat.Name
                    FROM Chat , ChatMessage
                    WHERE Chat.ID LIKE ChatMessage.ChatID AND ChatMessage.MessageID LIKE :msgID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['msgID' => $msgID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "Chat"):' => $e->getMessage()]);
        }

    }
}
