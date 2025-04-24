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
    
    //function qui retourne le nombre total d'amis d'un utilisateur selon l'ID passee en parametre
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
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "UserFriend"):' => $e->getMessage()]);
        }
    }

    //function qui retourne le nombre total de jeux ou de parties de jeux cumulees d'un utilisateur selon l'ID passee en parametre
    public static function getUserTotalGameCountForAndroid($userID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT SUM(UserActivity.Game_Count) AS game_count_total FROM UserActivity WHERE UserActivity.UserID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "UserActivity"):' => $e->getMessage()]);
        }
    }

    public static function getUserImageLibForAndroid($userID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            $sql = 'SELECT ImgLib.Url FROM ImgLib WHERE ImgLib.UserID LIKE :userID';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID]);
            $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Table "ImgLib"):' => $e->getMessage()]);
        }
    }

    //Function qui conbine les requete des fonctions ci-dessus
    public static function getUserDataCombinedAndroid($userID){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        if (!$userID) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'userID' manquant."]);
            return;
        }
        try {
            /*$sql = 'SELECT U.UserName AS name, U.Pseudo AS pseudo, U.Description AS description, U.Img, UF.total_friends, UA.game_count_total, IL.url, IL.image
                FROM (SELECT User.ID AS UserID, User.Name AS UserName, User.Pseudo, User.Description, User.Img FROM User WHERE User.ID = :userID) AS U
                INNER JOIN (SELECT UserFriend.UserID, COUNT(UserFriend.FriendID) AS total_friends FROM UserFriend WHERE UserFriend.UserID =:userID2) AS UF ON UF.UserID = U.UserID
                INNER JOIN (SELECT UserActivity.UserID, SUM(UserActivity.Game_Count) AS game_count_total FROM UserActivity WHERE UserActivity.UserID = :userID3) AS UA ON UA.UserID = UF.UserID
                INNER JOIN (SELECT ImgLib.UserID, ImgLib.Url AS url, ImgLib.Img AS image FROM ImgLib WHERE ImgLib.UserID = :userID4) AS IL ON IL.UserID = UA.UserID
                GROUP BY U.UserID';*/

            /*$sql = 'SELECT U.UserName AS name, U.Pseudo AS pseudo, U.Description AS description, U.Img, UF.total_friends, UA.game_count_total, IL.urls, IL.images
                FROM (SELECT User.ID AS UserID, User.Name AS UserName, User.Pseudo, User.Description, User.Img FROM User WHERE User.ID = :userID) AS U
                INNER JOIN (SELECT UserFriend.UserID, COUNT(UserFriend.FriendID) AS total_friends FROM UserFriend WHERE UserFriend.UserID =:userID2) AS UF ON UF.UserID = U.UserID
                INNER JOIN (SELECT UserActivity.UserID, SUM(UserActivity.Game_Count) AS game_count_total FROM UserActivity WHERE UserActivity.UserID = :userID3) AS UA ON UA.UserID = UF.UserID
                INNER JOIN (SELECT Im.UserID, Im.Url AS urls, Im.Img AS images FROM (SELECT ImgLib.UserID, ImgLib.Url, ImgLib.Img FROM ImgLib WHERE ImgLib.UserID = :userID4 GROUP BY ImgLib.UserID) AS Im) AS IL ON IL.UserID = UA.UserID
                GROUP BY U.UserID';
                */

                $sql = 'SELECT U.UserName AS name, U.Pseudo AS pseudo, U.Description AS description, U.Img, 
                UF.total_friends, UA.game_count_total, 
                GROUP_CONCAT(IL.url) AS urls, IL.image
                FROM 
                    (SELECT User.ID AS UserID, User.Name AS UserName, User.Pseudo, User.Description, User.Img 
                    FROM User WHERE User.ID = :userID) AS U
                INNER JOIN 
                    (SELECT UserFriend.UserID, COUNT(UserFriend.FriendID) AS total_friends 
                    FROM UserFriend WHERE UserFriend.UserID = :userID2) AS UF ON UF.UserID = U.UserID
                INNER JOIN 
                    (SELECT UserActivity.UserID, SUM(UserActivity.Game_Count) AS game_count_total 
                    FROM UserActivity WHERE UserActivity.UserID = :userID3) AS UA ON UA.UserID = UF.UserID
                INNER JOIN 
                    (SELECT ImgLib.UserID, ImgLib.Url AS url, ImgLib.Img AS image 
                    FROM ImgLib WHERE ImgLib.UserID = :userID4) AS IL ON IL.UserID = UA.UserID
                GROUP BY U.UserID';

                
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID, 'userID2' => $userID, 'userID3' => $userID, 'userID4' => $userID]);
            $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($userData as &$data) {
                if (!empty($data['urls'])) {
                    $data['urls'] = explode(',', $data['urls']);
                }
                if (!empty($data['images'])) {
                    $data['images'] = explode(',', $data['images']);
                }
            }
    
    

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, UserActivity et UserFriend"):' => $e->getMessage()]);
        }
    }
    
}
