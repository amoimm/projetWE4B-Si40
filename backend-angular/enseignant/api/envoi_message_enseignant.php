<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once('../../bdd/config.php');
require_once('../../connect/Verif_connection.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé"]);
    exit;
}

verifierEnseignantOuAdmin();

$id_utilisateur = (int) $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$id_conv = (int) ($data['id_conv'] ?? 0);
$contenu = trim($data['message'] ?? '');

if ($id_conv && $contenu !== '') {
    try {
        $stmt = $db->prepare("
            INSERT INTO message (id_conv, id_redacteur, contenu, lu) 
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$id_conv, $id_utilisateur, $contenu]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes ou invalides.']);
}
?>