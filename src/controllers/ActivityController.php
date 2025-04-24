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
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'message' => 'Utilisez POST pour créer une activité.']);
                return;
            }
    
            // Support both JSON and form-data (form-data is expected based on example)
            $data = $_POST; // Directly use $_POST for form-data
    
            // --- Basic Data Extraction ---
            $title        = $data['title']        ?? null;
            $isSport      = isset($data['isSport']) ? ($data['isSport'] === 'on' || $data['isSport'] === 'true' || $data['isSport'] === '1') : null; // Handle 'on' from checkbox
            $mainImg      = $data['main_img']     ?? null;
            $mainImgUrl   = $data['main_img_url'] ?? null;
            $logoImg      = $data['logo_img']     ?? null;
            $logoImgUrl   = $data['logo_img_url'] ?? null;
            $description  = $data['description']  ?? null;
            // Ensure pointValue is treated as an integer or null
            $pointValue     = isset($data['point_value']) ? (filter_var($data['point_value'], FILTER_VALIDATE_INT) !== false ? (int)$data['point_value'] : null) : null;
            $word4player    = $data['word_4_player']   ?? null;
            $word4teammate  = $data['word_4_teammate'] ?? null;
            $word4playing   = $data['word_4_playing']  ?? null;
            $liveUrl        = $data['live_url']        ?? null;
            $liveDesc       = $data['live_desc']       ?? null;
            $mainColor      = $data['main_color']      ?? null;
            $secondColor    = $data['second_color']    ?? null;
            $friendMain     = $data['friend_main_color']   ?? null;
            $friendSecond   = $data['friend_second_color'] ?? null;
            $apiKey         = $data['apiKey']          ?? null;
    
            // --- Array Data Extraction ---
            // Ensure they are arrays even if only one value is sent and trim whitespace
            $trimArray = function ($arr) {
                return array_map('trim', $arr);
            };
            $positionNames    = isset($data['positionIDs']) ? (is_array($data['positionIDs']) ? $trimArray($data['positionIDs']) : [trim($data['positionIDs'])]) : [];
            $languageNames    = isset($data['languageIDs']) ? (is_array($data['languageIDs']) ? $trimArray($data['languageIDs']) : [trim($data['languageIDs'])]) : [];
            $typeNames        = isset($data['TypeIDs'])     ? (is_array($data['TypeIDs'])     ? $trimArray($data['TypeIDs'])     : [trim($data['TypeIDs'])])     : [];
            $environmentNames = isset($data['EnvironmentIDs'])? (is_array($data['EnvironmentIDs'])? $trimArray($data['EnvironmentIDs']): [trim($data['EnvironmentIDs'])]): [];
    
    
            // --- Validation ---
            if (!$apiKey) {
                 http_response_code(401); // Unauthorized
                 echo json_encode(['success' => false, 'message' => "Clé API ('apiKey') manquante."]);
                 return;
            }
    
            // Accept either file upload, URL, or value in body for main_img
            $hasMainImg = (isset($_FILES['main_img']) && $_FILES['main_img']['error'] === UPLOAD_ERR_OK) || !empty($mainImg) || !empty($mainImgUrl);
            // Logo is optional now based on schema, but let's keep the check consistent if needed
            // $hasLogoImg = (isset($_FILES['logo_img']) && $_FILES['logo_img']['error'] === UPLOAD_ERR_OK) || !empty($logoImg) || !empty($logoImgUrl);
    
            if (empty($title) || $isSport === null || !$hasMainImg || empty($description)) {
                http_response_code(400); // Bad Request
                echo json_encode([
                    'success' => false,
                    'message' => "Champs obligatoires manquants ou invalides: 'title', 'isSport', 'main_img' (fichier, URL ou valeur), 'description'."
                ]);
                return;
            }
    
            // --- Image Handling ---
            $imageFolder = __DIR__ . '/../../ressources/images/activity/'; // Use absolute path
            if (!file_exists($imageFolder)) {
                 if (!mkdir($imageFolder, 0755, true)) {
                     http_response_code(500);
                     error_log("Failed to create image directory: " . $imageFolder);
                     echo json_encode(['success' => false, 'message' => "Impossible de créer le dossier d'images."]);
                     return;
                 }
            }
            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; // Added webp
    
            // Function to handle image processing (upload or URL)
            $processImage = function($fileKey, $urlKey, $baseName, $imageFolder, $allowedTypes) use ($data) {
                $imagePath = $data[$fileKey] ?? null; // Check if path/value is directly in body
                $imageUrl = $data[$urlKey] ?? null;
    
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
                    $originalFileName = basename($_FILES[$fileKey]['name']);
                    // Use finfo for more reliable MIME type detection
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileType = finfo_file($finfo, $fileTmpPath);
                    finfo_close($finfo);
    
                    if (in_array($fileType, $allowedTypes)) {
                        // Sanitize filename more robustly
                        $safeFileName = preg_replace('/[^A-Za-z0-9\._-]/', '_', $originalFileName);
                        $extension = pathinfo($safeFileName, PATHINFO_EXTENSION);
                        $destinationPath = $imageFolder . uniqid($baseName . '_', true) . '.' . ($extension ?: 'png'); // Add default extension if missing
    
                        if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                            throw new Exception("Impossible de déplacer le fichier '$originalFileName' téléchargé.");
                        }
                        // Return relative path from web root if needed for URLs, or keep absolute for file system access
                        // Assuming 'ressources' is accessible from web root
                        return 'ressources/images/activity/' . basename($destinationPath);
                    } else {
                        throw new Exception("Type de fichier '$originalFileName' non pris en charge: $fileType");
                    }
                } elseif (!empty($imageUrl)) {
                     // Basic URL validation
                     if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                         return trim($imageUrl);
                     } else {
                         throw new Exception("URL fournie pour '$urlKey' est invalide.");
                     }
                } elseif (!empty($imagePath)) {
                    // If a non-URL string was provided in the body (less common for images)
                    return trim($imagePath);
                }
                return null; // No image provided or handled
            };
    
            // --- Database Operations ---
            try {
                // Process images before transaction starts
                $mainImgPath = $processImage('main_img', 'main_img_url', 'activity', $imageFolder, $allowedImageTypes);
                $logoImgPath = $processImage('logo_img', 'logo_img_url', 'activity_logo', $imageFolder, $allowedImageTypes);
    
                // Re-validate after image processing
                 if ($mainImgPath === null) {
                     throw new Exception("L'image principale ('main_img' ou 'main_img_url') est requise et n'a pas pu être traitée.");
                 }
    
    
                $pdo->beginTransaction();
    
                // 1. Find User by API Key
                $stmtUser = $pdo->prepare("SELECT ID, Password FROM User WHERE ApiKey = :apiKey");
                $stmtUser->execute([':apiKey' => $apiKey]);
                $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
                if (!$user) {
                    // No rollback needed yet
                    http_response_code(401); // Unauthorized
                    echo json_encode(['success' => false, 'message' => "Clé API invalide."]);
                    return; // Exit before transaction starts
                }
                $userId = $user['ID'];
                $userPassword = $user['Password']; // Get the user's password hash
    
                // 2. Insert into Activity table
                $bitValue = $isSport ? 1 : 0; // Use integer 1 or 0 for BIT/BOOLEAN
    
                $sqlActivity = "
                    INSERT INTO Activity
                      (Title, IsSport, Main_Img, Description,
                       Logo_Img, Point_Value, Word_4_Player, Word_4_Teammate,
                       Word_4_Playing, Live_url, Live_Desc, Main_Color,
                       Second_Color, Friend_Main_Color, Friend_Second_Color)
                    VALUES
                      (:title, :isSport, :mainImg, :descr,
                       :logoImg, :pVal, :wPlayer, :wTeammate,
                       :wPlaying, :lUrl, :lDesc, :mColor,
                       :sColor, :fMain, :fSecond)
                ";
                $stmtActivity = $pdo->prepare($sqlActivity);
    
                // Bind parameters, ensuring correct types
                $stmtActivity->bindValue(':title',    $title);
                $stmtActivity->bindValue(':isSport',  $bitValue, PDO::PARAM_INT); // Bind as integer
                $stmtActivity->bindValue(':mainImg',  $mainImgPath); // Use processed path/URL
                $stmtActivity->bindValue(':descr',    $description);
                $stmtActivity->bindValue(':logoImg',  $logoImgPath, $logoImgPath === null ? PDO::PARAM_NULL : PDO::PARAM_STR); // Use processed path/URL
                $stmtActivity->bindValue(':pVal',     $pointValue, $pointValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT); // Bind as integer or null
                $stmtActivity->bindValue(':wPlayer',  $word4player);
                $stmtActivity->bindValue(':wTeammate',$word4teammate);
                $stmtActivity->bindValue(':wPlaying', $word4playing);
                $stmtActivity->bindValue(':lUrl',     $liveUrl);
                $stmtActivity->bindValue(':lDesc',    $liveDesc);
                $stmtActivity->bindValue(':mColor',   $mainColor);
                $stmtActivity->bindValue(':sColor',   $secondColor);
                $stmtActivity->bindValue(':fMain',    $friendMain);
                $stmtActivity->bindValue(':fSecond',  $friendSecond);
    
                $stmtActivity->execute();
                $newActivityId = $pdo->lastInsertId();
    
                // Helper function to get IDs from names (Corrected)
                $getIdsFromNames = function(array $names, string $tableName, string $columnName = 'Name') use ($pdo): array {
                    if (empty($names)) return [];

                    // Filter out empty strings that might result from trimming
                    $names = array_filter($names, function($value) { return $value !== ''; });
                    if (empty($names)) return [];

                    $placeholders = implode(',', array_fill(0, count($names), '?'));
                    // Ensure correct quoting for column name
                    $safeColumnName = '`' . str_replace('`', '', $columnName) . '`';
                    $safeTableName = '`' . str_replace('`', '', $tableName) . '`';

                    // ***** CORRECTED SQL: Select Name first, then ID *****
                    $sql = "SELECT $safeColumnName, ID FROM $safeTableName WHERE $safeColumnName IN ($placeholders)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($names);
                    // PDO::FETCH_KEY_PAIR now correctly creates [Name => ID] map
                    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                    $ids = [];
                    $notFound = [];
                    foreach ($names as $name) {
                        // This check should now work correctly
                        if (isset($results[$name])) {
                            $ids[] = $results[$name];
                        } else {
                            $notFound[] = $name;
                        }
                    }

                    if (!empty($notFound)) {
                        // Log detailed error
                        error_log("Warning: Names not found in table '$tableName': " . implode(', ', $notFound));
                        // Optionally, throw an exception to halt the process and rollback
                        // throw new Exception("Certains éléments n'ont pas été trouvés dans '$tableName': " . implode(', ', $notFound));
                    }
                    return $ids;
                };

    
                // 3. Get IDs and Insert into Array Tables
                $positionIDs    = $getIdsFromNames($positionNames, 'Position');
                $languageIDs    = $getIdsFromNames($languageNames, 'Language');
                $typeIDs        = $getIdsFromNames($typeNames, 'Type');
                $environmentIDs = $getIdsFromNames($environmentNames, 'Environment');
    
                // Log fetched IDs for debugging
                error_log("--- Activity Creation (ID: $newActivityId) ---");
                error_log("Input Position Names: " . print_r($positionNames, true));
                error_log("Fetched Position IDs: " . print_r($positionIDs, true));
                error_log("Input Language Names: " . print_r($languageNames, true));
                error_log("Fetched Language IDs: " . print_r($languageIDs, true));
                error_log("Input Type Names: " . print_r($typeNames, true));
                error_log("Fetched Type IDs: " . print_r($typeIDs, true));
                error_log("Input Environment Names: " . print_r($environmentNames, true));
                error_log("Fetched Environment IDs: " . print_r($environmentIDs, true));
                error_log("------------------------------------------");
    
    
                // Helper function to insert into junction tables
                $insertIntoJunction = function(int $activityId, array $foreignIds, string $junctionTable, string $foreignKeyColumn) use ($pdo) {
                    if (empty($foreignIds)) return; // Do nothing if no IDs were found/provided
    
                    // Prepare the statement once outside the loop
                    $sql = "INSERT INTO `$junctionTable` (ActivityID, `$foreignKeyColumn`) VALUES (:activityId, :foreignId)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':activityId', $activityId, PDO::PARAM_INT);
    
                    foreach ($foreignIds as $foreignId) {
                        // Ensure foreignId is an integer before binding
                        if (filter_var($foreignId, FILTER_VALIDATE_INT) !== false) {
                            $stmt->bindValue(':foreignId', (int)$foreignId, PDO::PARAM_INT);
                            try {
                                $stmt->execute();
                            } catch (PDOException $e) {
                                // Log error if a specific insert fails (e.g., duplicate entry, FK violation)
                                error_log("Failed to insert into $junctionTable (ActivityID: $activityId, $foreignKeyColumn: $foreignId): " . $e->getMessage());
                                // Decide if this should halt the entire process or just skip this entry
                                // throw $e; // Re-throw to cause rollback
                            }
                        } else {
                            error_log("Skipping invalid foreign ID '$foreignId' for table '$junctionTable'");
                        }
                    }
                };
    
                // Insert into junction tables
                $insertIntoJunction($newActivityId, $positionIDs,    'PositionArrayActivity',    'PositionID');
                $insertIntoJunction($newActivityId, $languageIDs,    'LanguageArrayActivity',    'LanguageID');
                $insertIntoJunction($newActivityId, $typeIDs,        'TypeArrayActivity',        'TypeID');
                $insertIntoJunction($newActivityId, $environmentIDs, 'EnvironmentArrayActivity', 'EnvironmentID');
    
                // 4. Insert into AdminActivity
                $sqlAdmin = "INSERT INTO AdminActivity (UserID, ActivityID, Password) VALUES (:userId, :activityId, :password)";
                $stmtAdmin = $pdo->prepare($sqlAdmin);
                $stmtAdmin->execute([
                    ':userId' => $userId,
                    ':activityId' => $newActivityId,
                    ':password' => $userPassword // Use the fetched password hash
                ]);
    
                // If all successful, commit
                $pdo->commit();
    
                http_response_code(201); // Created
                echo json_encode([
                    'success' => true,
                    'message' => "Activité créée avec succès.",
                    'activity_id' => $newActivityId
                ]);
    
            } catch (PDOException $e) {
                // Rollback transaction on database error
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                http_response_code(500); // Internal Server Error
                error_log("DB Error creating activity: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine()); // Log detailed error
                echo json_encode([
                    'success' => false,
                    'message' => "Erreur interne du serveur lors de la création de l'activité (DB)."
                    // 'debug_message' => "Erreur DB : " . $e->getMessage() // Optional: for debugging only
                ]);
            } catch (Exception $e) {
                 // Rollback transaction on general error (e.g., file upload, validation)
                 if ($pdo->inTransaction()) {
                     $pdo->rollBack();
                 }
                 // Determine appropriate HTTP status code based on exception type if needed
                 $statusCode = ($e->getMessage() === "Clé API invalide.") ? 401 : (($e->getMessage() === "URL fournie pour 'main_img_url' est invalide." || strpos($e->getMessage(), 'non pris en charge') !== false || strpos($e->getMessage(), 'requis') !== false) ? 400 : 500);
                 http_response_code($statusCode);
                 error_log("General Error creating activity: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
                 echo json_encode([
                     'success' => false,
                     'message' => $e->getMessage() // Provide more specific error message from the exception
                     // 'debug_message' => "Erreur : " . $e->getMessage() // Optional: for debugging only
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

