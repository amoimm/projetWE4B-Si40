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
require_once __DIR__ . '/../bdd/config_mongodb.php';

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

        try{
            require_once __DIR__ . '/../vendor/autoload.php';
            $certsCollection = $mongoClient->selectCollection('coursconnect_nosql', 'certif_prof');
            // Extraction des IDs utilisateurs de la page actuelle
            $userIds = array_map(function($u) { return (int)$u['id_utilisateurs']; }, $utilisateurs);
            if (!empty($userIds)) {
                $cursor = $certsCollection->find(['id_utilisateur' => ['$in' => $userIds]]);

                // Indexation par ID utilisateur
                $certsByUser = [];
                foreach ($cursor as $doc) {
                    $uId = (int)$doc['id_utilisateur'];

                    // On ne récupère pas le base64 ici pour éviter de charger la mémoire inutilement, seulement le nom
                    $fichiers = [];
                    if (isset($doc['certificats'])) {
                        foreach ($doc['certificats'] as $c) {
                            $fichiers[] = [
                                'nom_fichier' => $c['nom_fichier']
                            ];
                        }
                    }
                    $certsByUser[$uId] = [
                        'statut_verification' => $doc['statut_verification'] ?? 'en_attente',
                        'fichiers' => $fichiers
                    ];
                }
                // Liaison avec l'objet utilisateur renvoyé à Angular
                foreach ($utilisateurs as &$u) {
                    $uId = (int)$u['id_utilisateurs'];
                    $u['certifs'] = isset($certsByUser[$uId]) ? $certsByUser[$uId] : null;
                }
                unset($u);
            }

        } catch (Exception $e_mongo) {
            // En cas d'erreur avec MongoDB, on s'assure que la clé 'certifs' existe pour éviter des erreurs Angular
            foreach ($utilisateurs as &$u) {
                $u['certifs'] = null;
            }
            unset($u);
        }
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
        // Supprime un utilisateur et toutes ses données associées
        $data = json_decode(file_get_contents("php://input"), true);
        $id_utilisateur = isset($data['id_utilisateur']) ? (int)$data['id_utilisateur'] : 0;
        if ($id_utilisateur > 0) {
            // 1. Récupérer les infos de l'utilisateur avant suppression (pour les logs)
            $user_info = $db->prepare("SELECT nom, prenom, email, rang FROM utilisateurs WHERE id_utilisateurs = ?");
            $user_info->execute([$id_utilisateur]);
            $user_data = $user_info->fetch(PDO::FETCH_ASSOC);

            // A. Suppression des messages (rédigés par l'utilisateur ou faisant partie de ses cours)
            $stmt_msg = $db->prepare("DELETE FROM message WHERE id_redacteur = :id OR id_conv IN (SELECT id_conv FROM conversation WHERE id_eleve = :id OR id_cours IN (SELECT id_cours FROM cours WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id)))");
            $stmt_msg->execute(['id' => $id_utilisateur]);

            // B. Suppression des conversations (où il est élève ou liées à ses cours)
            $stmt_conv = $db->prepare("DELETE FROM conversation WHERE id_eleve = :id OR id_cours IN (SELECT id_cours FROM cours WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id))");
            $stmt_conv->execute(['id' => $id_utilisateur]);

            // C. Suppression des rendez-vous (RDV)
            $stmt_rdv = $db->prepare("DELETE FROM rdv WHERE id_eleve = :id OR id_cours IN (SELECT id_cours FROM cours WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id))");
            $stmt_rdv->execute(['id' => $id_utilisateur]);

            // D. Suppression des avis sur les cours
            $stmt_avis_cours = $db->prepare("DELETE FROM avis_cours WHERE id_cours IN (SELECT id_cours FROM cours WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id))");
            $stmt_avis_cours->execute(['id' => $id_utilisateur]);

            // E. Suppression des avis généraux rédigés
            $stmt_avis = $db->prepare("DELETE FROM avis WHERE id_utilisateur = :id");
            $stmt_avis->execute(['id' => $id_utilisateur]);

            // F. Suppression des cours liés à ses matières enseignées
            $stmt_cours = $db->prepare("DELETE FROM cours WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id)");
            $stmt_cours->execute(['id' => $id_utilisateur]);

            // G. Suppression des langues enseignées
            $stmt_langues = $db->prepare("DELETE FROM enseignant_langue WHERE id_em IN (SELECT id_em FROM enseignant_matiere WHERE id_utilisateur = :id)");
            $stmt_langues->execute(['id' => $id_utilisateur]);

            // H. Suppression des matières enseignées
            $stmt_matieres = $db->prepare("DELETE FROM enseignant_matiere WHERE id_utilisateur = :id");
            $stmt_matieres->execute(['id' => $id_utilisateur]);

            // I. Suppression de l'utilisateur
            $stmt_user = $db->prepare("DELETE FROM utilisateurs WHERE id_utilisateurs = :id");
            $stmt_user->execute(['id' => $id_utilisateur]);

            // 3. Suppression des certificats PDF associés dans MongoDB
            try {
                $certifsCollection->deleteOne(['id_utilisateur' => $id_utilisateur]);
            } catch (Throwable $e_mongo) {
                // Ignorer si MongoDB n'est pas disponible ou s'il n'y a pas de certificats
            }
            // 4. Logger la suppression dans MongoDB
            if ($user_data) {
                try {
                    $admin_id = isset($data['id_user']) ? $data['id_user'] : 'admin';
                    $roles_map = [0 => 'Étudiant', 1 => 'Professeur', 2 => 'Admin'];
                    $role_text = $roles_map[(int)$user_data['rang']] ?? 'Inconnu';
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
            echo json_encode(['success' => true, 'message' => 'Utilisateur et données associées supprimés avec succès']);
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID utilisateur invalide']);
        }
    }elseif ($action === 'voir_certificat') {
        // Récupère les données base64 d'un certificat spécifique
        $id_utilisateur = isset($_GET['id_utilisateur']) ? (int)$_GET['id_utilisateur'] : 0;
        $nom_fichier = isset($_GET['nom_fichier']) ? trim($_GET['nom_fichier']) : '';
        if ($id_utilisateur > 0 && !empty($nom_fichier)) {
            try {
                require_once __DIR__ . '/../vendor/autoload.php';
                $certsCollection = $mongoClient->selectCollection('coursconnect_nosql', 'certif_prof');
                // Recherche du document pour cet utilisateur contenant ce fichier
                $doc = $certsCollection->findOne([
                    'id_utilisateur' => $id_utilisateur,
                    'certificats.nom_fichier' => $nom_fichier
                ]);
                if ($doc) {
                    $foundCert = null;
                    foreach ($doc['certificats'] as $c) {
                        if ($c['nom_fichier'] === $nom_fichier) {
                            $foundCert = $c;
                            break;
                        }
                    }
                    if ($foundCert) {
                        echo json_encode([
                            'success' => true,
                            'nom_fichier' => $foundCert['nom_fichier'],
                            'type' => $foundCert['type'] ?? 'application/pdf',
                            'donnees_base64' => $foundCert['donnees_base64']
                        ]);
                        exit();
                    }
                }
                http_response_code(404);
                echo json_encode(['erreur' => 'Certificat introuvable']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['erreur' => 'Erreur serveur : ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['erreur' => 'Paramètres invalides']);
        }
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
