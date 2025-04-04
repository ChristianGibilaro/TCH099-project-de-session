<?php

    require_once(__DIR__.'/router.php');
    
    require 'config.php';
    require 'ActivitiesController.php';
    require './src/controllers/ActiviteController.php';
    require './src/controllers/TeamController.php';
    require './src/controllers/MatchController.php';
    require './src/controllers/SteamController.php';

    //ROUTES POUR LES USERS

    //La route pour la creation d'un nouveau compte
    post('/api/creerUser', function() {
        ActivitiesController::creerUser();
    });
     //La route pour la creation d'un nouveau compte
    post('/api/connexionUser', function() {
        ActivitiesController::connexionUser();
    });

    //ROUTES POUR LES TEAMS

    post('/api/creerTeam', function() {
        TeamController::creerTeam();
    });
    get('/api/teams/$id', function($id){
        TeamController::getTeam($id);
     });

     //ROUTES POUR LES ACTIVITES

    post('/api/creerActivite', function() {
        ActiviteController::creerActivite();
        });

    get('/api/activities/$id', function($id){
        ActiviteController::getActivite($id);
        });

    //ROUTES POUR LES MATCHS

    post('/api/creerMatch', function() {
        MatchController::creerMatch();
        });   

    get('/api/steam/game/$appid', function($appid){
        SteamController::getGameData($appid);
        });

    get('/api/steam/user/$appid', function($appid){
        SteamController::getGameData($appid);
        });
    

?>