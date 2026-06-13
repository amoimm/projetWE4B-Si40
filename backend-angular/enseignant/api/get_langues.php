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

$idUser = $_SESSION['user_id'] ?? ($_GET['user_id'] ?? null);

if (!$idUser) {
    http_response_code(401);
    echo json_encode(["error" => "Session non identifiee."]);
    exit;
}

verifierEnseignantOuAdmin();

try {
    $stmt = $db->query('SELECT id_langue, nom FROM langue ORDER BY nom ASC');
    $langues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast types
    foreach ($langues as &$l) {
        $l['id_langue'] = (int)$l['id_langue'];
    }

    echo json_encode($langues);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>
