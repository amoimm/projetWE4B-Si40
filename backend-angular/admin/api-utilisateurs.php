<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';

    if ($action === 'list') {
        // Récupère la liste des utilisateurs avec filtres et pagination
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $rang_filter = isset($_GET['rang']) ? (int)$_GET['rang'] : -1;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        // Construire la clause WHERE
        $where_clauses = [];
        $params = [];

        if (!empty($search)) {
            $where_clauses[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
            $search_param = "%$search%";
            array_push($params, $search_param, $search_param, $search_param);
        }

        if ($rang_filter !== -1) {
            $where_clauses[] = "rang = ?";
            $params[] = $rang_filter;
        }

        $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

        // Récupère le nombre total
        $count_query = $db->prepare("SELECT COUNT(*) FROM utilisateurs $where_sql");
        $count_query->execute($params);
        $total = (int)$count_query->fetchColumn();

        // Récupère les utilisateurs
        $list_query = $db->prepare("SELECT * FROM utilisateurs $where_sql ORDER BY id_utilisateurs DESC LIMIT ? OFFSET ?");
        $params[] = $per_page;
        $params[] = $offset;
        $list_query->execute($params);
        $utilisateurs = $list_query->fetchAll(PDO::FETCH_ASSOC);

        $total_pages = ceil($total / $per_page);

        echo json_encode([
            'users' => $utilisateurs,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page
        ]);

    } elseif ($action === 'recents') {
        // Récupère les 5 utilisateurs récents
        $query = $db->query("SELECT id_utilisateurs, nom, prenom, email, rang FROM utilisateurs ORDER BY id_utilisateurs DESC LIMIT 5");
        $recents = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($recents);

    } elseif ($action === 'modifier_rang') {
        // Modifie le rang d'un utilisateur
        $data = json_decode(file_get_contents("php://input"), true);
        $id_utilisateur = isset($data['id_utilisateur']) ? (int)$data['id_utilisateur'] : 0;
        $nouveau_rang = isset($data['rang']) ? (int)$data['rang'] : 0;

        if ($id_utilisateur > 0) {
            $stmt = $db->prepare("UPDATE utilisateurs SET rang = ? WHERE id_utilisateurs = ?");
            $stmt->execute([$nouveau_rang, $id_utilisateur]);

            echo json_encode(['success' => true, 'message' => 'Rang mis à jour']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID utilisateur invalide']);
        }

    } elseif ($action === 'supprimer') {
        // Supprime un utilisateur
        $data = json_decode(file_get_contents("php://input"), true);
        $id_utilisateur = isset($data['id_utilisateur']) ? (int)$data['id_utilisateur'] : 0;

        if ($id_utilisateur > 0) {
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id_utilisateurs = ?");
            $stmt->execute([$id_utilisateur]);

            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID utilisateur invalide']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
