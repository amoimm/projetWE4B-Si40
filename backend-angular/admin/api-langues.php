<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Récupère toutes les langues
        $query = $db->query("SELECT * FROM langue ORDER BY nom ASC");
        $langues = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($langues);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ajoute une nouvelle langue
        $data = json_decode(file_get_contents("php://input"), true);
        $nom_langue = isset($data['nom_langue']) ? trim($data['nom_langue']) : '';

        if (!empty($nom_langue)) {
            $stmt = $db->prepare("INSERT INTO langue (nom) VALUES (?)");
            $stmt->execute([$nom_langue]);

            echo json_encode(['success' => true, 'message' => 'Langue ajoutée']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'Nom de langue vide']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
