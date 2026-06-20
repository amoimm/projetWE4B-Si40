<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
// IMPORTANT : On ajoute 'X-User-Id' dans la liste des headers autorisés par le CORS
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$headers = getallheaders();

$id_session = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : 0;

if ($id_session <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Identifiant utilisateur manquant dans les entêtes."]);
    exit;
}


$id_cours = (int) ($_GET['id'] ?? 0);

if ($id_cours <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Identifiant de cours invalide."]);
    exit;
}

try {
    $sql = "SELECT 
                c.id_cours,
                c.prix_heure,
                c.mode_cours,
                c.camera_obligatoire,
                c.suivi,
                c.description,
                em.id_em,
                em.id_matiere,
                em.id_utilisateur,
                GROUP_CONCAT(DISTINCT el.id_el) AS langues_ids
            FROM cours c
            LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em
            LEFT JOIN enseignant_langue el ON em.id_em = el.id_em
            WHERE c.id_cours = ?
            GROUP BY c.id_cours";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id_cours]);
    $cours = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cours || ((int)$cours['id_utilisateur'] !== $id_session)) {
        http_response_code(404);
        echo json_encode(["error" => "Cours non trouvé ou accès refusé."]);
        exit;
    }

    $langues_ids = [];
    if (!empty($cours['langues_ids'])) {
        $langues_ids = array_map('intval', explode(',', $cours['langues_ids']));
    }

    $response = [
        "id_cours" => (int) $cours['id_cours'],
        "id_matiere" => (int) $cours['id_matiere'],
        "prix_heure" => (float) $cours['prix_heure'],
        "mode_cours" => $cours['mode_cours'],
        "camera_obligatoire" => (int)$cours['camera_obligatoire'] === 1,
        "suivi" => (int)$cours['suivi'] === 1,
        "description" => $cours['description'],
        "langues" => $langues_ids
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>