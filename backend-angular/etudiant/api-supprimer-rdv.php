<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_rdv']) || !isset($data['id_eleve'])) {
    echo json_encode(["erreur" => "Données manquantes."]);
    exit;
}

try {
    $sql_delete = "DELETE FROM rdv WHERE id_rdv = :id_rdv AND id_eleve = :id_eleve";
    $stmt = $db->prepare($sql_delete);
    $stmt->execute([
        'id_rdv' => $data['id_rdv'],
        'id_eleve' => $data['id_eleve']
    ]);

    echo json_encode(["succes" => true, "message" => "Rendez-vous annulé"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur.", "details" => $e->getMessage()]);
}