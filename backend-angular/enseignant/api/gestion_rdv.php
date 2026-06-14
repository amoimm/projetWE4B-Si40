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

$data = json_decode(file_get_contents("php://input"), true);
$id_rdv = (int)$data['id_rdv'];
$action = $data['action']; // 'accepter' ou 'refuser' ou 'annuler'

if ($action === 'accepter') {
    $stmt = $db->prepare("UPDATE rdv SET est_valide = 1 WHERE id_rdv = ?");
    $stmt->execute([$id_rdv]);
} elseif ($action === 'refuser' || $action === 'annuler') {
    $stmt = $db->prepare("DELETE FROM rdv WHERE id_rdv = ?");
    $stmt->execute([$id_rdv]);
}
echo json_encode(['success' => true]);
?>