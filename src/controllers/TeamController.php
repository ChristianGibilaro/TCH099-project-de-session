<?php

include_once(__DIR__ . '/../../config.php');

class TeamController
{
    private static function sendJSON($success, $message, $extra = [], $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    private static function getInput()
    {
        if (!empty($_POST)) return $_POST;
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    private static function getApiKeyFromUserID(int $userID): ?string
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT ApiKey FROM User WHERE ID = :id");
        $stmt->execute(['id' => $userID]);
        return $stmt->fetchColumn() ?: null;
    }

    public static function createTeam()
    {
        global $pdo;
        $data = self::getInput();

        $activityID  = $data['activityID']   ?? null;
        $name        = $data['name']         ?? null;
        $description = $data['description']  ?? null;
        $mainColor   = $data['main_color']   ?? null;
        $secondColor = $data['second_color'] ?? null;
        $rating      = $data['rating']       ?? null;

<<<<<<< HEAD
        if (!$activityID || !$name || !$description || $rating === null)
            self::sendJSON(false, "Champs 'activityID', 'name', 'description', 'rating' obligatoires.");

        $stmt = $pdo->prepare("
            INSERT INTO Team (ActivityID, Name, Description, Main_Color, Second_Color, Rating)
            VALUES (:a, :n, :d, :m, :s, :r)
        ");
=======
    public static function createTeam()
    {
        global $pdo;
        $data = self::getInput();

        $activityID  = $data['activityID']  ?? null;
        $name        = $data['name']        ?? null;
        $description = $data['description'] ?? null;
        $mainColor   = $data['main_color']  ?? null;
        $secondColor = $data['second_color']?? null;

        if (!$activityID || !$name || !$description || !$mainColor || !$secondColor)
            self::sendJSON(false, "Champs 'activityID', 'name', 'description', 'main_color', 'second_color' obligatoires.");

        $stmt = $pdo->prepare("INSERT INTO Team (ActivityID, Name, Description, Main_Color, Second_Color) VALUES (:a,:n,:d,:m,:s)");
>>>>>>> parent of da59436 (Merge branch 'backend' of https://github.com/ChristianGibilaro/TCH099-project-de-session into backend)
        $stmt->execute([
            ':a' => $activityID,
            ':n' => $name,
            ':d' => $description,
            ':m' => $mainColor,
<<<<<<< HEAD
            ':s' => $secondColor,
            ':r' => $rating
=======
            ':s' => $secondColor
>>>>>>> parent of da59436 (Merge branch 'backend' of https://github.com/ChristianGibilaro/TCH099-project-de-session into backend)
        ]);

        self::sendJSON(true, "Équipe créée avec succès.", ["team_id" => $pdo->lastInsertId()]);
    }

    public static function inviteUser(int $userID)
    {
        global $pdo;
        $userID = intval($userID);
        $data = self::getInput();

        $teamID = $data['teamID'] ?? null;
        $rankID = $data['rankID'] ?? 3;
        $msg    = $data['message'] ?? "Invitation à rejoindre l'équipe.";
        $exp    = $data['expires'] ?? null;

        if (!$teamID) self::sendJSON(false, "teamID manquant.");

        $sql = "INSERT INTO Invitation (Name, TeamID, UserID, RankID, Expiration_date)
                VALUES (:n,:t,:u,:r,:e)";
        $pdo->prepare($sql)->execute([
            ':n'=>$msg, ':t'=>$teamID, ':u'=>$userID, ':r'=>$rankID, ':e'=>$exp
        ]);

        self::sendJSON(true, "Invitation envoyée à l'utilisateur $userID.");
    }

    public static function joinTeam(int $userID)
    {
        global $pdo;
        $data = self::getInput();

        $teamID = $data['teamID'] ?? null;
        if (!$teamID) self::sendJSON(false, "teamID manquant.");

        $dup = $pdo->prepare("SELECT 1 FROM UserTeam WHERE UserID=:u AND TeamID=:t");
        $dup->execute([':u'=>$userID, ':t'=>$teamID]);
        if ($dup->fetchColumn()) self::sendJSON(false, "Déjà membre.", [], 409);

        $pdo->prepare("INSERT INTO UserTeam (UserID,TeamID,RankID,Game_Count)
             VALUES (:u,:t,3,0)")->execute([':u'=>$userID, ':t'=>$teamID]);

        self::sendJSON(true, "Utilisateur $userID ajouté.");
    }

    public static function getTeam($id)
    {
        global $pdo;
        header('Content-Type: application/json');
        $data = self::getInput();
        $fields = $data['fields'] ?? '*';

        $select = ($fields === '*') ? '*' : implode(', ', array_map('htmlspecialchars', $fields));
        $stmt = $pdo->prepare("SELECT $select FROM Team WHERE ID = :id");
        $stmt->execute(['id' => $id]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$team) self::sendJSON(false, "Équipe introuvable.");
        self::sendJSON(true, "Équipe récupérée.", ["team" => $team]);
    }

    public static function getTeamByTitle(string $title): void
    {
        global $pdo;

        header('Content-Type: application/json');
        $title = trim($title);

        try {
            $stmt = $pdo->prepare("SELECT * FROM Team WHERE Name = :title LIMIT 1");
            $stmt->execute(['title' => $title]);
            $team = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$team) {
                echo json_encode(['success' => false, 'message' => 'Équipe introuvable.']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $team]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur DB : ' . $e->getMessage()
            ]);
        }
    }

    public static function searchTeams(): void
{
    global $pdo;
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    $title = $input['title'] ?? '';
    $limit = isset($input['limit']) ? (int)$input['limit'] : null;
    $fields = $input['fields'] ?? '*';
    $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));

    $sql = "SELECT $selectFields FROM Team WHERE Name LIKE :search ORDER BY Name ASC";
    if ($limit !== null && $limit > 0) {
        $sql .= " LIMIT :limit";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $title . '%', PDO::PARAM_STR);
    if ($limit !== null && $limit > 0) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();

    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$teams) {
        echo json_encode(['success' => false, 'message' => "Équipe introuvable."]);
        return;
    }

    echo json_encode(['success' => true, 'data' => $teams]);
}


    public static function banUser(int $userID): void
    {
        global $pdo;

        $teamID = $_POST['teamID'] ?? null;
        if (!$teamID) self::sendJSON(false, "teamID manquant.");

        $sql = "DELETE FROM UserTeam WHERE UserID = :u AND TeamID = :t";
        $pdo->prepare($sql)->execute([':u' => $userID, ':t' => $teamID]);

        self::sendJSON(true, "Utilisateur $userID banni de l’équipe $teamID.");
    }

    public static function quitTeam(int $userID): void
    {
        global $pdo;

        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);
        $teamID = $data['teamID'] ?? null;

        if (!$teamID) self::sendJSON(false, "teamID manquant.");

        $sql = "DELETE FROM UserTeam WHERE UserID = :u AND TeamID = :t";
        $pdo->prepare($sql)->execute([':u' => $userID, ':t' => $teamID]);

        self::sendJSON(true, "Utilisateur $userID a quitté l’équipe $teamID.");
    }

    public static function connectTeam(): void
    {
        global $pdo;

        header('Content-Type: application/json');

        $data = [];
        if (isset($_POST) && !empty($_POST)) {
            $data = $_POST;
        } else {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
        }

        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Corps JSON invalide.']);
            return;
        }

        $email = $data['email']    ?? null;
        $password = $data['password'] ?? null;
        $teamID = $data['teamID']  ?? null;

        if (!$email || !$password || !$teamID) {
            echo json_encode([
                'success' => false,
                'message' => "Champs 'email', 'password' et 'teamID' requis."
            ]);
            return;
        }

        $stmt = $pdo->prepare("SELECT ID FROM User WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        $userID = $stmt->fetchColumn();

        if (!$userID) {
            echo json_encode([
                'success' => false,
                'message' => "Aucun utilisateur avec cet email."
            ]);
            return;
        }

        $stmt = $pdo->prepare("SELECT Password FROM AdminTeam WHERE UserID = :uid AND TeamID = :tid");
        $stmt->execute(['uid' => $userID, 'tid' => $teamID]);
        $dbPassword = $stmt->fetchColumn();

        if (!$dbPassword) {
            echo json_encode([
                'success' => false,
                'message' => "Aucun droit admin pour cette équipe."
            ]);
            return;
        }

        if ($dbPassword !== $password) {
            echo json_encode([
                'success' => false,
                'message' => "Mot de passe incorrect."
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => "Connexion admin réussie.",
            'apiKey' => self::getApiKeyFromUserID($userID),
            'userID' => $userID
        ]);
    }

    public static function deleteTeam($id): void
    {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM Team WHERE ID = :id");
        $stmt->execute(['id' => $id]);

        self::sendJSON(true, "Équipe $id supprimée avec succès.");
    }

    public static function updateTeam($id): void
    {
        global $pdo;

        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            self::sendJSON(false, "Corps JSON invalide.");
        }

        $fields = [
            'ActivityID', 'Name', 'Description',
            'Main_Color', 'Second_Color', 'Rating'
        ];

        $updates = [];
        $params = [':id' => $id];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updates)) {
            self::sendJSON(false, "Aucun champ à mettre à jour.");
        }

        $sql = "UPDATE Team SET " . implode(", ", $updates) . " WHERE ID = :id";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            self::sendJSON(true, "Équipe mise à jour avec succès.");
        } catch (PDOException $e) {
            self::sendJSON(false, "Erreur DB : " . $e->getMessage());
        }
    }

<<<<<<< HEAD
    public static function getUsersByTeam($teamID): void
    {
        global $pdo;
        header('Content-Type: application/json');

        $stmt = $pdo->prepare("SELECT u.* FROM User u INNER JOIN UserTeam ut ON u.ID = ut.UserID WHERE ut.TeamID = :tid");
        $stmt->execute(['tid' => $teamID]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$users) {
            self::sendJSON(false, "Aucun utilisateur trouvé pour l'équipe $teamID.");
            return;
        }

        self::sendJSON(true, "Utilisateurs de l'équipe $teamID récupérés avec succès.", ['users' => $users]);
    }
=======
    if (empty($updates)) {
        self::sendJSON(false, "Aucun champ à mettre à jour.");
    }

    $sql = "UPDATE Team SET " . implode(", ", $updates) . " WHERE ID = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        self::sendJSON(true, "Équipe mise à jour avec succès.");
    } catch (PDOException $e) {
        self::sendJSON(false, "Erreur DB : " . $e->getMessage());
    }
}

>>>>>>> parent of da59436 (Merge branch 'backend' of https://github.com/ChristianGibilaro/TCH099-project-de-session into backend)

}
