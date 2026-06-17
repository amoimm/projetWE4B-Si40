<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    // Évite le conflit : On garde une référence à la connexion MySQL ($db) avant d'inclure MongoDB
    $mysql_db = $db;

    // Récupère les statistiques globales (SQL)
    $stats_users = $mysql_db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $stats_cours = $mysql_db->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    $stats_messages = $mysql_db->query("SELECT COUNT(*) FROM message")->fetchColumn();

    $top_matieres = [];
    $activite_jours = [];
    $user_connected = 0;
    $mongo_available = false;

    try {
        require_once __DIR__ . '/../bdd/config_mongodb.php';

        $thirtyMinutesAgo = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $thirtyMinutesAgo->modify('-30 minutes');
        $hexTime = dechex($thirtyMinutesAgo->getTimestamp()) . str_repeat('0', 16);
        $objectIdMin = new MongoDB\BSON\ObjectId($hexTime);
        $pipeline_connected = [
            ['$match' => [
                '_id' => ['$gte' => $objectIdMin], // Actif dans les 30 dernières minutes
                'id_user' => ['$exists' => true, '$ne' => null, '$nin' => [0, '0', '']]
            ]],
            ['$sort' => ['_id' => -1]],
            ['$group' => [
                '_id' => '$id_user',
                'latest_action' => ['$first' => '$action']
            ]],
            ['$match' => [
                'latest_action' => ['$ne' => 'LOGOUT']
            ]],
            ['$count' => 'count']
        ];

        $cursor_connected = $activitylogsCollection->aggregate($pipeline_connected)->toArray();
        $user_connected = isset($cursor_connected[0]['count']) ? (int)$cursor_connected[0]['count'] : 0;

        // Charger toutes les matières de MySQL pour faire le mapping id -> nom
        $matieres_sql = $mysql_db->query("SELECT id_matiere, nom FROM matiere")->fetchAll(PDO::FETCH_KEY_PAIR);

        // 1. Top 5 des matières recherchées (MongoDB)
        $pipeline_matieres = [
            ['$match' => [
                'category' => 'STUDENT_SEARCH',
                'details.matiere' => ['$ne' => '']
            ]],
            ['$group' => [
                '_id' => '$details.matiere',
                'count' => ['$sum' => 1]
            ]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 5]
        ];
        $cursor_matieres = $activitylogsCollection->aggregate($pipeline_matieres);
        foreach ($cursor_matieres as $doc) {
            $id_matiere = $doc['_id'];
            $nom_matiere = isset($matieres_sql[$id_matiere]) ? $matieres_sql[$id_matiere] : 'Autre/Inconnue';
            $top_matieres[] = [
                'matiere' => $nom_matiere,
                'count' => $doc['count']
            ];
        }

        // 2. Activité des 7 derniers jours
        $pipeline_activite = [
            ['$project' => [
                'day' => ['$substr' => ['$timestamp', 0, 10]]
            ]],
            ['$group' => [
                '_id' => '$day',
                'count' => ['$sum' => 1]
            ]],
            ['$sort' => ['_id' => -1]],
            ['$limit' => 7]
        ];
        $cursor_activite = $activitylogsCollection->aggregate($pipeline_activite);
        foreach ($cursor_activite as $doc) {
            if ($doc['_id']) {
                $activite_jours[] = [
                    'date' => $doc['_id'],
                    'count' => $doc['count']
                ];
            }
        }

        // Tri chronologique des jours pour la courbe
        usort($activite_jours, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $mongo_available = true;
    } catch (Exception $mongo_ex) {
        // En cas d'erreur avec MongoDB, on continue avec les données SQL uniquement
    }

    echo json_encode([
        'users' => (int)$stats_users,
        'users_connected' => $user_connected,
        'cours' => (int)$stats_cours,
        'messages' => (int)$stats_messages,
        'mongo_available' => $mongo_available,
        'top_matieres' => $top_matieres,
        'activite_jours' => $activite_jours
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>
