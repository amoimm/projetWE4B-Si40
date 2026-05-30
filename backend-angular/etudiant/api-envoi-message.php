<?php

// api-envoi-message.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Gérer la requête préliminaire (Preflight) d'Angular
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../bdd/config.php';

// 1. Récupération du JSON envoyé par Angular
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours']) || !isset($data['id_redacteur']) || !isset($data['contenu'])) {
    echo json_encode(["erreur" => "Données manquantes."]);
    exit;
}

$id_cours = (int)$data['id_cours'];
$id_redacteur = (int)$data['id_redacteur'];
$contenu = trim($data['contenu']);
$id_conv = !empty($data['id_conv']) ? (int)$data['id_conv'] : null;

try {
    $db->beginTransaction();

    // 2. Si c'est le tout premier message, on crée la conversation
    if ($id_conv === null) {
        $sql_create_conv = "INSERT INTO conversation (id_eleve, id_cours) VALUES (:id_eleve, :id_cours)";
        $stmt_conv = $db->prepare($sql_create_conv);
        $stmt_conv->execute(['id_eleve' => $id_redacteur, 'id_cours' => $id_cours]);
        $id_conv = $db->lastInsertId(); // On récupère le nouvel ID
    }

    // 3. On insère le message
    $sql_insert_msg = "INSERT INTO message (id_conv, id_redacteur, contenu, lu) VALUES (:id_conv, :id_redacteur, :contenu, 0)";
    $stmt_msg = $db->prepare($sql_insert_msg);
    $stmt_msg->execute([
        'id_conv' => $id_conv,
        'id_redacteur' => $id_redacteur,
        'contenu' => $contenu
    ]);

    $db->commit();

    echo json_encode([
        "succes" => true,
        "id_conv" => $id_conv,
        "message" => "Message enregistré dans la BDD."
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur.", "details" => $e->getMessage()]);
}