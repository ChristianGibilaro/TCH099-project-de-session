<?php
require_once(__DIR__.'/router.php');

require 'config.php';
require './src/controllers/ActivitiesController.php';
require './src/controllers/ActiviteController.php';
require './src/controllers/TeamController.php';
require './src/controllers/MatchController.php';
require './src/controllers/AndroidController.php';
require './src/controllers/UserController.php';
require './src/controllers/SteamController.php';
require './src/controllers/RecaptchaController.php';


get('/api/test', function() {
    echo('test!');
    // Add these lines:
    if (ob_get_level() > 0) { // Check if buffering is active
         ob_flush(); // Send output buffer content if any
    }
    flush(); // Force PHP to send its output to the webserver
});

//ROUTES POUR LES USERS
post('/api/creerUser', function() {
    ActivitiesController::creerUser();
});
//La route pour la creation d'un nouveau compte
/*post('/api/createUser', function() {
    UserController::createUser();
});*/
    //La route pour la connexion de l'utilisateur
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

/*get('/api/activities/$id', function($id){
    ActiviteController::getActivite($id);
    });*/

//ROUTES POUR LES MATCHS

post('/api/creerMatch', function() {
    MatchController::creerMatch();
    });   

get('/api/steam/game/$appid', function($appid){
    SteamController::getGameData($appid);
    });

get('/api/steam/user/$userid', function($userid){
    SteamController::getUserData($userid);
    });
    

post('/api/creerMatch', function() {
    MatchController::creerMatch();
    });

/***Les routes pour Android */
get('/api/inconnu/${userID}', function($userID){
    AndroidController::getAllDatAndroid($userID);
});
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

 post('/api/creerChat', function(){
    AndroidController::creerChat();
 });

 post('/api/envoyerMessage', function(){
    AndroidController::createMessage();
 });

// Route for verifying the reCAPTCHA V3 token
post('/api/verifyHuman', function() {
    RecaptchaController::verifyHuman();
});

// Route for simulating a bot
post('/api/simulateBot', function() {
    RecaptchaController::simulateBot();
});

http_response_code(404); // Set HTTP status code to 404 Not Found
header('Content-Type: application/json'); // Set content type to JSON
echo json_encode([
    'success' => false,
    'message' => 'Error 404: Route not found.'
]);
exit(); // Explicitly exit here to be sure nothing else runs
?>