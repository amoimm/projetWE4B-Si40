<?php
// api-logs.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer le contenu JSON envoyé par Angular
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!empty($data['message'])) {
        require_once 'bdd/config_mongodb.php';

        // Préparation du document à insérer dans MongoDB
        $document = [
            'level'     => $data['level'] ?? 'INFO',
            'message'   => $data['message'],
            'id_user'   => $data['id_user'],
            'timestamp' => new MongoDB\BSON\UTCDateTime(new DateTime()) // Date et heure actuelles
        ];

        try {
            // Insertion dans MongoDB
            $result = $logsCollection->insertOne($document);
            echo json_encode(["status" => "success", "id" => $result->getInsertedId()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Données incomplètes."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
}
?>