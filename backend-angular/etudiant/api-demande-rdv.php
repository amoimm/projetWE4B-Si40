<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours']) || !isset($data['id_eleve'])) {
    echo json_encode(["erreur" => "Données manquantes."]);
    exit;
}

$date_heure = $data['date_cours'] . ' ' . $data['heure_cours'] . ':00';
$duree_int = ($data['duree_cours'] === '30 min') ? 30 : 60;
$lieu = trim($data['lieu']);
$commentaire = "Langue : " . trim($data['langue_cours']);

try {
    $sql_rdv = "INSERT INTO rdv (id_cours, id_eleve, date_heure, duree, lieu, commentaire, est_valide) 
                VALUES (:id_cours, :id_eleve, :date_heure, :duree, :lieu, :commentaire, 0)";
    $stmt_rdv = $db->prepare($sql_rdv);
    $stmt_rdv->execute([
        'id_cours' => $data['id_cours'],
        'id_eleve' => $data['id_eleve'],
        'date_heure' => $date_heure,
        'duree' => $duree_int,
        'lieu' => $lieu,
        'commentaire' => $commentaire
    ]);

    echo json_encode(["succes" => true, "message" => "Rendez-vous demandé avec succès"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur.", "details" => $e->getMessage()]);
}