<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Récupère toutes les matières
        $query = $db->query("SELECT * FROM matiere ORDER BY nom ASC");
        $matieres = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($matieres);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ajoute une nouvelle matière
        $data = json_decode(file_get_contents("php://input"), true);
        $nom_matiere = isset($data['nom_matiere']) ? trim($data['nom_matiere']) : '';

        if (!empty($nom_matiere)) {
            $stmt = $db->prepare("INSERT INTO matiere (nom) VALUES (?)");
            $stmt->execute([$nom_matiere]);

            // Logger l'action dans MongoDB
            try {
                $admin_id = isset($data['id_user']) ? $data['id_user'] : 'admin';

                require_once __DIR__ . '/../bdd/config_mongodb.php';
                $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
                $activitylogsCollection->insertOne([
                    'level' => 'INFO',
                    'category' => 'ADMIN',
                    'action' => 'ADD_MATIERE',
                    'message' => "L'administrateur a ajouté une matière ",
                    'id_user' => $admin_id,
                    'timestamp' => $dateFrance->format('d-m-Y H:i:s'),
                    'details' => [
                        'nom_matiere' => $nom_matiere
                    ]
                ]);
            } catch (Exception $e_mongo) {
                // Ignorer en cas d'erreur de log
            }

            echo json_encode(['success' => true, 'message' => 'Matière ajoutée']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'Nom de matière vide']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
