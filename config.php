<?php
ini_set('display_errors', 1); // Attempt to display errors directly in output
error_reporting(E_ALL);    // Report all types of errors and warnings


include_once 'session_demarrage.php';
//session_start();
    //configuration et connexion à la base de données
    $host = 'db';
    $db = 'mydatabase';
    $user = 'user';
    $pass = 'password';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        die("Erreur de connexion à la base de données: ".$e->getMessage());
    }
?>