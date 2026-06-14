<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$id_session = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

if ($id_session === 0) {
    $headers = getallheaders();
    $id_session = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : (isset($headers['x-user-id']) ? (int)$headers['x-user-id'] : 0);
}

if ($id_session <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Identifiant utilisateur manquant dans les entêtes."]);
    exit;
}

$is_admin = false;

$data = json_decode(file_get_contents("php://input"), true);

$id_cours = (int) ($data['id_cours'] ?? 0);
$matiere = (int) ($data['matiere'] ?? 0);
$langues_selectionnees = $data['langues'] ?? [];
$prix_heure = floatval($data['prix_heure'] ?? 0);
$mode_cours = htmlspecialchars(trim($data['mode_cours'] ?? ''));
$camera_obligatoire = isset($data['camera_obligatoire']) && $data['camera_obligatoire'] ? 1 : 0;
$suivi = isset($data['suivi']) && $data['suivi'] ? 1 : 0;
$description = htmlspecialchars(trim($data['description'] ?? ''));

if ($id_cours <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "errors" => ["Identifiant de cours manquant ou invalide."]]);
    exit;
}

$erreurs = [];

if ($matiere <= 0) {
    $erreurs[] = "La matière est obligatoire.";
}

if (empty($langues_selectionnees)) {
    $erreurs[] = "Vous devez sélectionner au moins une langue.";
}

if ($prix_heure <= 0) {
    $erreurs[] = "Le prix par heure doit être un nombre positif.";
}

if (empty($mode_cours)) {
    $erreurs[] = "Veuillez sélectionner un mode de cours.";
}

if (empty($description)) {
    $erreurs[] = "La description est obligatoire.";
} elseif (strlen($description) < 20) {
    $erreurs[] = "La description doit contenir au moins 20 caractères.";
}

if (!empty($erreurs)) {
    http_response_code(400);
    echo json_encode(["success" => false, "errors" => $erreurs]);
    exit;
}

try {
    $sql = "SELECT c.id_em, em.id_utilisateur FROM cours c JOIN enseignant_matiere em ON c.id_em = em.id_em WHERE c.id_cours = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id_cours]);
    $cours = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cours) {
        http_response_code(404);
        echo json_encode(["success" => false, "errors" => ["Cours introuvable."]]);
        exit;
    }

    $id_proprio = (int)$cours['id_utilisateur'];
    $em = (int)$cours['id_em'];

    if (!$is_admin && $id_proprio !== $id_session) {
        http_response_code(403);
        echo json_encode(["success" => false, "errors" => ["Accès refusé."]]);
        exit;
    }

    $db->beginTransaction();

    $sql = "UPDATE enseignant_matiere SET id_matiere = ? WHERE id_em = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$matiere, $em]);

    $sql = "UPDATE cours SET prix_heure = ?, mode_cours = ?, camera_obligatoire = ?, suivi = ?, description = ? WHERE id_cours = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$prix_heure, $mode_cours, $camera_obligatoire, $suivi, $description, $id_cours]);

    $stmtDel = $db->prepare("DELETE FROM enseignant_langue WHERE id_em = ?");
    $stmtDel->execute([$em]);

    $stmtIns = $db->prepare("INSERT INTO enseignant_langue (id_el, id_em) VALUES (?, ?)");
    foreach ($langues_selectionnees as $id_langue) {
        $stmtIns->execute([(int)$id_langue, $em]);
    }

    $db->commit();
    echo json_encode(["success" => true, "message" => "Le cours a été modifié avec succès !"]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "errors" => ["Erreur lors de la modification du cours : " . $e->getMessage()]]);
}
?>