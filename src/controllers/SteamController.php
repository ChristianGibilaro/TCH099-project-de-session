<?php

include_once(__DIR__ . '/../../config.php');

class SteamController
{
    public static function getGameData($appid)
    {
        $url = 'https://store.steampowered.com/api/appdetails?appids=' . $appid;
        $json = file_get_contents($url);
        echo($json);
    }
}
