<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class FilterController
{


        // Add this new function to the FilterController class
    public static function getFiltersByType()
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        // Get filterType from body
        $input = json_decode(file_get_contents('php://input'), true);
        $requestedType = $input['filterType'] ?? null;

        if (!$requestedType) {
            echo json_encode(['success' => false, 'message' => 'Filter type is required']);
            return;
        }

        $filterTypes = [
            'PositionFilter'    => ['arrayTable' => 'PositionArray',    'elementTable' => 'Position',    'elementKey' => 'PositionID',    'filterKey' => 'PositionFilterID'],
            'TypeFilter'        => ['arrayTable' => 'TypeArray',        'elementTable' => 'Type',        'elementKey' => 'TypeID',        'filterKey' => 'TypeFilterID'],
            'LanguageFilter'    => ['arrayTable' => 'LanguageArray',    'elementTable' => 'Language',    'elementKey' => 'LanguageID',    'filterKey' => 'LanguageFilterID'],
            'EnvironmentFilter' => ['arrayTable' => 'EnvironmentArray', 'elementTable' => 'Environment', 'elementKey' => 'EnvironmentID', 'filterKey' => 'EnvironmentFilterID'],
        ];

        if (!isset($filterTypes[$requestedType])) {
            echo json_encode(['success' => false, 'message' => 'Invalid filter type']);
            return;
        }

        $info = $filterTypes[$requestedType];

        try {
            // Get all filters of the requested type
            $stmt = $pdo->prepare("SELECT * FROM $requestedType");
            $stmt->execute();
            $filters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get elements for each filter
            $result = [];
            foreach ($filters as $filter) {
                $sql = "SELECT e.* 
                        FROM {$info['arrayTable']} a
                        INNER JOIN {$info['elementTable']} e ON a.{$info['elementKey']} = e.ID
                        WHERE a.{$info['filterKey']} = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $filter['ID']]);
                $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $result[] = [
                    'filter' => $filter,
                    'elements' => $elements
                ];
            }

            echo json_encode([
                'success' => true,
                'filterType' => $requestedType,
                'filters' => $result
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function getFilterById($id)
    {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Get filterType from body if provided
        $input = json_decode(file_get_contents('php://input'), true);
        $requestedType = isset($input['filterType']) ? $input['filterType'] : null;
    
        $filterTypes = [
            'PositionFilter'   => ['arrayTable' => 'PositionArray',   'elementTable' => 'Position',   'elementKey' => 'PositionID',   'filterKey' => 'PositionFilterID'],
            'TypeFilter'       => ['arrayTable' => 'TypeArray',       'elementTable' => 'Type',       'elementKey' => 'TypeID',       'filterKey' => 'TypeFilterID'],
            'LanguageFilter'   => ['arrayTable' => 'LanguageArray',   'elementTable' => 'Language',   'elementKey' => 'LanguageID',   'filterKey' => 'LanguageFilterID'],
            'EnvironmentFilter'=> ['arrayTable' => 'EnvironmentArray','elementTable' => 'Environment','elementKey' => 'EnvironmentID','filterKey' => 'EnvironmentFilterID'],
        ];
    
        // If filterType is specified, only check that one
        if ($requestedType && isset($filterTypes[$requestedType])) {
            $typesToCheck = [$requestedType => $filterTypes[$requestedType]];
        } else {
            $typesToCheck = $filterTypes;
        }
    
        foreach ($typesToCheck as $filterTable => $info) {
            $stmt = $pdo->prepare("SELECT * FROM $filterTable WHERE ID = :id");
            $stmt->execute(['id' => $id]);
            $filter = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($filter) {
                $sql = "SELECT e.* 
                        FROM {$info['arrayTable']} a
                        INNER JOIN {$info['elementTable']} e ON a.{$info['elementKey']} = e.ID
                        WHERE a.{$info['filterKey']} = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                echo json_encode([
                    'success' => true,
                    'filterType' => $filterTable,
                    'filter' => $filter,
                    'elements' => $elements
                ]);
                return;
            }
        }
    
        echo json_encode(['success' => false, 'message' => 'Filter not found for any type.']);
    }

    public static function createFilter() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Get filterType from body if provided
        $input = json_decode(file_get_contents('php://input'), true);
        // Example input:
        // {
        //   "type": "Position",
        //   "filter_name": "Europe",
        //   "count": 2,
        //   "options": [
        //     { "Name": "Paris", "Country": "France", "State": null, "City": null, "Street": null, "Number": null, "GPS": null, "Local_Time": null },
        //     { "Name": "Berlin", "Country": "Germany", "State": null, "City": null, "Street": null, "Number": null, "GPS": null, "Local_Time": null }
        //   ]
        // }
        // For other types, options: [ { "Name": "Français" }, { "Name": "Anglais" } ]

        $type = $input['type'] ?? null;
        $filterName = $input['filter_name'] ?? null;
        $count = $input['count'] ?? null;
        $options = $input['options'] ?? [];

        if (!$type || !$filterName || !$count || !is_array($options) || empty($options)) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants ou invalides.']);
            return;
        }

        // Table and field names
        $optionTable = $type;
        $filterTable = $type . 'Filter';
        $arrayTable = $type . 'Array';
        $optionIDField = $type . 'ID';
        $filterIDField = $type . 'FilterID';

        // Position fields
        $positionFields = ['Name', 'Country', 'State', 'City', 'Street', 'Number', 'GPS', 'Local_Time'];

        try {
            $pdo->beginTransaction(); // Start the transaction

            // Check if filter name already exists for this type
            $stmtCheck = $pdo->prepare("SELECT ID FROM `$filterTable` WHERE `Name` = ?");
            $stmtCheck->execute([$filterName]);
            if ($stmtCheck->fetch()) {
                 $pdo->rollBack(); // Rollback before returning error
                 echo json_encode(['success' => false, 'message' => "Un filtre de type '$type' avec le nom '$filterName' existe déjà."]);
                 return;
            }

            // 1. Insert options and collect their IDs
            $optionIDs = [];
            foreach ($options as $opt) {
                if ($type === 'Position') {
                    // Only keep allowed fields for Position
                    $fields = [];
                    $values = [];
                    foreach ($positionFields as $field) {
                        if (array_key_exists($field, $opt)) {
                            $fields[] = "`$field`";
                            $values[] = $opt[$field];
                        }
                    }
                    $placeholders = implode(',', array_fill(0, count($fields), '?'));
                    $fieldList = implode(',', $fields);
                    $stmt = $pdo->prepare("INSERT INTO `$optionTable` ($fieldList) VALUES ($placeholders)");
                    $stmt->execute($values);
                } else {
                    // Only insert Name for other types
                    $stmt = $pdo->prepare("INSERT INTO `$optionTable` (`Name`) VALUES (?)");
                    $stmt->execute([$opt['Name']]);
                }
                $optionIDs[] = $pdo->lastInsertId();
            }

            // 2. Insert filter
            $stmt = $pdo->prepare("INSERT INTO `$filterTable` (`Name`, `Count`) VALUES (?, ?)");
            $stmt->execute([$filterName, $count]);
            $filterID = $pdo->lastInsertId();

            // 3. Link options to filter
            $stmt2 = $pdo->prepare("INSERT INTO `$arrayTable` (`$optionIDField`, `$filterIDField`) VALUES (?, ?)");
            foreach ($optionIDs as $oid) {
                $stmt2->execute([$oid, $filterID]);
            }

            $pdo->commit(); // Commit the transaction if everything was successful
            echo json_encode(['success' => true, 'filterID' => $filterID, 'optionIDs' => $optionIDs]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) { // Check if a transaction is active before rolling back
                $pdo->rollBack(); // Roll back the transaction on error
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}