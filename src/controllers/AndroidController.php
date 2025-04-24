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
    public static function getUserDataForAndroid($apiKey){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!$apiKey) {
            echo json_encode([
                'success' => false,
                'message' => "Paramètre 'apiKey' manquant."
            ]);
            return;
        }
    
        try {
            // On cherche l'utilisateur dont la clé API correspond
            $sql = '
                SELECT
                    User.Name,
                    User.Pseudo,
                    User.Description,
                    User.Img
                FROM `User`
                WHERE User.ApiKey = :apiKey
                LIMIT 1
            ';
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Si aucun résultat, renvoyer un JSON vide ou un flag success=false
            if (!$userData) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé pour cette apiKey.'
                ]);
                return;
            }
    
            // Renvoyer les données trouvées
            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
    
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur database : ' . $e->getMessage()
            ]);
        }
    }
    public static function getActivityImages(){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $folder = __DIR__ . '/../../ressources/images/activity/';
        $files = array_diff(scandir($folder), ['.','..']);
    
        // récupère le host et porte
        $host = $_SERVER['HTTP_HOST']; // 10.0.2.2:9999 en émulateur
        $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http';
        $baseUrl = "$proto://$host/ressources/images/activity/";
    
        $images = [];
        foreach($files as $f){
            $images[] = $baseUrl . $f;
        }
    
        echo json_encode([
          'success' => true,
          'images'  => $images
        ], JSON_UNESCAPED_SLASHES);
    }
    

    //function qui retourne le nombre total d'amis d'un utilisateur selon l'ID passee en parametre
    public static function getUserTotalFriendsForAndroid($apiKey){
        global $pdo;
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant."]);
            return;
        }
    
        try {
            // jointure entre User et UserFriend, filtre sur ApiKey
            $sql = '
                SELECT COUNT(UF.FriendID) AS total_friends
                  FROM UserFriend UF
                  JOIN User U ON UF.UserID = U.ID
                 WHERE U.ApiKey = :apiKey
            ';
    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['apiKey' => $apiKey]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
            echo json_encode($data, JSON_UNESCAPED_SLASHES);
    
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'Erreur Database (UserFriend / User)' => $e->getMessage()
            ]);
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
            $sql = 'SELECT U.UserName AS name, U.Pseudo AS pseudo, U.Description AS description, U.Img, UF.total_friends, UA.game_count_total
                FROM (SELECT User.ID AS UserID, User.Name AS UserName, User.Pseudo, User.Description, User.Img FROM User WHERE User.ID = :userID) AS U
                INNER JOIN (SELECT UserFriend.UserID, COUNT(UserFriend.FriendID) AS total_friends FROM UserFriend WHERE UserFriend.UserID =:userID2) AS UF ON UF.UserID = U.UserID
                INNER JOIN (SELECT UserActivity.UserID, SUM(UserActivity.Game_Count) AS game_count_total FROM UserActivity WHERE UserActivity.UserID = :userID3) AS UA ON UA.UserID = UF.UserID';
                
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['userID' => $userID, 'userID2' => $userID, 'userID3' => $userID]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            

            echo json_encode($userData, JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['Erreur Database(Contrainte ou Syntaxe-> Tables "User, UserActivity et UserFriend"):' => $e->getMessage()]);
        }
    }
    
}
