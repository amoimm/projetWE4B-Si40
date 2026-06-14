<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$idUser = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

if ($idUser === 0) {
    $headers = getallheaders();
    $idUser = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : (isset($headers['x-user-id']) ? (int)$headers['x-user-id'] : 0);
}

if ($idUser <= 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non autorisé. ID utilisateur manquant."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$idCours = isset($data['id_cours']) ? (int)$data['id_cours'] : 0;

if ($idCours <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Identifiant de cours invalide."]);
    exit;
}

try {
    $is_admin = false;

    $stmt = $db->prepare("
        SELECT c.id_em 
        FROM cours c
        JOIN enseignant_matiere em ON c.id_em = em.id_em
        WHERE c.id_cours = :id
    ");
    $stmt->execute(['id' => $idCours]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Cours introuvable."]);
        exit;
    }

    $stmtOwner = $db->prepare("SELECT id_utilisateur FROM enseignant_matiere WHERE id_em = ?");
    $stmtOwner->execute([$result['id_em']]);
    $owner = $stmtOwner->fetch(PDO::FETCH_ASSOC);

    if (!$is_admin && (int)$owner['id_utilisateur'] !== $idUser) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Vous n'avez pas l'autorisation de supprimer ce cours."]);
        exit;
    }

    $id_em = (int)$result['id_em'];

    $db->beginTransaction();

    $stmtDelMsg = $db->prepare("DELETE FROM message WHERE id_conv IN (SELECT id_conv FROM conversation WHERE id_cours = :id)");
    $stmtDelMsg->execute(['id' => $idCours]);

    $stmtDelConv = $db->prepare("DELETE FROM conversation WHERE id_cours = :id");
    $stmtDelConv->execute(['id' => $idCours]);

    $stmtDelRdv = $db->prepare("DELETE FROM rdv WHERE id_cours = :id");
    $stmtDelRdv->execute(['id' => $idCours]);

    $stmtGetAvis = $db->prepare("SELECT id_avis FROM avis_cours WHERE id_cours = :id");
    $stmtGetAvis->execute(['id' => $idCours]);
    $avisIds = $stmtGetAvis->fetchAll(PDO::FETCH_COLUMN);

    $stmtDelAvisCours = $db->prepare("DELETE FROM avis_cours WHERE id_cours = :id");
    $stmtDelAvisCours->execute(['id' => $idCours]);

    if (!empty($avisIds)) {
        $inQuery = implode(',', array_fill(0, count($avisIds), '?'));
        $stmtDelAvis = $db->prepare("DELETE FROM avis WHERE id_avis IN ($inQuery)");
        $stmtDelAvis->execute($avisIds);
    }

    $stmtDelCours = $db->prepare("DELETE FROM cours WHERE id_cours = :id");
    $stmtDelCours->execute(['id' => $idCours]);

    $stmtDelLang = $db->prepare("DELETE FROM enseignant_langue WHERE id_em = :id_em");
    $stmtDelLang->execute(['id_em' => $id_em]);

    $stmtDelEM = $db->prepare("DELETE FROM enseignant_matiere WHERE id_em = :id_em");
    $stmtDelEM->execute(['id_em' => $id_em]);

    $db->commit();

    echo json_encode(["success" => true, "message" => "Cours supprimé avec succès."]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Erreur de suppression SQL : " . $e->getMessage()]);
}
?>