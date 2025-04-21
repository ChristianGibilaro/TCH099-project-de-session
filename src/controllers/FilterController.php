<?php
//include_once("/../../config.php");
include_once(__DIR__ . '/../../config.php');

class FilterController
{

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
}