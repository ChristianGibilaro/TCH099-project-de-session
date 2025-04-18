<?php
require_once(__DIR__.'/router.php');

require 'config.php';
require './src/controllers/ActivityController.php';
require './src/controllers/TeamController.php';
require './src/controllers/MatchController.php';
require './src/controllers/AndroidController.php';
require './src/controllers/UserController.php';
require './src/controllers/SteamController.php';
require './src/controllers/RecaptchaController.php';
require './src/controllers/ChatController.php';
require './src/controllers/AdminController.php';

//Diviser chaque routes clesse en fichiers pour chaque controller

post('/api/admin/connect', function() {
    // Call the adminConnect method directly (form data is automatically available in $_POST)
    AdminController::adminConnect();
});

get('/api/admin/hash', function() {
    // Read the JSON body from the request
    $input = json_decode(file_get_contents('php://input'), true);

    // Pass the input directly to the controller
    AdminController::generatePasswordHash($input);
});

//----------------------------USER CONTROLLER----------------------------//

// Route for creating a user
post('/api/user/create', function() {
    UserController::creerUser();
});


post('/api/user/connect', function() {
    UserController::userConnect();
});


post('/api/user/disconnect', function() {
    UserController::userDisconnect();
});

// Route for getting user data by ID
post('/api/user/id/${userID}', function($userID) {
    // Read the JSON body from the request
    $input = json_decode(file_get_contents('php://input'), true);

    // Pass the userID and the input body to the controller
    UserController::getUserById($userID, $input);
});

get('/api/user/username/${username}', function($username) {
    // Read the JSON body from the request
    $input = json_decode(file_get_contents('php://input'), true);

    // Pass the username and the input body to the controller
    UserController::getUserByUsername($username, $input);
});

get('/api/user/apikey', function() {
    // Read the JSON body from the request
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the apiKey is provided in the body
    if (!isset($input['apiKey'])) {
        echo json_encode(['success' => false, 'message' => "Paramètre 'apiKey' manquant."]);
        return;
    }

    // Pass the apiKey and the input body to the controller
    UserController::getUserByApiKey($input['apiKey'], $input);
});

post('/api/user/search/${username}', function($username) {
    $input = json_decode(file_get_contents('php://input'), true);
    UserController::searchUsersByUsername($username, $input);
});

put('/api/user/id/${userID}', function($userID) {
    AdminController::modifyUserByAdmin($userID);
});

put('/api/user/apikey', function() {
    UserController::modifyUserByApiKey();
});

delete('/api/user/${userID}', function($userID) {
    AdminController::deleteUserByAdmin($userID);
});


//put('/api/user/ban/${userID}', function($userID){
    //need admin apikey and user IP
//});

//----------------------------ACTIVITIES CONTROLLER----------------------------//

post('/api/activity/create', function() {
    ActivityController::createActivite();
});

post('/api/activity/invite/${userID}', function($userID) {
    //need Activityadmin apikey
});
    
post('/api/activity/join/${userID}', function($userID) {
    //need Activityadmin apikey
});

//post('/api/activity/ban/${userID}', function($userID) {
//    //need admin apikey
//});

post('/api/activity/quit/${userID}', function($userID) {
    //need admin apikey
});

post('/api/activity/connect', function() {
    //need admin apikey
});

post('/api/activity/all', function() {
    ActivityController::getAllActivity();
});

post('/api/activity/id/$id', function($id) {
    ActivityController::getActivite($id);
});

post('/api/activity/title', function() {
    ActivityController::getActiviteByTitle();
});

post('/api/activity/search/${title}', function($title){
    ActivityController::searchActivities($title);
});


put('/api/activity/$id', function($id){

});

delete('/api/activity/${id}', function($id){
    //need admin apikey & admin paswsword
});
   

//----------------------------TEAM CONTROLLER----------------------------//

post('/api/team/create', function() {
    // TeamController::creerTeam(); Need re-work, I will do it later
});

post('/api/team/invite/${userID}', function($userID) {
    //need admin apikey
});

post('/api/team/join/${userID}', function($userID) {
    //need admin apikey
});

post('/api/team/ban/${userID}', function($userID) {
    //need admin apikey
});

post('/api/team/quit/${userID}', function($userID) {
    //need admin apikey
});

post('/api/team/connect', function() {
     //need admin apikey
});

get('/api/team/$id', function($id){
    // TeamController::getTeam($id); Need re-work, I will do it later
    //have params in body to say what to get
});

get('/api/team/search', function(){
    //search parameter in body
    //have params in body to say what to get
});

get('/api/team/${title}', function($title){
    //search parameter in body
    //have params in body to say what to get
});

put('/api/team/$id', function($id){
    //need admin apikey
});

delete('/api/team/${id}', function($id){
    //need admin apikey
});


//----------------------------MATCH CONTROLLER----------------------------//

post('/api/match/create', function() {
    //MatchController::creerMatch(); need re-work, I will do it later
});

post('/api/match/invite/${userID}', function($userID) {
    //need admin apikey
});

post('/api/match/join/${userID}', function($userID) {
    //need admin apikey
});

post('/api/match/quit/${userID}', function($userID) {
    //need admin apikey
});

post('/api/match/ban/${userID}', function($userID) {
    //need admin apikey
});

post('/api/match/connect', function() {
     //need admin apikey
});

get('/api/match/$id', function($id){
    //have params in body to say what to get
});

get('/api/match/search', function(){
    //search parameter in body
    //have params in body to say what to get
});

put('/api/match/$id', function($id){
    //need admin apikey
});

delete('/api/match/${id}', function($id){
    //need admin apikey
});









//----------------------------CHAT CONTROLLER----------------------------// Routes no modifier, a faire plus tard

/***Les routes pour Android */
get('/api/inconnu/${userID}', function($userID){
    AndroidController::getAllDatAndroid($userID);
});


 get('/api/singleMessageOnly/${msgID}', function($msgID){
    ChatController::getMessageOnly($msgID);
 });
 get('/api/singleMessageSelonUserIDOnly/${userID}', function($userID){
    ChatController::getMessageSelonUserID($userID);
 });
 
 get('/api/singleMessageSelonChatIDOnly/${chatID}', function($chatID){
    ChatController::getMessageSelonChatID($chatID);
 });
 
 get('/api/singleChatOnly/${chatID}', function($chatID){
    ChatController::getChatOnly($chatID);
 });

 get('/api/singleChateSelonMessageIDOnly/${msgID}', function($msgID){
    ChatController::getChatSelonMessageID($msgID);
 });

 post('/api/creerChat', function(){
    ChatController::creerChat();
 });

 post('/api/envoyerMessage', function(){
    ChatController::createMessage();
 });


//---------------------------- EXTERNAL API GATES ----------------------------//

post('/api/recaptcha/verifyHuman', function() {
    RecaptchaController::verifyHuman();
});

post('/api/recaptcha/simulateBot', function() {
    RecaptchaController::simulateBot();
});

get('/api/steam/game/$appid', function($appid){
    SteamController::getGameData($appid);
    });

get('/api/steam/user/$userid', function($userid){
    SteamController::getUserData($userid);
    });


//---------------------------- ERROR HANDLING ----------------------------//

// Handle 404 errors
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Error 404: Route not found.'
]);
exit();

?>