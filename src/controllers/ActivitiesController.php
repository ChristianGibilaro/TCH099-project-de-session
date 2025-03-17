<?php

class ActivitiesController
{

    public static function translateIds($activities)
    {
        global $pdo;

        foreach ($activities as &$options) {
            $findcoach = $pdo->prepare("SELECT name FROM coaches WHERE id = :id");
            $findcoach->bindParam(':id', $options['coach_id']);
            $findcoach->execute();
            $options['coach_id'] = $findcoach->fetch(PDO::FETCH_ASSOC)['name'];

            $findlocation = $pdo->prepare("SELECT name FROM locations WHERE id = :id");
            $findlocation->bindParam(':id', $options['location_id']);
            $findlocation->execute();
            $options['location_id'] = $findlocation->fetch(PDO::FETCH_ASSOC)['name'];
        }
        return $activities;
    }
    public static function translateId($activity)
    {
        global $pdo;

        $findcoach = $pdo->prepare("SELECT name FROM coaches WHERE id = :id");
        $findcoach->bindParam(':id', $activity['coach_id']);
        $findcoach->execute();
        $activity['coach_id'] = $findcoach->fetch(PDO::FETCH_ASSOC)['name'];

        $findlocation = $pdo->prepare("SELECT name FROM locations WHERE id = :id");
        $findlocation->bindParam(':id', $activity['location_id']);
        $findlocation->execute();
        $activity['location_id'] = $findlocation->fetch(PDO::FETCH_ASSOC)['name'];
        return $activity;
    }

    public static function getAllActivities()
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities');
            $activities = $stmt->fetchAll();
            echo json_encode(ActivitiesController::translateIds($activities));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function getRandomActivities()
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities ORDER BY RAND() LIMIT 5');
            $randImages = $stmt->fetchAll();
            echo json_encode($randImages);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function getActivity($id)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->prepare('SELECT * FROM activities WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(ActivitiesController::translateId($activity));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getFilteredActivities($filter)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {
            $stmt = $pdo->query('SELECT * FROM activities');
            $a = $stmt->fetchAll();
            $activities = ActivitiesController::translateIds($a);
            $filtred = array();

            for ($i = 0; $i < count($activities); $i++) {
                if (($filter['niveau'] == $activities[$i]['level_id'] || $filter['niveau'] == "tous") &&
                    ($filter['lieu'] == $activities[$i]['location_id']  || $filter['lieu'] == "tous") &&
                    ($filter['coach'] == $activities[$i]['coach_id'] || $filter['coach'] == "tous") &&
                    ($filter['jour'] == $activities[$i]['schedule_day'] || $filter['jour'] == "tous")
                ) {
                    array_push($filtred, $activities[$i]);
                }
            }
            echo json_encode($filtred);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public static function addNewActivite($activity)
    {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON
    
        try {
            //Find a higher ID
            $stmt = $pdo->query('SELECT max(id) as max_id FROM activities');
            $highestId = $stmt->fetchColumn();
            $newId = 1+ $highestId ;

            //Find the corresponding id of the coach
            $findcoach = $pdo->prepare("SELECT id FROM coaches WHERE name = :name");
            $findcoach->bindParam(':name', $activity['coach_id']);
            $findcoach->execute();
            $coachID = $findcoach->fetchColumn();

            //Find the corresponding id of the location 
            $findlocation = $pdo->prepare("SELECT id FROM locations WHERE name = :name");
            $findlocation->bindParam(':name', $activity['location_id']);
            $findlocation->execute();
            $locationID = $findlocation->fetchColumn();

            $query = "INSERT INTO activities(id ,name, description, image, level_id, coach_id, schedule_day, schedule_time, location_id) 
                      VALUES (:id, :name, :description, :image, :level_id, :coach_id, :schedule_day, :schedule_time, :location_id);";
    
            $stmt = $pdo->prepare($query);

            $stmt->bindParam(":id", $newId);
            $stmt->bindParam(":name", $activity['name']);
            $stmt->bindParam(":description", $activity['description']);
            $stmt->bindParam(":image", $activity['image']);
            $stmt->bindParam(":level_id", $activity['level_id']);
            $stmt->bindParam(":coach_id", $coachID);
            $stmt->bindParam(":schedule_day", $activity['schedule_day']);
            $stmt->bindParam(":schedule_time", $activity['schedule_time']);
            $stmt->bindParam(":location_id",  $locationID);
    
            $stmt->execute();
    
            http_response_code(200);  // succès
            echo json_encode(["message" => "Activité ajoutée avec succès!"]);
        } catch (PDOException $e) {
            http_response_code(500);  // erreur serveur
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function modifierActivite($id, $data){
        global $pdo;

        header('Access-Control-Allow-Origin: *');  // autorise toutes les origines (ou remplace * par une origine spécifique)
        header('Content-Type: application/json; charset=utf-8');  // indique que la réponse est en JSON

        try {

            //Find the corresponding id of the coach
            $findcoach = $pdo->prepare("SELECT id FROM coaches WHERE name = :name");
            $findcoach->bindParam(':name', $data['coach_id']);
            $findcoach->execute();
            $coachID = $findcoach->fetchColumn();


            //Find the corresponding id of the location 
            $findlocation = $pdo->prepare("SELECT id FROM locations WHERE name = :name");
            $findlocation->bindParam(':name', $data['location_id']);
            $findlocation->execute();
            $locationID = $findlocation->fetchColumn();

            //$query = "UPDATE activities(name, description, image, level_id, coach_id, schedule_day, schedule_time, location_id) 
            //VALUES (:id, :name, :description, :image, :level_id, :coach_id, :schedule_day, :schedule_time, :location_id) WHERE id = :id";
            $query = "UPDATE activities SET name = :name ,description = :description ,image = :image ,
             level_id = :level_id ,coach_id = :coach_id,schedule_day = :schedule_day,schedule_time = :schedule_time,location_id = :location_id WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":image", $data['image']);
            $stmt->bindParam(":level_id", $data['level_id']);
            $stmt->bindParam(":coach_id", $coachID);
            $stmt->bindParam(":schedule_day", $data['schedule_day']);       
            $stmt->bindParam(":schedule_time", $data['schedule_time']);
            $stmt->bindParam(":location_id", $locationID);

            $stmt->execute();

            http_response_code(200);  // succès
            echo json_encode(["message" => "Activité ajoutée avec succès!"]);
        } catch (PDOException $e) {
            http_response_code(500);  // erreur serveur
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
