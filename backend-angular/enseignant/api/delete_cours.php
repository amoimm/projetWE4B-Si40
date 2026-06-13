<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once('../../bdd/config.php');
require_once('../../connect/Verif_connection.php');

$data = json_decode(file_get_contents("php://input"), true);
$idUser = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($data['user_id']) ? (int)$data['user_id'] : 0);

if ($idUser <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Session expirée ou ID utilisateur manquant."]);
    exit;
}
$idCours = isset($data['id_cours']) ? (int)$data['id_cours'] : 0;

if ($idCours <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Identifiant de cours invalide."]);
    exit;
}

try {
    // Vérifier si le cours appartient bien à l'enseignant connecté (ou s'il est admin)
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    
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

    // Récupérer l'ID enseignant lié à ce cours
    $stmtOwner = $db->prepare("SELECT id_utilisateur FROM enseignant_matiere WHERE id_em = ?");
    $stmtOwner->execute([$result['id_em']]);
    $owner = $stmtOwner->fetch(PDO::FETCH_ASSOC);

    if (!$is_admin && (int)$owner['id_utilisateur'] !== $idUser) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Vous n'avez pas l'autorisation de supprimer ce cours."]);
        exit;
    }

    $id_em = (int)$result['id_em'];

    // Démarrer une transaction
    $db->beginTransaction();

    // 1. Supprimer le cours
    $stmtDelCours = $db->prepare("DELETE FROM cours WHERE id_cours = :id");
    $stmtDelCours->execute(['id' => $idCours]);

    // 2. Supprimer les langues associées
    $stmtDelLang = $db->prepare("DELETE FROM enseignant_langue WHERE id_em = :id_em");
    $stmtDelLang->execute(['id_em' => $id_em]);

    // 3. Supprimer la liaison matière
    $stmtDelEM = $db->prepare("DELETE FROM enseignant_matiere WHERE id_em = :id_em");
    $stmtDelEM->execute(['id_em' => $id_em]);

    $db->commit();

    echo json_encode(["success" => true, "message" => "Cours supprimé avec succès."]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur suppression : " . $e->getMessage()]);
}
?>
