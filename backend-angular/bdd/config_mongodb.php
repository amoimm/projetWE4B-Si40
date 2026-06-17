<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Charge Composer

try {

    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");

    // Sélection de la base de données et de la collection
    $db_mongoDB = $mongoClient->coursconnect_nosql;
    $activitylogsCollection = $db_mongoDB->activity_logs;
    $devenirprofCollection = $db_mongoDB->demande_prof;

} catch (Exception $e) {
    die("Erreur de connexion à MongoDB : " . $e->getMessage());
}
