<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Charge Composer

try {

    $client = new MongoDB\Client("mongodb://localhost:27017");

    // Sélection de la base de données et de la collection (équivalent d'une table)
    $db = $client->coursconnect_nosql;
    $activitylogsCollection = $db->activity_logs;
    $devenirprofCollection = $db->demande_prof;

} catch (Exception $e) {
    die("Erreur de connexion à MongoDB : " . $e->getMessage());
}
