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
require './src/controllers/FilterController.php';

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

post('/api/filter/create', function() {
    FilterController::createFilter();
});

post('/api/filter/all', function() {
    FilterController::getFiltersByType();
});

post('/api/filter/$id', function($id) {
    FilterController::getFilterById($id);
});


//----------------------------USER CONTROLLER----------------------------//

//Route pour avoir les donnees d'un user selon
get('/api/profile_android/${userID}', function($userID){
    AndroidController::getUserDataForAndroid($userID);
});


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
   
post('/api/activity/count', function(){
    ActivityController::countAllActivity();
});

//---------------------------- TEAM CONTROLLER ----------------------------//

post('/api/team/create', function () {
    TeamController::createTeam();                    //  ← fonction prête -- fonctionelle
});

post('/api/team/invite/${userID}', function ($userID) {
    TeamController::inviteUser($userID);            //  ← admin API‑Key requise -- fonctionelle
});

post('/api/team/join/${userID}', function ($userID) {
    TeamController::joinTeam($userID);              //  ← admin API‑Key requise -- fonctionelle
});

post('/api/team/ban/${userID}', function ($userID) {
    TeamController::banUser($userID);               //  ← admin API‑Key requise -- fonctionnelle
});

post('/api/team/quit/${userID}', function ($userID) {
    TeamController::quitTeam($userID);              //  ← admin API‑Key requise -- fonctionelle
});

post('/api/team/connect', function () {
    TeamController::connectTeam();                  //  ← admin API‑Key créée/retournée -- fonctionelle
});

get('/api/team/$id', function ($id) {
    TeamController::getTeam($id);                   //  ← params optionnels dans le body -- fonctionelle
});

post('/api/team/search', function () {
    TeamController::searchTeams();                  //  ← ?search=…&limit=… -- fonctionelle et a present un POST*
});

post('/api/team/title', function () {
    $input = json_decode(file_get_contents('php://input'), true); // fonctionelle
    $title = $input['title'] ?? '';
    TeamController::getTeamByTitle($title);
});

put('/api/team/$id', function ($id) {
    TeamController::updateTeam($id);                //  ← admin API‑Key requise -- fonctionelle
});

delete('/api/team/${id}', function ($id) {
    TeamController::deleteTeam($id);                //  ← admin API‑Key requise -- faut tester avec password que je ne possede pas
});


//----------------------------MATCH CONTROLLER----------------------------//

post('/api/match/create', function () {
    MatchController::createMatch();                // fonctionelle
});

post('/api/match/invite/${userID}', function ($userID) {
    MatchController::inviteUser($userID);          // fonctionelle
});

post('/api/match/join/${userID}', function ($userID) {
    MatchController::joinMatch($userID);           // fonctionelle 
});

post('/api/match/quit/${userID}', function ($userID) {
    MatchController::quitMatch($userID);           // fonctionelle
});

post('/api/match/ban/${userID}', function ($userID) {
    MatchController::banUser($userID);             // fonctionelle (ne peut pas ban qqun sans etre admin de la team)
});

post('/api/match/connect', function () {
    MatchController::connectMatch();               // fonctionelle
});

get('/api/match/$id', function ($id) {
    MatchController::getMatch($id);                // fonctionelle
});

post('/api/match/search', function () {
    MatchController::searchMatches();              // fonctionelle, passe de get a post (mettre les infos a filtrer dans le post)
});

put('/api/match/$id', function ($id) {
    MatchController::updateMatch($id);             // fonctionelle
});

delete('/api/match/${id}', function ($id) {
    MatchController::deleteMatch($id);             // fonctionelle
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
 // Liste des chats d’un utilisateur (avec nom et image du créateur)
post('/api/user/chats', function() {
    ChatController::getChatsForUser();
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