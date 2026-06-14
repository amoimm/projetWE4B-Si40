<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        
        $idx = 1;
        foreach ($params as $param) {
            $list_query->bindValue($idx, $param);
            $idx++;
        }
        $list_query->bindValue($idx, (int)$per_page, PDO::PARAM_INT);
        $list_query->bindValue($idx + 1, (int)$offset, PDO::PARAM_INT);
        
        $list_query->execute();
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
            // Récupérer les infos de l'utilisateur avant modification
            $user_info = $db->prepare("SELECT nom, prenom, email, rang FROM utilisateurs WHERE id_utilisateurs = ?");
            $user_info->execute([$id_utilisateur]);
            $user_data = $user_info->fetch(PDO::FETCH_ASSOC);

            //Meis à jour du rang dans MySQL
            $stmt = $db->prepare("UPDATE utilisateurs SET rang = ? WHERE id_utilisateurs = ?");
            $stmt->execute([$nouveau_rang, $id_utilisateur]);

            //Logger dans MongoDB
            if ($user_data) {
                try {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $admin_id = $_SESSION['user_id'] ?? 'admin';
                    
                    $roles_map = [0 => 'Étudiant', 1 => 'Professeur', 2 => 'Admin'];
                    $ancien_role = $roles_map[(int)$user_data['rang']] ?? 'Inconnu';
                    $nouveau_role = $roles_map[$nouveau_rang] ?? 'Inconnu';

                    require_once __DIR__ . '/../bdd/config_mongodb.php';
                    $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
                    $activitylogsCollection->insertOne([
                        'level' => 'INFO',
                        'category' => 'ADMIN',
                        'action' => 'CHANGE_USER_ROLE',
                        'message' => "L'administrateur a changé le rôle de l'utilisateur " . $user_data['prenom'] . " " . $user_data['nom'] . " (Email: " . $user_data['email'] . ") de " . $ancien_role . " à " . $nouveau_role,
                        'id_user' => $admin_id,
                        'timestamp' => $dateFrance->format('d-m-Y H:i:s'),
                        'details' => [
                            'target_user_id' => $id_utilisateur,
                            'target_user_email' => $user_data['email'],
                            'ancien_rang' => (int)$user_data['rang'],
                            'nouveau_rang' => $nouveau_rang
                        ]
                    ]);
                } catch (Throwable $e_mongo) {
                    // Ignorer les erreurs de log
                }
            }

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
            // 1. Récupérer les infos de l'utilisateur avant suppression
            $user_info = $db->prepare("SELECT nom, prenom, email, rang FROM utilisateurs WHERE id_utilisateurs = ?");
            $user_info->execute([$id_utilisateur]);
            $user_data = $user_info->fetch(PDO::FETCH_ASSOC);

            // 2. Supprimer l'utilisateur de MySQL
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id_utilisateurs = ?");
            $stmt->execute([$id_utilisateur]);

            // 3. Logger dans MongoDB
            if ($user_data) {
                try {
                    $admin_id = isset($data['id_user']) ? $data['id_user'] : 'admin';
                    
                    $roles_map = [0 => 'Étudiant', 1 => 'Professeur', 2 => 'Admin'];
                    $role_text = $roles_map[(int)$user_data['rang']] ?? 'Inconnu';

                    require_once __DIR__ . '/../bdd/config_mongodb.php';
                    $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
                    $activitylogsCollection->insertOne([
                        'level' => 'WARNING',
                        'category' => 'ADMIN',
                        'action' => 'DELETE_USER',
                        'message' => "L'administrateur a supprimé l'utilisateur : " . $user_data['prenom'] . " " . $user_data['nom'] . " (Email: " . $user_data['email'] . ", Rôle: " . $role_text . ")",
                        'id_user' => $admin_id,
                        'timestamp' => $dateFrance->format('d-m-Y H:i:s'),
                        'details' => [
                            'deleted_user_id' => $id_utilisateur,
                            'deleted_user_email' => $user_data['email'],
                            'deleted_user_rang' => (int)$user_data['rang']
                        ]
                    ]);
                } catch (Throwable $e_mongo) {
                    // Ignorer les erreurs de log
                }
            }

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
