<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class ActivityController
{

    public static function createActivite()
    {
        global $pdo;
        header('Content-Type: application/json');
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer une activité.']);
            return;
        }
    
        // Support both JSON and form-data
        $data = [];
        if (isset($_POST) && !empty($_POST)) {
            $data = $_POST;
        } else {
            $inputJSON = file_get_contents('php://input');
            $data = json_decode($inputJSON, true);
        }
    
        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Corps de requête invalide (JSON ou formulaire attendu).']);
            return;
        }
    
        $title        = $data['title']        ?? null;
        $isSport      = $data['isSport']      ?? null;
        $mainImg      = $data['main_img']     ?? null;
        $mainImgUrl   = $data['main_img_url'] ?? null;
        $logoImg      = $data['logo_img']     ?? null;
        $logoImgUrl   = $data['logo_img_url'] ?? null;
        $description  = $data['description']  ?? null;
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
    
        // Accept either file upload, URL, or value in body for main_img
        $hasMainImg = (isset($_FILES['main_img']) && $_FILES['main_img']['error'] === UPLOAD_ERR_OK) || $mainImg || $mainImgUrl;
        $hasLogoImg = (isset($_FILES['logo_img']) && $_FILES['logo_img']['error'] === UPLOAD_ERR_OK) || $logoImg || $logoImgUrl;
    
        if (!$title || $isSport === null || !$hasMainImg || !$description) {
            echo json_encode([
                'success' => false,
                'message' => "Champs 'title', 'isSport', 'main_img' (fichier, URL ou valeur), 'description' obligatoires."
            ]);
            return;
        }
    
        // Handle main_img: file upload, URL, or value
        if (isset($_FILES['main_img']) && $_FILES['main_img']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['main_img']['tmp_name'];
            $originalFileName = basename($_FILES['main_img']['name']);
            $fileType = mime_content_type($fileTmpPath);
    
            $imageFolder = 'ressources/images/activity/';
            if (!file_exists($imageFolder)) mkdir($imageFolder, 0755, true);
    
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fileType, $allowedImageTypes)) {
                $destinationPath = $imageFolder . uniqid('activity_', true) . '_' . $originalFileName;
                if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                    echo json_encode(['success' => false, 'message' => 'Impossible de déplacer le fichier téléchargé.']);
                    return;
                }
                $mainImg = $destinationPath;
            } else {
                echo json_encode(['success' => false, 'message' => "Type de fichier non pris en charge: $fileType"]);
                return;
            }
        } elseif ($mainImgUrl) {
            $mainImg = trim($mainImgUrl);
        } // else keep $mainImg as is (from body)
    
        // Handle logo_img: file upload, URL, or value
        if (isset($_FILES['logo_img']) && $_FILES['logo_img']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo_img']['tmp_name'];
            $originalFileName = basename($_FILES['logo_img']['name']);
            $fileType = mime_content_type($fileTmpPath);
    
            $imageFolder = 'ressources/images/activity/';
            if (!file_exists($imageFolder)) mkdir($imageFolder, 0755, true);
    
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fileType, $allowedImageTypes)) {
                $destinationPath = $imageFolder . uniqid('activity_logo_', true) . '_' . $originalFileName;
                if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                    echo json_encode(['success' => false, 'message' => 'Impossible de déplacer le fichier logo téléchargé.']);
                    return;
                }
                $logoImg = $destinationPath;
            } else {
                echo json_encode(['success' => false, 'message' => "Type de fichier logo non pris en charge: $fileType"]);
                return;
            }
        } elseif ($logoImgUrl) {
            $logoImg = trim($logoImgUrl);
        } // else keep $logoImg as is (from body)
    
        $bitValue = ($isSport) ? "b'1'" : "b'0'";
    
        try {
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

    public static function getActivite($id)
    {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $input = json_decode(file_get_contents('php://input'), true);
    
        try {
            // Activity fields
            $fields = isset($input['fields']) ? $input['fields'] : '*';
            if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
                return;
            }
            $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));
            $sql = "SELECT $selectFields FROM Activity WHERE ID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$activity) {
                echo json_encode(['success' => false, 'message' => "Activité introuvable avec l'ID fourni."]);
                return;
            }
    
            
            // ActivityData fields
            $activityDataFields = isset($input['activityDataFields']) ? $input['activityDataFields'] : '*';

            if ($activityDataFields !== '*' && (!is_array($activityDataFields) || empty($activityDataFields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'activityDataFields' doit être '*' ou un tableau non vide."]);
                return;
            }
            $selectActivityDataFields = $activityDataFields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $activityDataFields));
            $sql = "SELECT $selectActivityDataFields FROM ActivityData WHERE ActivityID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $activityData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Level fields
            $levelFields = isset($input['levelFields']) ? $input['levelFields'] : '*';
            if ($levelFields !== '*' && (!is_array($levelFields) || empty($levelFields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'levelFields' doit être '*' ou un tableau non vide."]);
                return;
            }
            $selectLevelFields = $levelFields === '*' ? 'Level.*' : implode(', ', array_map(function ($field) {
                return $field === 'Index' ? 'Level.`Index`' : 'Level.' . htmlspecialchars($field);
            }, $levelFields));
            $sql = "SELECT $selectLevelFields FROM ActivityLevel 
                    INNER JOIN Level ON ActivityLevel.LevelID = Level.ID 
                    WHERE ActivityLevel.ActivityID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Filter fields
            $filterFields = isset($input['filterFields']) ? $input['filterFields'] : '*';
            if ($filterFields !== '*' && (!is_array($filterFields) || empty($filterFields))) {
                echo json_encode(['success' => false, 'message' => "Paramètre 'filterFields' doit être '*' ou un tableau non vide."]);
                return;
            }
            $filterFieldMap = [
                'PositionFilterID'      => 'pf.ID AS PositionFilterID',
                'PositionFilterName'    => 'pf.Name AS PositionFilterName',
                'PositionName'          => 'p.Name AS PositionName',
                'TypeFilterID'          => 'tf.ID AS TypeFilterID',
                'TypeFilterName'        => 'tf.Name AS TypeFilterName',
                'TypeName'              => 't.Name AS TypeName',
                'LanguageFilterID'      => 'lf.ID AS LanguageFilterID',
                'LanguageFilterName'    => 'lf.Name AS LanguageFilterName',
                'LanguageName'          => 'l.Name AS LanguageName',
                'EnvironmentFilterID'   => 'ef.ID AS EnvironmentFilterID',
                'EnvironmentFilterName' => 'ef.Name AS EnvironmentFilterName',
                'EnvironmentName'       => 'e.Name AS EnvironmentName'
            ];
            if ($filterFields === '*') {
                $selectFilterFields = implode(', ', $filterFieldMap);
            } else {
                $selectFilterFieldsArr = array_filter(array_map(function ($field) use ($filterFieldMap) {
                    return $filterFieldMap[$field] ?? '';
                }, $filterFields));
                if (empty($selectFilterFieldsArr)) {
                    echo json_encode(['success' => false, 'message' => "Aucun champ valide dans 'filterFields'."]);
                    return;
                }
                $selectFilterFields = implode(', ', $selectFilterFieldsArr);
            }
            $sql = "
                SELECT $selectFilterFields
                FROM ActivityFilter af
                LEFT JOIN PositionFilter pf ON af.PositionFilterID = pf.ID
                LEFT JOIN PositionArray pa ON pf.ID = pa.PositionFilterID
                LEFT JOIN Position p ON pa.PositionID = p.ID
                LEFT JOIN TypeFilter tf ON af.TypeFilterID = tf.ID
                LEFT JOIN Type t ON tf.ID = t.ID
                LEFT JOIN LanguageFilter lf ON af.LanguageFilterID = lf.ID
                LEFT JOIN Language l ON lf.ID = l.ID
                LEFT JOIN EnvironmentFilter ef ON af.EnvironmentFilterID = ef.ID
                LEFT JOIN Environment e ON ef.ID = e.ID
                WHERE af.ID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $filters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $positionFilters = [];
            $typeFilters = [];
            $languageFilters = [];
            $environmentFilters = [];
            
            foreach ($filters as $filter) {
                if (isset($filter['PositionFilterID']) || isset($filter['PositionFilterName'])) {
                    $entry = [];
                    if (isset($filter['PositionFilterID'])) $entry['ID'] = $filter['PositionFilterID'];
                    if (isset($filter['PositionFilterName'])) $entry['Name'] = $filter['PositionFilterName'];
                    if ($entry && !in_array($entry, $positionFilters, true)) $positionFilters[] = $entry;
                }
                if (isset($filter['TypeFilterID']) || isset($filter['TypeFilterName'])) {
                    $entry = [];
                    if (isset($filter['TypeFilterID'])) $entry['ID'] = $filter['TypeFilterID'];
                    if (isset($filter['TypeFilterName'])) $entry['Name'] = $filter['TypeFilterName'];
                    if ($entry && !in_array($entry, $typeFilters, true)) $typeFilters[] = $entry;
                }
                if (isset($filter['LanguageFilterID']) || isset($filter['LanguageFilterName'])) {
                    $entry = [];
                    if (isset($filter['LanguageFilterID'])) $entry['ID'] = $filter['LanguageFilterID'];
                    if (isset($filter['LanguageFilterName'])) $entry['Name'] = $filter['LanguageFilterName'];
                    if ($entry && !in_array($entry, $languageFilters, true)) $languageFilters[] = $entry;
                }
                if (isset($filter['EnvironmentFilterID']) || isset($filter['EnvironmentFilterName'])) {
                    $entry = [];
                    if (isset($filter['EnvironmentFilterID'])) $entry['ID'] = $filter['EnvironmentFilterID'];
                    if (isset($filter['EnvironmentFilterName'])) $entry['Name'] = $filter['EnvironmentFilterName'];
                    if ($entry && !in_array($entry, $environmentFilters, true)) $environmentFilters[] = $entry;
                }
            }
            
            // Add associated entities for each filter
            foreach ($positionFilters as &$pf) {
                if (isset($pf['ID'])) {
                    $sql = "SELECT p.* FROM PositionArray pa INNER JOIN Position p ON pa.PositionID = p.ID WHERE pa.PositionFilterID = :pfid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['pfid' => $pf['ID']]);
                    $pf['Positions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($pf);
            
            foreach ($typeFilters as &$tf) {
                if (isset($tf['ID'])) {
                    $sql = "SELECT t.* FROM TypeArray ta INNER JOIN Type t ON ta.TypeID = t.ID WHERE ta.TypeFilterID = :tfid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['tfid' => $tf['ID']]);
                    $tf['Types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($tf);
            
            foreach ($languageFilters as &$lf) {
                if (isset($lf['ID'])) {
                    $sql = "SELECT l.* FROM LanguageArray la INNER JOIN Language l ON la.LanguageID = l.ID WHERE la.LanguageFilterID = :lfid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['lfid' => $lf['ID']]);
                    $lf['Languages'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($lf);
            
            foreach ($environmentFilters as &$ef) {
                if (isset($ef['ID'])) {
                    $sql = "SELECT e.* FROM EnvironmentArray ea INNER JOIN Environment e ON ea.EnvironmentID = e.ID WHERE ea.EnvironmentFilterID = :efid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['efid' => $ef['ID']]);
                    $ef['Environments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($ef);
            
            // Combine activity data with related data
            $result = [
                'activity' => $activity,
                'activityData' => $activityData,
                'relatedData' => [
                    'levels' => $levels,
                    'positionFilters' => $positionFilters,
                    'typeFilters' => $typeFilters,
                    'languageFilters' => $languageFilters,
                    'environmentFilters' => $environmentFilters
                ]
            ];
            
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur Database: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public static function getActiviteByTitle()
{
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['title']) || empty($input['title'])) {
        echo json_encode(['success' => false, 'message' => "Paramètre 'title' manquant ou vide dans le corps de la requête."]);
        return;
    }

    $title = $input['title'];

    // Find the activity ID by title
    $stmt = $pdo->prepare("SELECT ID FROM Activity WHERE Title = :title");
    $stmt->execute(['title' => $title]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        echo json_encode(['success' => false, 'message' => "Activité introuvable avec le titre fourni."]);
        return;
    }

    // Call the same controller with the found ID
    self::getActivite($activity['ID']);
}

public static function searchActivities($title)
{
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    $query = $title;
    $limit = isset($input['limit']) ? intval($input['limit']) : 10;
    $fields = isset($input['fields']) ? $input['fields'] : '*';
    $activityDataFields = isset($input['activityDataFields']) ? $input['activityDataFields'] : '*';
    $levelFields = isset($input['levelFields']) ? $input['levelFields'] : '*';
    $filterFields = isset($input['filterFields']) ? $input['filterFields'] : '*';

    if ($limit <= 0) $limit = 10;

    // Validate fields input
    if ($fields !== '*' && (!is_array($fields) || empty($fields))) {
        echo json_encode(['success' => false, 'message' => "Paramètre 'fields' doit être '*' ou un tableau non vide."]);
        return;
    }

    // Build the SELECT clause
    $selectFields = $fields === '*' ? '*' : implode(', ', array_map('htmlspecialchars', $fields));

    // Query to find activities matching the title, ordered by similarity
    $sql = "SELECT $selectFields, LOCATE(:query, Title) AS similarity
            FROM Activity
            WHERE Title LIKE :queryPattern
            ORDER BY similarity ASC, Title ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', $query, PDO::PARAM_STR);
    $stmt->bindValue(':queryPattern', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($activities as $activity) {
        // For each activity, call getActivite with the same params
        // Simulate the input for getActivite
        $_POST = [];
        $activityID = $activity['ID'];
        // Build a fake input for getActivite
        $fakeInput = [
            'fields' => $fields,
            'activityDataFields' => $activityDataFields,
            'levelFields' => $levelFields,
            'filterFields' => $filterFields
        ];
        // Use output buffering to capture the output of getActivite
        ob_start();
        self::getActivite($activityID);
        $json = ob_get_clean();
        $decoded = json_decode($json, true);
        if ($decoded && isset($decoded['data'])) {
            $results[] = $decoded['data'];
        }
    }

    echo json_encode(['success' => true, 'data' => $results]);
}

public static function getAllActivity()
{
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    // Filters (all optional, can be omitted or partially provided)
    $languageID    = $input['languageID']    ?? null;
    $positionID    = $input['positionID']    ?? null;
    $environmentID = $input['environmentID'] ?? null;
    $typeID        = $input['typeID']        ?? null;
    $limit         = isset($input['limit']) ? intval($input['limit']) : 10;
    $start         = isset($input['start']) ? intval($input['start']) : 0; // <-- NEW

    // Sorting
    $orderBy = $input['orderBy'] ?? 'Title'; // Title, Team_Count, Player_Count, Active_Player_Count
    $order   = strtoupper($input['order'] ?? 'ASC'); // ASC or DESC

    // Fields for each section
    $fields             = $input['fields'] ?? '*';
    $activityDataFields = $input['activityDataFields'] ?? '*';
    $levelFields        = $input['levelFields'] ?? '*';
    $filterFields       = $input['filterFields'] ?? '*';

    // Validate order
    $allowedOrderBy = [
        'Title' => 'a.Title',
        'Team_Count' => 'ad.Team_Count',
        'Player_Count' => 'ad.Player_Count',
        'Active_Player_Count' => 'ad.Active_Player_Count'
    ];
    $orderBySql = $allowedOrderBy[$orderBy] ?? 'a.Title';
    $order = ($order === 'DESC') ? 'DESC' : 'ASC';

    // Build WHERE clause (all filters optional)
    $where = [];
    $params = [];

    if ($languageID !== null && $languageID !== '') {
        $where[] = 'ad.LanguageID = :languageID';
        $params[':languageID'] = $languageID;
    }
    if ($positionID !== null && $positionID !== '') {
        $where[] = 'ad.PositionID = :positionID';
        $params[':positionID'] = $positionID;
    }
    if ($environmentID !== null && $environmentID !== '') {
        $where[] = 'ad.EnvironmentID = :environmentID';
        $params[':environmentID'] = $environmentID;
    }
    if ($typeID !== null && $typeID !== '') {
        $where[] = 'ad.TypeID = :typeID';
        $params[':typeID'] = $typeID;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Select fields
    $selectFields = $fields === '*' ? 'a.*' : 'a.' . implode(', a.', array_map('htmlspecialchars', $fields));
    $selectActivityDataFields = $activityDataFields === '*' ? 'ad.*' : 'ad.' . implode(', ad.', array_map('htmlspecialchars', $activityDataFields));

    // Main query with OFFSET
    $sql = "SELECT a.ID
            FROM Activity a
            LEFT JOIN ActivityData ad ON ad.ActivityID = a.ID
            $whereSql
            GROUP BY a.ID
            ORDER BY $orderBySql $order
            LIMIT :limit OFFSET :start";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT); // <-- NEW
    $stmt->execute();

    $activityIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $results = [];
    foreach ($activityIDs as $activityID) {
        // Prepare input for getActivite
        $fakeInput = [
            'fields' => $fields,
            'activityDataFields' => $activityDataFields,
            'levelFields' => $levelFields,
            'filterFields' => $filterFields
        ];
        // Use output buffering to capture the output of getActivite
        ob_start();
        self::getActivite($activityID);
        $json = ob_get_clean();
        $decoded = json_decode($json, true);
        if ($decoded && isset($decoded['data'])) {
            $results[] = $decoded['data'];
        }
    }

    echo json_encode(['success' => true, 'data' => $results]);
}

public static function countAllActivity()
{
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    // Filters (all optional, can be omitted or partially provided)
    $languageID    = $input['languageID']    ?? null;
    $positionID    = $input['positionID']    ?? null;
    $environmentID = $input['environmentID'] ?? null;
    $typeID        = $input['typeID']        ?? null;

    // Build WHERE clause (all filters optional)
    $where = [];
    $params = [];

    if ($languageID !== null && $languageID !== '') {
        $where[] = 'ad.LanguageID = :languageID';
        $params[':languageID'] = $languageID;
    }
    if ($positionID !== null && $positionID !== '') {
        $where[] = 'ad.PositionID = :positionID';
        $params[':positionID'] = $positionID;
    }
    if ($environmentID !== null && $environmentID !== '') {
        $where[] = 'ad.EnvironmentID = :environmentID';
        $params[':environmentID'] = $environmentID;
    }
    if ($typeID !== null && $typeID !== '') {
        $where[] = 'ad.TypeID = :typeID';
        $params[':typeID'] = $typeID;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Count query
    $sql = "SELECT COUNT(DISTINCT a.ID) as total
            FROM Activity a
            LEFT JOIN ActivityData ad ON ad.ActivityID = a.ID
            $whereSql";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();

    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'count' => intval($count['total'])]);
}
}

