<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';

    if ($action === 'list') {
        // Récupère la liste des cours avec possibilité de recherche
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $query = "SELECT c.*, u.nom, u.prenom, m.nom as nom_matiere
                  FROM cours c
                  JOIN enseignant_matiere em ON c.id_em = em.id_em
                  JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
                  JOIN matiere m ON em.id_matiere = m.id_matiere";

        if (!empty($search)) {
            $search_param = "%$search%";
            $query .= " WHERE (m.nom LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ? OR c.description LIKE ?)";
            $stmt = $db->prepare($query . " ORDER BY c.id_cours DESC");
            $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
        } else {
            $stmt = $db->prepare($query . " ORDER BY c.id_cours DESC");
            $stmt->execute();
        }

        $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cours);

    } elseif ($action === 'supprimer') {
        // Supprime un cours
        $data = json_decode(file_get_contents("php://input"), true);
        $id_cours = isset($data['id_cours']) ? (int)$data['id_cours'] : 0;

        if ($id_cours > 0) {
            $stmt = $db->prepare("DELETE FROM cours WHERE id_cours = ?");
            $stmt->execute([$id_cours]);

            echo json_encode(['success' => true, 'message' => 'Cours supprimé']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID cours invalide']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
