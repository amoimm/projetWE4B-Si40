<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

$id_conv = (int) ($_GET['id'] ?? $_GET['id_conv'] ?? 0);
$id_utilisateur = (int) $_SESSION['user_id'];

if ($id_conv <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Identifiant de conversation manquant ou invalide."]);
    exit;
}

try {
    // Mettre à jour les messages de l'autre rédacteur comme lus
    $stmtUpdate = $db->prepare("
        UPDATE message 
        SET lu = 1 
        WHERE id_conv = ? AND id_redacteur != ? AND lu = 0
    ");
    $stmtUpdate->execute([$id_conv, $id_utilisateur]);

    // Charger tous les messages
    $stmt = $db->prepare("
        SELECT 
            m.id_message,
            m.contenu, 
            m.heure, 
            m.id_redacteur, 
            u.prenom, 
            u.nom
        FROM message m
        JOIN utilisateurs u ON u.id_utilisateurs = m.id_redacteur
        WHERE m.id_conv = ?
        ORDER BY m.heure ASC
    ");
    $stmt->execute([$id_conv]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast types
    foreach ($messages as &$msg) {
        $msg['id_message'] = (int)$msg['id_message'];
        $msg['id_redacteur'] = (int)$msg['id_redacteur'];
    }

    echo json_encode($messages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>