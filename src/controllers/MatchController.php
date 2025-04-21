<?php
include_once(__DIR__ . '/../../config.php');

class MatchController
{
    private static function sendJSON($success, $message, $data = [], $code = 200): void
    {
        http_response_code($code);
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    private static function checkAdmin($matchID): void
    {
        global $pdo;
        $headers = getallheaders();
        $apiKey = $headers['X-API-Key'] ?? null;
        if (!$apiKey) self::sendJSON(false, "Clé API manquante.", [], 401);

        $stmt = $pdo->prepare("SELECT ID FROM User WHERE ApiKey = :api");
        $stmt->execute(['api' => $apiKey]);
        $userID = $stmt->fetchColumn();
        if (!$userID) self::sendJSON(false, "Clé API invalide.", [], 401);

        $stmt = $pdo->prepare("SELECT 1 FROM AdminMatch WHERE UserID = :u AND MatchID = :m");
        $stmt->execute(['u' => $userID, 'm' => $matchID]);
        if (!$stmt->fetchColumn()) self::sendJSON(false, "Droits administrateur requis pour le match $matchID.", [], 403);
    }

    public static function createMatch(): void
{
    global $pdo;
    header('Content-Type: application/json');

    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) self::sendJSON(false, "Corps JSON invalide.");

    $teamID     = $data['teamID']     ?? null;
    $activityID = $data['activityID'] ?? null;
    $levelID    = $data['levelID']    ?? null;
    $userID     = $data['userID']     ?? null;

    $isPublic   = isset($data['isPublic']) && $data['isPublic'] ? '1' : '0';

    if (!$teamID || !$activityID || !$levelID || !$userID) {
        self::sendJSON(false, "Champs 'teamID', 'activityID', 'levelID', 'userID' requis.");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO `Match` (Is_Public, UserID, TeamID, ActivityID, LevelID)
            VALUES (:pub, :uid, :tid, :aid, :lid)
        ");
        $stmt->execute([
            ':pub' => $isPublic,
            ':uid' => $userID,
            ':tid' => $teamID,
            ':aid' => $activityID,
            ':lid' => $levelID
        ]);

        self::sendJSON(true, "Match créé avec succès.", ['matchID' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        self::sendJSON(false, "Erreur SQL : " . $e->getMessage());
    }
}



    public static function inviteUser(int $userID): void
    {
        global $pdo;
        $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

        $matchID = $data['matchID'] ?? null;
        $message = $data['message'] ?? 'Invitation à rejoindre le match.';

        if (!$matchID) self::sendJSON(false, "matchID manquant.");
        self::checkAdmin($matchID);

        $sql = "INSERT INTO InvitationMatch (Name, MatchID, UserID)
                VALUES (:n,:m,:u)";
        $pdo->prepare($sql)->execute([':n' => $message, ':m' => $matchID, ':u' => $userID]);

        self::sendJSON(true, "Invitation envoyée au joueur $userID.");
    }

    public static function joinMatch(int $userID): void
{
    global $pdo;

    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) self::sendJSON(false, "Corps JSON invalide.");

    $matchID = $data['matchID'] ?? null;
    if (!$matchID) self::sendJSON(false, "matchID manquant.");

    self::checkAdmin($matchID);

    $stmt = $pdo->prepare("SELECT 1 FROM UserMatch WHERE UserID = :u AND MatchID = :m");
    $stmt->execute([':u' => $userID, ':m' => $matchID]);

    if ($stmt->fetchColumn()) {
        self::sendJSON(false, "Déjà dans le match.", [], 409);
    }

    $pdo->prepare("INSERT INTO UserMatch (UserID, MatchID) VALUES (:u, :m)")
        ->execute([':u' => $userID, ':m' => $matchID]);

    self::sendJSON(true, "Joueur $userID ajouté au match $matchID.");
}


public static function quitMatch(int $userID): void
{
    global $pdo;
    header('Content-Type: application/json');

    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) self::sendJSON(false, "Corps invalide (JSON ou formulaire attendu).");

    $matchID = $data['matchID'] ?? null;
    if (!$matchID) self::sendJSON(false, "matchID manquant.");

    $stmt = $pdo->prepare("DELETE FROM UserMatch WHERE UserID = :u AND MatchID = :m");
    $stmt->execute([':u' => $userID, ':m' => $matchID]);

    self::sendJSON(true, "Joueur $userID a quitté le match $matchID.");
}


public static function banUser(int $userID): void
{
    global $pdo;
    header('Content-Type: application/json');

    $data = $_POST;
    if (empty($data)) {
        $raw = file_get_contents("php://input");
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }

    $matchID = $data['matchID'] ?? null;
    if (!$matchID) self::sendJSON(false, "matchID manquant.");
    self::checkAdmin($matchID);

    $stmt = $pdo->prepare("DELETE FROM UserMatch WHERE UserID = :u AND MatchID = :m");
    $stmt->execute([':u' => $userID, ':m' => $matchID]);

    self::sendJSON(true, "Joueur $userID retiré du match $matchID.");
}


    public static function connectMatch(): void
    {
        global $pdo;
        $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

        $matchID  = $data['matchID']  ?? null;
        $password = $data['password'] ?? null;

        if (!$matchID || !$password)
            self::sendJSON(false, "Champs 'matchID' et 'password' requis.");

        $sql = "SELECT UserID FROM AdminMatch WHERE MatchID = :m AND Password = :p";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':m' => $matchID, ':p' => $password]);
        $adminID = $stmt->fetchColumn();

        if (!$adminID) self::sendJSON(false, "Mot de passe incorrect.", [], 401);

        $stmt = $pdo->prepare("SELECT ApiKey FROM User WHERE ID = :id");
        $stmt->execute([':id' => $adminID]);
        $apiKey = $stmt->fetchColumn();

        self::sendJSON(true, "Connexion admin réussie.", ['apiKey' => $apiKey]);
    }

    public static function getMatch($id): void
    {
        global $pdo;
        $fields = json_decode(file_get_contents("php://input"), true)['fields'] ?? '*';
        $select = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));

        $stmt = $pdo->prepare("SELECT $select FROM `Match` WHERE ID = :id");
        $stmt->execute(['id' => $id]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) self::sendJSON(false, "Match introuvable.");
        self::sendJSON(true, "Match trouvé.", $match);
    }

    public static function searchMatches(): void
{
    global $pdo;
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true);

    $where = [];
    $params = [];

    // Champs acceptés
    $teamID     = $input['teamID']     ?? null;
    $activityID = $input['activityID'] ?? null;
    $levelID    = $input['levelID']    ?? null;
    $isPublic   = $input['isPublic']   ?? null;
    $userID     = $input['userID']     ?? null;

    if ($teamID !== null) {
        $where[] = 'TeamID = :teamID';
        $params[':teamID'] = $teamID;
    }
    if ($activityID !== null) {
        $where[] = 'ActivityID = :activityID';
        $params[':activityID'] = $activityID;
    }
    if ($levelID !== null) {
        $where[] = 'LevelID = :levelID';
        $params[':levelID'] = $levelID;
    }
    if ($isPublic !== null) {
        $where[] = 'Is_Public = :isPublic';
        $params[':isPublic'] = $isPublic;
    }
    if ($userID !== null) {
        $where[] = 'UserID = :userID';
        $params[':userID'] = $userID;
    }

    $whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "SELECT * FROM `Match` $whereSQL ORDER BY ID DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$matches) {
            self::sendJSON(false, "Aucun match trouvé.", []);
        } else {
            self::sendJSON(true, "Résultats trouvés.", $matches);
        }
    } catch (PDOException $e) {
        self::sendJSON(false, "Erreur SQL : " . $e->getMessage(), []);
    }
}


public static function updateMatch($id): void
{
    global $pdo;
    self::checkAdmin($id);

    $input = $_POST ?: json_decode(file_get_contents("php://input"), true);
    if (!is_array($input)) {
        self::sendJSON(false, "Corps JSON ou formulaire invalide.");
    }

    $fields = array_intersect_key($input, array_flip(['Is_Public', 'LevelID', 'UserID', 'ActivityID', 'TeamID']));
    if (empty($fields)) {
        self::sendJSON(false, "Aucun champ à mettre à jour.");
    }

    $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
    $fields['id'] = $id;

    try {
        $sql = "UPDATE `Match` SET $set WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($fields);

        self::sendJSON(true, "Match $id mis à jour.");
    } catch (PDOException $e) {
        self::sendJSON(false, "Erreur SQL : " . $e->getMessage());
    }
}


public static function deleteMatch($id): void
{
    global $pdo;

    self::checkAdmin($id);

    try {
        // Supprime d'abord les dépendances liées
        $pdo->prepare("DELETE FROM AdminMatch WHERE MatchID = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM UserMatch WHERE MatchID = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM MatchData WHERE MatchID = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM InvitationMatch WHERE MatchID = :id")->execute([':id' => $id]);

        // Enfin, supprimer le match
        $pdo->prepare("DELETE FROM `Match` WHERE ID = :id")->execute([':id' => $id]);

        self::sendJSON(true, "Match $id supprimé avec succès.");
    } catch (PDOException $e) {
        self::sendJSON(false, "Erreur SQL : " . $e->getMessage());
    }
}

}
