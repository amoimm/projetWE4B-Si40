<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    // Récupère les statistiques du tableau de bord
    $stats_users = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $stats_cours = $db->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    $stats_messages = $db->query("SELECT COUNT(*) FROM message")->fetchColumn();

    echo json_encode([
        'users' => (int)$stats_users,
        'cours' => (int)$stats_cours,
        'messages' => (int)$stats_messages
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
