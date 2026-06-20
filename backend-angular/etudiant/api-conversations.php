<?php
// api-conversations.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../bdd/config.php';

$id_eleve = isset($_GET['id_eleve']) ? (int)$_GET['id_eleve'] : 1;

try {
    $sql = "SELECT 
                conversation.id_conv,
                c.description AS description,
                mat.nom AS nom_matiere,
                u.nom AS nom_prof,
                u.prenom AS prenom_prof,
                conversation.id_cours,
                MAX(msg.heure) AS date_dernier_message,
                SUM(CASE WHEN msg.lu = 0 AND msg.id_redacteur != :id_eleve THEN 1 ELSE 0 END) AS nb_non_lus
            FROM conversation
            LEFT JOIN cours c ON conversation.id_cours = c.id_cours
            LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em 
            LEFT JOIN matiere mat ON em.id_matiere = mat.id_matiere
            LEFT JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
            LEFT JOIN message msg ON conversation.id_conv = msg.id_conv
            WHERE conversation.id_eleve = :id_eleve
            GROUP BY conversation.id_conv
            ORDER BY date_dernier_message DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_eleve' => $id_eleve]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($conversations as &$conv) {
        $conv['nb_non_lus'] = (int)$conv['nb_non_lus'];
    }

    echo json_encode($conversations);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur lors de la récupération des conversations", "details" => $e->getMessage()]);
}