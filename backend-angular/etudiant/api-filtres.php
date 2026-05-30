<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../bdd/config.php';

try {
    $matieres = $db->query("SELECT id_matiere, nom FROM matiere ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    $langues = $db->query("SELECT id_langue, nom FROM langue ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "matieres" => $matieres,
        "langues" => $langues
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur de base de données", "details" => $e->getMessage()]);
}