<?php
header('Content-Type: application/json');
session_start();
require_once('../src/models/config.php');
require_once('Verif_connection.php');

// Récupération des données envoyées par Angular via POST
$data = json_decode(file_get_contents("php://input"), true);

$id_utilisateur = (int) $_SESSION['user_id'];
$id_conv = (int) ($data['id_conv'] ?? 0);
$contenu = trim($data['message'] ?? '');

if ($id_conv && $contenu) {
    $stmt = $db->prepare("INSERT INTO message (id_conv, id_redacteur, contenu, lu) VALUES (?, ?, ?, 0)");
    $stmt->execute([$id_conv, $id_utilisateur, $contenu]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}