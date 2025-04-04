<?php

include_once(__DIR__ . '/../../config.php');
require 'SteamApiKeyPrivate.php'; //Ficher à creer dans chaque ordi, NE METTEZ VOTRE CLEF DANS SteamApiKey.php, creez SteamApiKeyPrivate.php et mettez le dedans, il sera ignorer par github

class SteamController
{
    public static function getGameData($appid)
    {
        $url = 'https://store.steampowered.com/api/appdetails?appids=' . $appid;
        $json = file_get_contents($url);
        echo($json);
    }

    public static function getUserData($userid)
    {
        $url = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . SteamApiKeyPrivate::getSteamKey() . '&steamids=' . $userid;
        $json = file_get_contents($url);
        echo($json);
    }
}
