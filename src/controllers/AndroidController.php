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
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, Chat et MEssage"):' => $e->getMessage()]);
        }
    }

    //retourne le nom, le pseudo, la description et l'image prole d'un utilisateur(Pour Android)
    public static function getUserDataForAndroid($userID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT User.Name, User.Pseudo, User.Description, User.Img FROM User WHERE User.ID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, Chat et MEssage"):' => $e->getMessage()]);
        }
    }

    public static function getUserTotalFriendsForAndroid($userID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT COUNT(UserFriend.FriendID) AS total_friends FROM UserFriend WHERE UserFriend.UserID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, Chat et MEssage"):' => $e->getMessage()]);
        }
    }

    
    
}
