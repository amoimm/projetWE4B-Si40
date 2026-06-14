<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');
require_once('../../connect/Verif_connection.php');

$idUser = $_SESSION['user_id'] ?? ($_GET['user_id'] ?? null);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé"]);
    exit;
}

verifierEnseignantOuAdmin();

try {
    $stmt = $db->query('SELECT id_matiere, nom FROM matiere ORDER BY nom ASC');
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cast types
    foreach ($matieres as &$m) {
        $m['id_matiere'] = (int)$m['id_matiere'];
    }

    echo json_encode($matieres);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>
