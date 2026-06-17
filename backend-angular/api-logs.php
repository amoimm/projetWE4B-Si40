<?php
// api-logs.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'bdd/config_mongodb.php'; // On le charge une bonne fois pour toutes
    $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));

    // =========================================================================
    // CAS 1 : C'est la candidature Prof (reçue via FormData)
    // =========================================================================
    if (isset($_POST['type_log']) && $_POST['type_log'] === 'CANDIDATURE_PROF') {

        // Attention : Ton Angular envoie 'subjects' et 'languages' !
        $matieres = isset($_POST['matieres']) ? json_decode($_POST['matieres'], true) : [];
        $langues = isset($_POST['langues']) ? json_decode($_POST['langues'], true) : [];

        $document = [
            'level'     => 'INFO',
            'message'   => $_POST['message'] ?? 'Devenir Prof: Soumission du profil enseignant.',
            'id_user'   => $_POST['id_user'] ?? null,
            'timestamp' => $dateFrance->format('d-m-Y H:i:s'),

            // Données spécifiques à la page (Context)
            'context'   => [
                'matieres'             => $matieres,
                'langues'            => $langues,
                'uploaded_files_names' => $_FILES['certifications']['name'] ?? [],
                'status'               => 'attente_validation'
            ]
        ];

        try {
            // Insertion spécifique dans la collection des PROFESSEURS
            $result = $devenirprofCollection->insertOne($document);
            echo json_encode(["status" => "success", "id" => $result->getInsertedId()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

    }
    // =========================================================================
    // CAS 2 : C'est le log classique de démarrage (reçu via JSON)
    // =========================================================================
    else {
        // Récupérer le contenu JSON envoyé par Angular
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if ($data && !empty($data['message'])) {
            $id_user = null;
            if (isset($data['id_user']) && $data['id_user'] !== '' && $data['id_user'] !== 0 && $data['id_user'] !== '0') {
                $id_user = (int)$data['id_user'];
            }
            $document = [
                'level'     => $data['level'] ?? 'INFO',
                'category'  => $data['category'] ?? 'GENERAL',
                'action'    => $data['action'] ?? 'ACTION',
                'message'   => $data['message'],
                'id_user'   => $id_user,
                'timestamp' => $dateFrance->format('d-m-Y H:i:s'),

                // details contient des objets spécifiques (ex: filtres, tarifs...)
                'details'   => $data['details'] ?? null
            ];
            try {
                // Insertion dans la collection des LOGS D'ACTIVITÉ
                $result = $activitylogsCollection->insertOne($document);
                echo json_encode(["status" => "success", "id" => $result->getInsertedId()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Données incomplètes. Attendu: JSON avec clé 'message'."
            ]);
        }
    }

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
}
?>