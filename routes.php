<?php

    require_once(__DIR__.'/router.php');
    
    require 'config.php';
    require 'ActivitiesController.php';
    require './src/controllers/ActiviteController.php';
    require './src/controllers/TeamController.php';


    //La route pour la creation d'un nouveau compte
    post('/api/creerUser', function() {
        ActivitiesController::creerUser();
    });
     //La route pour la creation d'un nouveau compte
    post('/api/connexionUser', function() {
        ActivitiesController::connexionUser();
    });

    post('/api/creerTeam', function() {
        TeamController::creerTeam();
    });
    get('/api/teams/$id', function($id){
        TeamController::getTeam($id);
     });

    post('/api/creerActivite', function() {
        ActiviteController::creerActivite();
        });

    get('/api/activities/$id', function($id){
        ActiviteController::getActivite($id);
        });


?>