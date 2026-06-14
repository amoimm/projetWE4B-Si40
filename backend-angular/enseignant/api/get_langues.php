<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$idUser = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

try {
    $stmt = $db->query('SELECT id_langue, nom FROM langue ORDER BY nom ASC');
    $langues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($langues as &$l) {
        $l['id_langue'] = (int)$l['id_langue'];
    }

    echo json_encode($langues);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>