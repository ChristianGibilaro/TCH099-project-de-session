<?php
// --- DEBUGGING ONLY: Force display errors ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');
// Assuming UserController might have the apiKey lookup, or we implement it here/in a utility
// include_once(__DIR__ . '/UserController.php'); // Or a dedicated Auth/User utility class

class ActivityController
{
    // Flag to indicate if output was successfully sent
    private static $outputSent = false;

    // Shutdown handler function - MODIFIED to send JSON errors
    public static function handleShutdown()
    {
        // Check if output was already sent successfully by the main script logic
        if (self::$outputSent) {
            return;
        }

        $error = error_get_last();
        $response = null; // Initialize response variable

        // Check if there was a fatal error
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE])) {
            // Prepare JSON error response with details
            $response = [
                'success' => false,
                'message' => 'Erreur Serveur Interne (Shutdown Handler).',
                'error_details' => [
                    'type' => $error['type'],
                    'message' => $error['message'],
                    'file' => basename($error['file']), // Keep path short for security
                    'line' => $error['line']
                ]
            ];
        } else if (!self::$outputSent) {
             // If no fatal error occurred but output wasn't marked as sent by main logic,
             // it implies the script finished without explicit output.
             $response = [
                 'success' => false,
                 'message' => 'Erreur Serveur: Réponse inattendue (Script terminé sans sortie explicite).'
             ];
        }

        // Try to send the JSON error response if one was prepared and headers haven't been sent
        if ($response !== null && !headers_sent()) {
            http_response_code(500); // Ensure error status
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json; charset=utf-8');
            // Use flags to handle potential encoding issues even in error response
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            self::$outputSent = true; // Mark output as sent by handler
        } else if ($response !== null) {
             // Cannot send JSON, maybe log internally as a last resort if logging is ever re-enabled
             // error_log("Shutdown Handler: Headers already sent, cannot send JSON error response: " . print_r($response, true));
        }
    }

    /**
     * Handles image upload or URL input. Prioritizes file upload.
     * (Helper function remains the same as previous versions)
     */
    private static function handleImageUploadOrUrl(
        string $fileKey,
        string $urlKey,
        array $filesArray,
        array $postArray,
        string $prefix,
        string $subFolder = 'activity/',
        bool $isRequired = false,
        ?int $index = null
    ): ?string {
        $imagePath = null;
        $fileProvided = false;
        $fileError = UPLOAD_ERR_NO_FILE;
        $fileTmpPath = null;
        $originalFileName = null;

        // Check if dealing with an array of files/urls (like levels)
        if ($index !== null) {
            $fileProvided = isset($filesArray[$fileKey]['tmp_name'][$index]) && $filesArray[$fileKey]['error'][$index] !== UPLOAD_ERR_NO_FILE;
            if ($fileProvided) {
                $fileError = $filesArray[$fileKey]['error'][$index];
                $fileTmpPath = $filesArray[$fileKey]['tmp_name'][$index];
                $originalFileName = basename($filesArray[$fileKey]['name'][$index]);
            }
            $urlValue = (isset($postArray[$urlKey]) && is_array($postArray[$urlKey]) && isset($postArray[$urlKey][$index]))
                        ? trim($postArray[$urlKey][$index])
                        : null;
        } else {
            $fileProvided = isset($filesArray[$fileKey]['tmp_name']) && $filesArray[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE;
            if ($fileProvided) {
                $fileError = $filesArray[$fileKey]['error'];
                $fileTmpPath = $filesArray[$fileKey]['tmp_name'];
                $originalFileName = basename($filesArray[$fileKey]['name']);
            }
            $urlValue = isset($postArray[$urlKey]) ? trim($postArray[$urlKey]) : null;
        }

        // --- Prioritize File Upload ---
        if ($fileProvided && $fileError === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($fileTmpPath);
            $imageFolder = __DIR__ . '/../../ressources/images/' . $subFolder;

            if (!file_exists($imageFolder)) {
                if (!mkdir($imageFolder, 0777, true)) {
                    throw new Exception("Impossible de créer le dossier d'images: " . $imageFolder);
                }
            }

            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($fileType, $allowedImageTypes)) {
                $safeFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalFileName);
                $destinationPath = $imageFolder . uniqid($prefix, true) . '_' . $safeFileName;
                $relativePath = 'ressources/images/' . $subFolder . basename($destinationPath);

                if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                    throw new Exception("Impossible de déplacer le fichier téléchargé ($fileKey" . ($index !== null ? "[$index]" : "") . ").");
                }
                $imagePath = $relativePath;
            } else {
                 // Optionally throw an exception if the file type is invalid but provided
                 throw new Exception("Type de fichier non pris en charge pour $fileKey" . ($index !== null ? "[$index]" : "") . ": $fileType.");
            }
        } elseif ($fileProvided && $fileError !== UPLOAD_ERR_OK && $fileError !== UPLOAD_ERR_NO_FILE) {
             throw new Exception("Erreur lors du téléchargement du fichier ($fileKey" . ($index !== null ? "[$index]" : "") . "). Code: " . $fileError);
        }

        // --- Fallback to URL ---
        if ($imagePath === null && !empty($urlValue)) {
            if (filter_var($urlValue, FILTER_VALIDATE_URL)) {
                $imagePath = $urlValue;
            } else {
                 // Optionally throw an exception for invalid URL format
                 throw new Exception("URL invalide fournie pour $urlKey" . ($index !== null ? "[$index]" : "") . ".");
            }
        }

        // --- Check if required ---
        if ($isRequired && $imagePath === null) {
            throw new Exception("Image requise manquante ou invalide pour '$fileKey' ou '$urlKey'" . ($index !== null ? " index $index" : "") . ".");
        }

        return $imagePath;
    }

    /**
     * Finds a User ID based on the provided API Key.
     * Returns null if the key is invalid or not found.
     */
    private static function getUserIdByApiKey(PDO $pdo, ?string $apiKey): ?int
    {
        if (empty($apiKey)) {
            return null;
        }
        try {
            $stmt = $pdo->prepare("SELECT ID FROM User WHERE ApiKey = :apiKey LIMIT 1");
            $stmt->execute([':apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? (int)$user['ID'] : null;
        } catch (PDOException $e) {
            // Re-throw exception to be caught by the main handler which sends JSON
            throw $e;
        }
    }

    /**
     * Finds a User ID and Password Hash based on the provided API Key.
     * Returns null if the key is invalid or not found.
     * @return array{ID: int, PasswordHash: string}|null
     */
    private static function getUserDataByApiKey(PDO $pdo, ?string $apiKey): ?array
    {
        if (empty($apiKey)) {
            return null;
        }
        try {
            // Fetch both ID and Password hash
            $stmt = $pdo->prepare("SELECT ID, Password FROM User WHERE ApiKey = :apiKey LIMIT 1");
            $stmt->execute([':apiKey' => $apiKey]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Return ID and Password hash if user found
            return $user ? ['ID' => (int)$user['ID'], 'PasswordHash' => $user['Password']] : null;
        } catch (PDOException $e) {
             // Re-throw exception to be caught by the main handler which sends JSON
            throw $e;
        }
    }

    /**
     * Helper to build SELECT clause, handling '*' and specific fields.
     */
    private static function buildSelectClause(mixed $fields, string $alias, array $specialMappings = []): string
    {
        if ($fields === '*' || empty($fields)) {
            return $alias . '.*';
        }
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        if (!is_array($fields)) {
            return $alias . '.*'; // Fallback
        }

        $selects = [];
        foreach ($fields as $field) {
            $field = trim($field);
            if (empty($field)) continue;

            // Check for special mappings (like 'Index' needing backticks)
            if (isset($specialMappings[$field])) {
                 $selects[] = $specialMappings[$field];
            } else {
                // Basic sanitization: allow alphanumeric and underscore
                if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                    $selects[] = $alias . '.`' . $field . '`';
                }
            }
        }

        return !empty($selects) ? implode(', ', $selects) : $alias . '.*'; // Fallback if no valid fields
    }

    /**
     * Helper to find Element ID by Name in a given table.
     */
    private static function getElementIdByName(PDO $pdo, string $tableName, string $elementName): ?int
    {
        try {
            // Basic table name validation (prevent injection)
            if (!preg_match('/^[a-zA-Z_]+$/', $tableName)) {
                throw new Exception("Invalid table name provided: $tableName");
            }
            $stmt = $pdo->prepare("SELECT ID FROM `$tableName` WHERE Name = :name LIMIT 1");
            $stmt->execute([':name' => $elementName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['ID'] : null;
        } catch (PDOException $e) {
            // Log internally or re-throw to send JSON? Re-throwing for consistency.
            throw new Exception("PDO Error finding element '$elementName' in table '$tableName': " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
             // Re-throw to send JSON
             throw $e;
        }
    }

    /**
     * Helper to find or create an Element by Name in a given table and return its ID.
     */
    private static function getOrCreateElementIdByName(PDO $pdo, string $tableName, string $elementName): ?int
    {
        try {
            // Basic table name validation
            if (!preg_match('/^[a-zA-Z_]+$/', $tableName)) {
                throw new Exception("Invalid table name provided: $tableName");
            }
            $elementName = trim($elementName);
            if (empty($elementName)) {
                return null;
            }

            // Check if exists
            $stmtSelect = $pdo->prepare("SELECT ID FROM `$tableName` WHERE Name = :name LIMIT 1");
            $stmtSelect->execute([':name' => $elementName]);
            $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return (int)$result['ID'];
            } else {
                // Create if not exists
                // Note: Assumes 'Name' is the only required column for creation. Adjust if needed.
                $stmtInsert = $pdo->prepare("INSERT INTO `$tableName` (Name) VALUES (:name)");
                $stmtInsert->execute([':name' => $elementName]);
                $newId = $pdo->lastInsertId();
                return $newId ? (int)$newId : null; // Return null if insert failed unexpectedly
            }
        } catch (PDOException $e) {
            // Re-throw to ensure transaction rollback and send JSON error
            throw $e;
        } catch (Exception $e) {
             // Re-throw to ensure transaction rollback and send JSON error
             throw $e;
        }
    }

    /**
     * Helper to create an individual filter, link elements, and return the filter ID.
     */
    private static function createIndividualFilter(
        PDO $pdo,
        string $filterTable, // e.g., 'PositionFilter'
        string $filterName,
        string $baseTable, // e.g., 'Position'
        array $elementNames, // e.g., ['mun', 'duna'] from PositionName[]
        string $linkTable, // e.g., 'PositionArray'
        string $baseFkCol, // e.g., 'PositionID'
        string $filterFkCol // e.g., 'PositionFilterID'
    ): ?int {
        try {
            // Basic table name validation
            if (!preg_match('/^[a-zA-Z_]+$/', $filterTable) || !preg_match('/^[a-zA-Z_]+$/', $linkTable)) {
                throw new Exception("Invalid table name provided for filter creation.");
            }
            $filterName = trim($filterName);
            if (empty($filterName)) {
                 throw new Exception("Filter name cannot be empty for $filterTable.");
            }

            // 1. Create the individual filter record
            // Note: Assumes 'Name' and 'Count' are the columns. Adjust if needed. Count defaults to 0 initially.
            $sqlCreateFilter = "INSERT INTO `$filterTable` (Name, Count) VALUES (:name, 0)";
            $stmtCreateFilter = $pdo->prepare($sqlCreateFilter);
            $stmtCreateFilter->execute([':name' => $filterName]);
            $filterID = $pdo->lastInsertId();

            if (!$filterID) {
                throw new Exception("Failed to create filter record in $filterTable for name: $filterName");
            }
            $filterID = (int)$filterID;

            // 2. Get/Create base elements and link them to the filter
            $elementIDs = [];
            if (!empty($elementNames)) {
                $sqlLink = "INSERT INTO `$linkTable` (`$baseFkCol`, `$filterFkCol`) VALUES (:baseID, :filterID)";
                $stmtLink = $pdo->prepare($sqlLink);

                foreach ($elementNames as $elementName) {
                    $baseElementID = self::getOrCreateElementIdByName($pdo, $baseTable, $elementName);
                    if ($baseElementID !== null) {
                        $elementIDs[] = $baseElementID; // Collect IDs for potential count update
                        try {
                             $stmtLink->execute([
                                 ':baseID' => $baseElementID,
                                 ':filterID' => $filterID
                             ]);
                        } catch (PDOException $linkError) {
                             // Handle potential duplicate links if UNIQUE constraint exists
                             if ($linkError->getCode() == '23000') {
                                 // Ignore duplicate link error
                             } else {
                                 throw $linkError; // Re-throw other linking errors
                             }
                        }
                    } else {
                        // Log or throw? Throwing for consistency, indicates data issue.
                        throw new Exception("Could not get/create base element '$elementName' in table '$baseTable' while creating filter '$filterName'.");
                    }
                }
            }

            // 3. Update the count in the filter table (optional, but good practice)
            if (!empty($elementIDs)) {
                $sqlUpdateCount = "UPDATE `$filterTable` SET Count = :count WHERE ID = :id";
                $stmtUpdateCount = $pdo->prepare($sqlUpdateCount);
                $stmtUpdateCount->execute([':count' => count($elementIDs), ':id' => $filterID]);
            }

            return $filterID;

        } catch (PDOException $e) {
            // Re-throw to send JSON
            throw $e;
        } catch (Exception $e) {
            // Re-throw to send JSON
            throw $e;
        }
    }

     /**
      * Helper to find or create a composite filter record and return its ID.
      * Assumes the composite table has columns: ID, PositionFilterID, TypeFilterID, LanguageFilterID, EnvironmentFilterID
      */
    private static function getOrCreateCompositeFilter(
        PDO $pdo,
        string $compositeTable, // e.g., 'TeamFilter'
        int $posFilterId,
        int $typeFilterId,
        int $langFilterId,
        int $envFilterId
    ): ?int {
         try {
             // Basic table name validation
             if (!preg_match('/^[a-zA-Z_]+$/', $compositeTable)) {
                 throw new Exception("Invalid composite table name provided: $compositeTable");
             }

             // Check if this combination already exists
             $sqlSelect = "SELECT ID FROM `$compositeTable` WHERE
                           PositionFilterID = :posID AND TypeFilterID = :typeID AND
                           LanguageFilterID = :langID AND EnvironmentFilterID = :envID
                           LIMIT 1";
             $stmtSelect = $pdo->prepare($sqlSelect);
             $stmtSelect->execute([
                 ':posID' => $posFilterId,
                 ':typeID' => $typeFilterId,
                 ':langID' => $langFilterId,
                 ':envID' => $envFilterId
             ]);
             $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);

             if ($result) {
                 return (int)$result['ID'];
             } else {
                 // Create if not exists
                 $sqlInsert = "INSERT INTO `$compositeTable`
                               (PositionFilterID, TypeFilterID, LanguageFilterID, EnvironmentFilterID)
                               VALUES
                               (:posID, :typeID, :langID, :envID)";
                 $stmtInsert = $pdo->prepare($sqlInsert);
                 $stmtInsert->execute([
                     ':posID' => $posFilterId,
                     ':typeID' => $typeFilterId,
                     ':langID' => $langFilterId,
                     ':envID' => $envFilterId
                 ]);
                 $newId = $pdo->lastInsertId();
                 return $newId ? (int)$newId : null;
             }
         } catch (PDOException $e) {
             // Re-throw to send JSON
             throw $e;
         } catch (Exception $e) {
              // Re-throw to send JSON
              throw $e;
         }
    }

    /**
     * Helper to link directly provided element names to an activity via *ArrayActivity tables.
     */
    private static function linkDirectElements(PDO $pdo, int $activityID, array $postData)
    {
        // Define mappings: POST key => [Junction Table, Element Table, Element FK Column]
        $linkMappings = [
            'positionIDs' => ['table' => 'PositionArrayActivity', 'elementTable' => 'Position', 'column' => 'PositionID'],
            'languageIDs' => ['table' => 'LanguageArrayActivity', 'elementTable' => 'Language', 'column' => 'LanguageID'],
            'TypeIDs' => ['table' => 'TypeArrayActivity', 'elementTable' => 'Type', 'column' => 'TypeID'],
            'EnvironmentIDs' => ['table' => 'EnvironmentArrayActivity', 'elementTable' => 'Environment', 'column' => 'EnvironmentID']
            // Add more mappings here if other direct links are needed
        ];

        // error_log("linkDirectElements: Starting for ActivityID $activityID"); // Removed log

        foreach ($linkMappings as $postKey => $mapping) {
            // Check if the key exists and is an array in the POST data
            if (isset($postData[$postKey]) && is_array($postData[$postKey])) {
                $elementNames = $postData[$postKey];
                $linkTable = $mapping['table'];
                $elementTable = $mapping['elementTable'];
                $elementColumn = $mapping['column'];

                // Basic validation of table/column names from config
                if (!preg_match('/^[a-zA-Z_]+$/', $linkTable) || !preg_match('/^[a-zA-Z_]+$/', $elementColumn)) {
                     // error_log("linkDirectElements: Invalid table/column name configuration for key $postKey. Skipping."); // Removed log
                     continue; // Skip this mapping if config is wrong
                }

                // error_log("linkDirectElements: Processing key '$postKey' for table '$linkTable'. Names: " . implode(', ', $elementNames)); // Removed log

                // Prepare the insert statement for the junction table
                $sql = "INSERT INTO `$linkTable` (ActivityID, `$elementColumn`) VALUES (:activityID, :elementID)";
                $stmt = $pdo->prepare($sql);

                foreach ($elementNames as $elementName) {
                    $elementName = trim($elementName);
                    if (empty($elementName)) continue; // Skip empty names

                    // Find the ID of the element by its name in the base table
                    // This might throw an exception which will be caught by the main handler
                    $elementID = self::getElementIdByName($pdo, $elementTable, $elementName);

                    if ($elementID !== null) {
                        try {
                            // Execute the insert into the junction table
                            $stmt->execute([':activityID' => $activityID, ':elementID' => $elementID]);
                            // error_log("linkDirectElements: Linked ActivityID=$activityID to Element=$elementName (ID=$elementID) in table $linkTable."); // Removed log
                        } catch (PDOException $e) {
                            // Handle potential duplicate entries gracefully
                            if ($e->getCode() == '23000') { // Integrity constraint violation (likely duplicate)
                                // error_log("linkDirectElements: Duplicate entry skipped for ActivityID=$activityID, Element=$elementName (ID=$elementID) in table $linkTable."); // Removed log
                            } else {
                                // Re-throw other PDO errors to be sent as JSON
                                throw $e;
                            }
                        }
                    } else {
                        // Element not found - throw an exception to indicate failure
                        throw new Exception("Element '$elementName' not found in table '$elementTable'. Cannot link to ActivityID $activityID.");
                    }
                }
            } else {
                 // Log if the expected array key is missing from POST data
                 // error_log("linkDirectElements: Key '$postKey' not found or not an array in POST data."); // Removed log
            }
        }
         // error_log("linkDirectElements: Finished for ActivityID $activityID"); // Removed log
    }


    public static function createActivite()
    {
        // Register the shutdown handler at the very beginning
        register_shutdown_function(['ActivityController', 'handleShutdown']);
        // Reset the flag at the start of each request
        self::$outputSent = false;

        // Removed PHP settings log

        global $pdo;
        // --- Set headers early ---
        if (!headers_sent()) {
             header('Access-Control-Allow-Origin: *');
             header('Content-Type: application/json; charset=utf-8');
        } else {
             // Cannot send headers, maybe log internally if needed
             // error_log("createActivite Warning: Headers already sent at the beginning of the function.");
        }


        $postData = $_POST;
        $filesData = $_FILES;
        // Initialize response with default failure state
        $response = ['success' => false, 'message' => 'Erreur serveur initiale.', 'activityId' => null];
        // Default error code
        if (!headers_sent()) {
             http_response_code(500);
        }


        // 1. Authentication & Authorization
        try {
            $apiKey = $postData['apiKey'] ?? null;
            $userData = self::getUserDataByApiKey($pdo, $apiKey);
            if ($userData === null) {
                 throw new Exception("Clé API invalide ou manquante.", 401); // Use exception for flow control
            }
            $userID = $userData['ID'];
            $userPasswordHash = $userData['PasswordHash'];

            // 2. Input Validation
            $requiredFields = ['title', 'description'];
            foreach ($requiredFields as $field) {
                 if (!isset($postData[$field]) || trim($postData[$field]) === '') {
                     throw new Exception("Champ requis manquant ou vide: '$field'.", 400);
                 }
            }

            // --- Start Transaction ---
            $pdo->beginTransaction();

            // 3. Handle Image Uploads/URLs
            $mainImgPath = self::handleImageUploadOrUrl('main_img', 'main_img_url', $filesData, $postData, 'act_main_', 'activity/', true);
            $logoImgPath = self::handleImageUploadOrUrl('logo_img_url', 'logo_img_url', $filesData, $postData, 'act_logo_', 'activity/', false);

            // --- Dynamic Filter Creation Logic ---
            $createdPositionFilterID = null;
            $createdTypeFilterID = null;
            $createdLanguageFilterID = null;
            $createdEnvironmentFilterID = null;
            $newFilterCreated = false; // Flag to track if any new individual filter was made

            // Process Position Filter
            $positionFilterTitle = $postData['title'] . "-" .trim($postData['positionFiltersTitle'] ?? '');
            if (!empty($positionFilterTitle)) {
                $positionNames = $postData['PositionName']  ?? [];
                $createdPositionFilterID = self::createIndividualFilter(
                    $pdo, 'PositionFilter', $positionFilterTitle, 'Position', $positionNames,
                    'PositionArray', 'PositionID', 'PositionFilterID'
                );
                if ($createdPositionFilterID) $newFilterCreated = true;
            }

            // Process Type Filter
            $typeFilterTitle = $postData['title'] . "-" .trim($postData['typeFiltersTitle'] ?? '');
            if (!empty($typeFilterTitle)) {
                $typeNames = $postData['typeName'] ?? [];
                $createdTypeFilterID = self::createIndividualFilter(
                    $pdo, 'TypeFilter', $typeFilterTitle, 'Type', $typeNames,
                    'TypeArray', 'TypeID', 'TypeFilterID'
                );
                 if ($createdTypeFilterID) $newFilterCreated = true;
            }

            // Process Language Filter
            $languageFilterTitle = $postData['title'] . "-" .trim($postData['languageFiltersTitle'] ?? '');
            if (!empty($languageFilterTitle)) {
                $languageNames = $postData['languageName'] ?? [];
                $createdLanguageFilterID = self::createIndividualFilter(
                    $pdo, 'LanguageFilter', $languageFilterTitle, 'Language', $languageNames,
                    'LanguageArray', 'LanguageID', 'LanguageFilterID'
                );
                 if ($createdLanguageFilterID) $newFilterCreated = true;
            }

            // Process Environment Filter
            $environmentFilterTitle = $postData['title'] . "-" .trim($postData['environmentFiltersTitle'] ?? '');
            if (!empty($environmentFilterTitle)) {
                $environmentNames = $postData['environmentName'] ?? [];
                $createdEnvironmentFilterID = self::createIndividualFilter(
                    $pdo, 'EnvironmentFilter', $environmentFilterTitle, 'Environment', $environmentNames,
                    'EnvironmentArray', 'EnvironmentID', 'EnvironmentFilterID'
                );
                 if ($createdEnvironmentFilterID) $newFilterCreated = true;
            }

            // Determine final composite filter IDs (MODIFIED LOGIC)
            $activityFilterID = 1; // Always 1 as per new requirement
            $matchFilterID = 1;    // Default
            $teamFilterID = 1;     // Default

            if ($newFilterCreated) {
                $finalPosFilterId = $createdPositionFilterID ?? 1;
                $finalTypeFilterId = $createdTypeFilterID ?? 1;
                $finalLangFilterId = $createdLanguageFilterID ?? 1;
                $finalEnvFilterId = $createdEnvironmentFilterID ?? 1;

                $teamFilterID = self::getOrCreateCompositeFilter($pdo, 'TeamFilter', $finalPosFilterId, $finalTypeFilterId, $finalLangFilterId, $finalEnvFilterId);
                $matchFilterID = self::getOrCreateCompositeFilter($pdo, 'MatchFilter', $finalPosFilterId, $finalTypeFilterId, $finalLangFilterId, $finalEnvFilterId);

                if ($teamFilterID === null || $matchFilterID === null) {
                     throw new Exception("Failed to get or create composite Team/Match filter IDs.");
                }
            }

            // 4. Insert into Activity Table
            $sqlActivity = "INSERT INTO Activity (
                                Title, IsSport, Main_Img, Description, ActivityFilterID,
                                MatchFilterID, TeamFilterID, Logo_Img, Point_Value, Word_4_Player,
                                Word_4_Teammate, Word_4_Playing, Live_url, Live_Desc, Main_Color,
                                Second_Color, Friend_Main_Color, Friend_Second_Color
                            ) VALUES (
                                :title, :isSport, :mainImg, :description, :activityFilterID,
                                :matchFilterID, :teamFilterID, :logoImg, :pointValue, :word4Player,
                                :word4Teammate, :word4Playing, :liveUrl, :liveDesc, :mainColor,
                                :secondColor, :friendMainColor, :friendSecondColor
                            )";
            $stmtActivity = $pdo->prepare($sqlActivity);
            $isSportValue = isset($postData['isSport']) ? (int)(bool)$postData['isSport'] : 0;
            $stmtActivity->bindValue(':title', $postData['title']);
            $stmtActivity->bindValue(':isSport', $isSportValue, PDO::PARAM_INT);
            $stmtActivity->bindValue(':mainImg', $mainImgPath);
            $stmtActivity->bindValue(':description', $postData['description']);
            $stmtActivity->bindValue(':activityFilterID', $activityFilterID, PDO::PARAM_INT);
            $stmtActivity->bindValue(':matchFilterID', $matchFilterID, PDO::PARAM_INT);
            $stmtActivity->bindValue(':teamFilterID', $teamFilterID, PDO::PARAM_INT);
            $stmtActivity->bindValue(':logoImg', $logoImgPath);
            $stmtActivity->bindValue(':pointValue', $postData['point_value'] ?? null);
            $stmtActivity->bindValue(':word4Player', $postData['word_4_player'] ?? null);
            $stmtActivity->bindValue(':word4Teammate', $postData['word_4_teammate'] ?? null);
            $stmtActivity->bindValue(':word4Playing', $postData['word_4_playing'] ?? null);
            $stmtActivity->bindValue(':liveUrl', $postData['live_url'] ?? null);
            $stmtActivity->bindValue(':liveDesc', $postData['live_desc'] ?? null);
            $stmtActivity->bindValue(':mainColor', $postData['main_color'] ?? null);
            $stmtActivity->bindValue(':secondColor', $postData['second_color'] ?? null);
            $stmtActivity->bindValue(':friendMainColor', $postData['friend_main_color'] ?? null);
            $stmtActivity->bindValue(':friendSecondColor', $postData['friend_second_color'] ?? null);

            $stmtActivity->execute();
            $activityID = $pdo->lastInsertId();

            if (!$activityID) {
                 throw new Exception("Failed to insert activity or retrieve last insert ID.");
            }

            // 5. Insert into ActivityData Table
            $sqlActivityData = "INSERT INTO ActivityData (ActivityID, Team_Count, QuickTeam_Count, Player_Count, Active_Player_Count, Rating) VALUES (:activityID, 0, 0, 0, 0, NULL)";
            $stmtActivityData = $pdo->prepare($sqlActivityData);
            $stmtActivityData->execute([':activityID' => $activityID]);

            


            // 8. Insert into AdminActivity
            // WARNING: Still includes Password hash insertion as per previous request. Reconsider if necessary.
            $sqlAdmin = "INSERT INTO AdminActivity (UserID, ActivityID, Password) VALUES (:userID, :activityID, :password)";
            $stmtAdmin = $pdo->prepare($sqlAdmin);
            $stmtAdmin->execute([
                ':userID' => $userID,
                ':activityID' => $activityID,
                ':password' => $userPasswordHash
            ]);

            // --- Commit Transaction ---
            $pdo->commit();

            // --- Prepare Success Response ---
            $response['success'] = true;
            $response['message'] = "Activité créée avec succès.";
            $response['activityId'] = $activityID;
             if (!headers_sent()) {
                 http_response_code(201); // Set success code
             }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $response['message'] = "Erreur Base de Données: " . ($e->errorInfo[2] ?? $e->getMessage());
            $response['error_details'] = [ // Add details for frontend
                 'type' => 'PDOException',
                 'code' => $e->getCode(),
                 'sqlstate' => $e->errorInfo[0] ?? null,
                 'driver_code' => $e->errorInfo[1] ?? null,
                 'file' => basename($e->getFile()),
                 'line' => $e->getLine()
            ];
            if (!headers_sent()) {
                 if ($e->getCode() == '23000') {
                      http_response_code(409); // Conflict
                      if (strpos($response['message'], 'Activity.Title') !== false) {
                          $response['message'] = 'Erreur: Le titre de cette activité existe déjà.';
                      }
                 } else {
                      http_response_code(500); // Internal Server Error
                 }
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $response['message'] = "Erreur Serveur: " . $e->getMessage();
             $response['error_details'] = [ // Add details for frontend
                 'type' => get_class($e),
                 'code' => $e->getCode(),
                 'file' => basename($e->getFile()),
                 'line' => $e->getLine()
            ];
            if (!headers_sent()) {
                 // Use exception code for HTTP status if it's a standard HTTP code
                 $httpCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
                 http_response_code($httpCode);
            }
        }

        // --- Final Output Stage ---
        // Removed diagnostic logs

        // Attempt to encode
        $jsonOutput = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        // Check for encoding errors
        if ($jsonOutput === false) {
            $jsonError = json_last_error_msg();
            $jsonErrorCode = json_last_error();
            // Removed log

            // UTF-8 cleanup attempt (same as before)
            if ($jsonErrorCode === JSON_ERROR_UTF8) {
                 array_walk_recursive($response, function (&$item, $key) {
                     if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                         $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                     }
                 });
                 $jsonOutput = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                 // Removed log
            }

            // If still failing, prepare minimal error JSON *but don't echo yet*
            if ($jsonOutput === false) {
                 $jsonError = json_last_error_msg(); // Get latest error
                 // Removed log
                 if (!headers_sent()) {
                      if (http_response_code() < 400) http_response_code(500);
                 }
                 // Use a known-good JSON structure, include JSON error details
                 $jsonOutput = '{"success":false,"message":"Server error: Failed to encode JSON response.","json_error_details":"' . addslashes($jsonError) . '"}';
            }
        }

        // --- Try to send the final output ---
        try {
             if (headers_sent()) {
                  // Cannot output JSON, maybe output was already sent or there was an earlier error with output.
                  // The shutdown handler might catch the reason if it was a fatal error.
                  // Removed log
             } else {
                  // Set final headers if not set
                  header('Access-Control-Allow-Origin: *');
                  header('Content-Type: application/json; charset=utf-8');
                  // Ensure status code reflects final state
                  if ($response['success'] && http_response_code() < 300) {
                       if (http_response_code() !== 201) http_response_code(200);
                  } elseif (!$response['success'] && http_response_code() < 400) {
                       http_response_code(500);
                  }
                  // Removed log

                  // Flush output buffers before echoing
                  while (ob_get_level() > 0) {
                      ob_end_flush();
                  }
                  flush();
                  // Removed log

                  echo $jsonOutput;
                  self::$outputSent = true; // Mark output as successfully sent
                  // Removed log
             }
        } catch (Exception $outputEx) {
             // Catch potential errors during final output/flush
             // Removed log
             self::$outputSent = false; // Mark as failed if exception occurs here
             // The shutdown handler will likely take over now.
        }

        // Removed final log
        // exit; // Keep commented unless absolutely needed

    } // End createActivite method

    /**
     * Retrieves activity details based on ID from URL and optional fields from JSON body.
     *
     * @param int $id The ID of the activity from the URL path.
     * @return array The response array (success/data or error).
     */
    public static function getActivite(int $id) // Changed signature to accept ID directly
    {
        global $pdo;
        $response = ['success' => false, 'message' => 'Erreur initiale getActivite']; // Default error
        self::$outputSent = false; // Reset flag for this request

        // Set headers only if this function is the main entry point and headers not sent
        if (php_sapi_name() !== 'cli' && !headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            // --- Get optional fields from JSON body ---
            $inputBody = json_decode(file_get_contents('php://input'), true);
            // Handle potential JSON decoding errors
            if (json_last_error() !== JSON_ERROR_NONE && !empty(file_get_contents('php://input'))) {
                 throw new Exception("Corps JSON invalide fourni.", 400);
            }
            $fieldParams = $inputBody['fields'] ?? null; // Expect 'fields' key in the JSON body

            // --- Validate ID (already received as int) ---
            if ($id <= 0) {
                // This check might be redundant if the routing enforces numeric ID, but good for safety
                throw new Exception("ID d'activité invalide fourni dans l'URL.", 400);
            }

            // --- Fetch Activity ---
            // Define fields to select for Activity, default to all if not specified
            $activityFields = $fieldParams['activity'] ?? '*';
            $activitySelectClause = self::buildSelectClause($activityFields, 'a');

            $sqlActivity = "SELECT {$activitySelectClause} FROM Activity a WHERE a.ID = :id";
            $stmtActivity = $pdo->prepare($sqlActivity);
            $stmtActivity->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtActivity->execute();
            $activity = $stmtActivity->fetch(PDO::FETCH_ASSOC);

            if (!$activity) {
                 // Throw exception to be caught and formatted as JSON error
                 throw new Exception("Activité introuvable avec l'ID fourni.", 404);
            }

            // --- Fetch ActivityData ---
            // Define fields to select for ActivityData, default to all if not specified
            $activityDataFields = $fieldParams['activityData'] ?? '*';
            $activityDataSelectClause = self::buildSelectClause($activityDataFields, 'ad');

            $sqlActivityData = "SELECT {$activityDataSelectClause} FROM ActivityData ad WHERE ad.ActivityID = :activityID";
            $stmtActivityData = $pdo->prepare($sqlActivityData);
            $stmtActivityData->bindParam(':activityID', $id, PDO::PARAM_INT); // Use the same ID
            $stmtActivityData->execute();
            $activityData = $stmtActivityData->fetch(PDO::FETCH_ASSOC);

            // --- Fetch Related Data (Levels, Positions, etc. - Assuming this logic exists below) ---
            // Example placeholder for fetching related data (adapt based on your actual implementation)
            // This logic should use the $id variable
            $levels = []; // Replace with actual fetch logic using $id
            $positions = []; // Replace with actual fetch logic using $id
            $types = []; // Replace with actual fetch logic using $id
            $languages = []; // Replace with actual fetch logic using $id
            $environments = []; // Replace with actual fetch logic using $id
            // ... (Your existing logic to fetch related data based on $id) ...


            // --- Assemble Result ---
            $result = [
                'activity' => $activity,
                'activityData' => $activityData ?: null, // Return null if no data found
                'relatedData' => [ // Include related data if fetched
                    'levels' => $levels,
                    'positions' => $positions,
                    'types' => $types,
                    'languages' => $languages,
                    'environments' => $environments
                ]
            ];

            $result['activity']['Main_Img'] = "http://localhost:9999/" . $result['activity']['Main_Img'];
            $result['activity']['Logo_Img'] = "http://localhost:9999/" . $result['activity']['Logo_Img'];

            // Prepare success response structure
            $response = ['success' => true, 'data' => $result];
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                 http_response_code(200);
            }

        } catch (PDOException $e) {
            // Handle PDO Exceptions (Database Errors)
            if (php_sapi_name() !== 'cli' && !headers_sent()) http_response_code(500);
            $response = [
                'success' => false,
                'message' => 'Erreur Base de Données: ' . ($e->errorInfo[2] ?? $e->getMessage()),
                'error_details' => [
                    'type' => 'PDOException',
                    'code' => $e->getCode(),
                    'sqlstate' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ];
        } catch (Exception $e) {
            // Handle other Exceptions (e.g., invalid ID, not found, invalid JSON)
             $httpCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
             if (php_sapi_name() !== 'cli' && !headers_sent()) http_response_code($httpCode);
            $response = [
                'success' => false,
                'message' => 'Erreur Serveur: ' . $e->getMessage(),
                'error_details' => [
                    'type' => get_class($e),
                    'code' => $e->getCode(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ];
        }

        // --- Final Output Stage ---
        // Output JSON only if called as the main script entry point and output not already sent
        if (php_sapi_name() !== 'cli' && !self::$outputSent && !headers_sent()) {
             $jsonOutput = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
             if ($jsonOutput === false) {
                  // Handle JSON encoding error
                  http_response_code(500);
                  $jsonError = json_last_error_msg();
                  echo '{"success":false,"message":"Server error: Failed to encode JSON response.","json_error_details":"' . addslashes($jsonError) . '"}';
             } else {
                  echo $jsonOutput;
             }
             self::$outputSent = true; // Mark output sent
        } elseif (php_sapi_name() !== 'cli' && !self::$outputSent && headers_sent()) {
             // Headers already sent, cannot output JSON. Maybe log this situation.
             // error_log("getActivite: Headers already sent, cannot send JSON response for ID {$id}.");
        }

        // Return the response array for internal calls or testing
        return $response;
    }

    /**
     * Searches for activities based on criteria provided in the JSON body.
     * Supports filtering, field selection, and pagination.
     *
     * @return array The response array (success/data or error).
     */
    public static function searchActivites() // Simplified signature
    {
        global $pdo;
        $response = ['success' => false, 'message' => 'Erreur initiale searchActivites']; // Default error
        self::$outputSent = false; // Reset flag

        // Set headers only if this function is the main entry point and headers not sent
        if (php_sapi_name() !== 'cli' && !headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            // --- Get parameters from JSON body ---
            $inputBody = json_decode(file_get_contents('php://input'), true);
            // Handle potential JSON decoding errors
            if (json_last_error() !== JSON_ERROR_NONE && !empty(file_get_contents('php://input'))) {
                 throw new Exception("Corps JSON invalide fourni.", 400);
            }

            // Extract search query term
            $queryTerm = $inputBody['query'] ?? null;

            // Extract field selection (expecting an array directly or '*')
            $activityFields = $inputBody['fields'] ?? '*';
            // Validate that fields is an array or '*'
            if (!is_array($activityFields) && $activityFields !== '*') {
                 throw new Exception("Le paramètre 'fields' doit être un tableau de noms de champs ou '*'.", 400);
            }

            // Extract pagination from the 'pagination' object
            $paginationData = $inputBody['pagination'] ?? [];
            $page = filter_var($paginationData['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $limit = filter_var($paginationData['limit'] ?? 10, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($page === false) $page = 1;
            if ($limit === false) $limit = 10;
            $offset = ($page - 1) * $limit;

            // --- Build Query ---
            $params = [];
            $joins = []; // Keep joins array in case future filters need it
            $whereClauses = [];

            // Define fields to select for Activity
            // Pass the array or '*' directly to buildSelectClause
            $activitySelectClause = self::buildSelectClause($activityFields, 'a');

            // Base query
            $sqlBase = "FROM Activity a";

            // Query term filter (searching Title and Description)
            if (!empty($queryTerm)) {
                // Add OR condition for searching in multiple fields
                $whereClauses[] = "(a.Title LIKE :query OR a.Description LIKE :query)";
                $params[':query'] = '%' . $queryTerm . '%';
            }

            // --- Construct Final SQL ---
            $sqlSelect = "SELECT DISTINCT {$activitySelectClause} "; // Use DISTINCT if joins are ever added back
            $sqlCount = "SELECT COUNT(DISTINCT a.ID) "; // Count distinct activities

            $sqlJoins = !empty($joins) ? ' ' . implode(' ', $joins) : '';
            $sqlWhere = !empty($whereClauses) ? ' WHERE ' . implode(' AND ', $whereClauses) : ''; // Use AND if other filters are added
            $sqlOrder = " ORDER BY a.ID DESC"; // Example ordering
            $sqlLimit = " LIMIT :limit OFFSET :offset";

            $sql = $sqlSelect . $sqlBase . $sqlJoins . $sqlWhere . $sqlOrder . $sqlLimit;
            $sqlTotal = $sqlCount . $sqlBase . $sqlJoins . $sqlWhere;

            // --- Execute Count Query ---
            $stmtTotal = $pdo->prepare($sqlTotal);
            if ($stmtTotal === false) {
                $errorInfo = $pdo->errorInfo();
                throw new Exception("PDO prepare() failed for count query. SQLSTATE[{$errorInfo[0]}]: {$errorInfo[2]}");
            }

            // Execute count query - Explicitly handle empty params case
            if (empty($params)) {
                $stmtTotal->execute(); // Execute without arguments if $params is empty
            } else {
                $stmtTotal->execute($params); // Execute with the parameters array otherwise
            }

            $totalRecords = $stmtTotal->fetchColumn();
            $totalPages = ceil($totalRecords / $limit);

            // --- Execute Search Query ---
            $stmt = $pdo->prepare($sql);
             if ($stmt === false) {
                $errorInfo = $pdo->errorInfo();
                throw new Exception("PDO prepare() failed for search query. SQLSTATE[{$errorInfo[0]}]: {$errorInfo[2]}");
            }

            // Combine all parameters needed for the main query
            // Start with filter params (e.g., :query if it exists)
            $queryParams = $params;

            // Add limit and offset to the parameters array, binding as integers
            $queryParams[':limit'] = $limit;
            $queryParams[':offset'] = $offset;

            // Bind parameters explicitly for type safety (especially LIMIT/OFFSET)
            foreach ($queryParams as $key => &$val) {
                 if ($key === ':limit' || $key === ':offset') {
                     $stmt->bindParam($key, $val, PDO::PARAM_INT);
                 } else {
                     $stmt->bindParam($key, $val); // Default is PDO::PARAM_STR
                 }
            }
            unset($val); // Unset reference

            // Execute the statement
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepend base URL to image paths if they are relative paths and exist
            $baseUrl = "http://localhost:9999/"; // Consider making this configurable
            foreach ($activities as &$activity) {
                if (isset($activity['Main_Img']) && !empty($activity['Main_Img']) && !filter_var($activity['Main_Img'], FILTER_VALIDATE_URL)) {
                    $activity['Main_Img'] = $baseUrl . ltrim($activity['Main_Img'], '/');
                }
                if (isset($activity['Logo_Img']) && !empty($activity['Logo_Img']) && !filter_var($activity['Logo_Img'], FILTER_VALIDATE_URL)) {
                    $activity['Logo_Img'] = $baseUrl . ltrim($activity['Logo_Img'], '/');
                }
            }
            unset($activity); // Break the reference after the loop

            // --- Assemble Result ---
            $response = [
                'success' => true,
                'data' => $activities,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => (int)$totalPages,
                    'totalRecords' => (int)$totalRecords,
                    'limit' => $limit
                ]
            ];
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                 http_response_code(200);
            }

        } catch (PDOException $e) {
            // Handle PDO Exceptions
            if (php_sapi_name() !== 'cli' && !headers_sent()) http_response_code(500);
            $response = [
                'success' => false,
                'message' => 'Erreur Base de Données: ' . ($e->errorInfo[2] ?? $e->getMessage()),
                'error_details' => [ // Added details
                    'type' => 'PDOException',
                    'code' => $e->getCode(),
                    'sqlstate' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ];
        } catch (Exception $e) {
            // Handle other Exceptions
             $httpCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
             if (php_sapi_name() !== 'cli' && !headers_sent()) http_response_code($httpCode);
            $response = [
                'success' => false,
                'message' => 'Erreur Serveur: ' . $e->getMessage(),
                'error_details' => [ // Added details
                    'type' => get_class($e),
                    'code' => $e->getCode(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ];
        }

        // --- Final Output Stage ---
        // (Identical output logic as in getActivite)
        if (php_sapi_name() !== 'cli' && !self::$outputSent && !headers_sent()) {
             $jsonOutput = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
             if ($jsonOutput === false) {
                  http_response_code(500);
                  $jsonError = json_last_error_msg();
                  // Ensure content type is set even for JSON encoding errors
                  header('Content-Type: application/json; charset=utf-8');
                  echo '{"success":false,"message":"Server error: Failed to encode JSON response.","json_error_details":"' . addslashes($jsonError) . '"}';
             } else {
                  // Ensure content type is set before output
                  header('Content-Type: application/json; charset=utf-8');
                  echo $jsonOutput;
             }
             self::$outputSent = true;
        } elseif (php_sapi_name() !== 'cli' && !self::$outputSent && headers_sent()) {
             // error_log("searchActivites: Headers already sent, cannot send JSON response.");
        }

        return $response; // Return for internal use/testing
    }

} // End Class ActivityController

