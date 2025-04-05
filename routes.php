<?php

require_once(__DIR__.'/router.php');

require 'config.php';
require 'ActivitiesController.php';
require './src/controllers/ActiviteController.php';
require './src/controllers/TeamController.php';
require './src/controllers/MatchController.php';
require './src/controllers/AndroidController.php';

//ROUTES POUR LES USERS

//La route pour la creation d'un nouveau compte
post('/api/creerUser', function() {
    ActivitiesController::creerUser();
});
    //La route pour la connexion de l'utilisateur
post('/api/connexionUser', function() {
    ActivitiesController::connexionUser();
});

get('/api/inconnu/${userID}', function($userID){
    AndroidController::getAllDatAndroid($userID);
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

/*get('/api/activities/$id', function($id){
    ActiviteController::getActivite($id);
    });*/

//ROUTES POUR LES MATCHS

post('/api/creerMatch', function() {
    MatchController::creerMatch();
    });

/***Les routes pour Android */
get('/api/singleUserOnly/${userID}', function($userID){
    AndroidController::getUserOnly($userID);
 });
 get('/api/singleMessageOnly/${msgID}', function($msgID){
    AndroidController::getMessageOnly($msgID);
 });
 get('/api/singleMessageSelonUserIDOnly/${userID}', function($userID){
    AndroidController::getMessageSelonUserID($userID);
 });
 
 get('/api/singleMessageSelonChatIDOnly/${chatID}', function($chatID){
    AndroidController::getMessageSelonChatID($chatID);
 });
 
 get('/api/singleChatOnly/${chatID}', function($chatID){
    AndroidController::getChatOnly($chatID);
 });

 get('/api/singleChateSelonMessageIDOnly/${msgID}', function($msgID){
    AndroidController::getChatSelonMessageID($msgID);
 });
?>