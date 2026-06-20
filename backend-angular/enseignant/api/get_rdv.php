<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$id_utilisateur = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

if ($id_utilisateur === 0) {
    $headers = getallheaders();
    $id_utilisateur = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : (isset($headers['x-user-id']) ? (int)$headers['x-user-id'] : 0);
}

if ($id_utilisateur <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Identifiant utilisateur manquant."]);
    exit;
}

$id_cours = (int)($_GET['id_cours'] ?? 0);
$id_eleve = (int)($_GET['id_eleve'] ?? 0);
$date_actuelle = date("Y-m-d H:i:s");

$sql_rdv = "SELECT date_heure, est_valide, id_rdv, lieu 
            FROM rdv 
            WHERE id_cours = :id_cours 
            AND id_eleve = :id_eleve 
            AND date_heure >= :date_now 
            ORDER BY date_heure ASC";

$stmt_rdv = $db->prepare($sql_rdv);
$stmt_rdv->execute(['id_cours' => $id_cours, 'id_eleve' => $id_eleve,'date_now' => $date_actuelle]);
$rdvs = $stmt_rdv->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rdvs);
?>