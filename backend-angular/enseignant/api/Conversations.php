<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once('../../bdd/config.php');
require_once('../../connect/Verif_connection.php');

$id_enseignant = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);

if ($id_enseignant <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Session expirée ou ID utilisateur manquant."]);
    exit;
}

try {
    // Sélectionner les conversations de l'enseignant
    $stmt = $db->prepare("
        SELECT 
            c.id_conv AS id,
            c.id_conv AS id_conv,
            u.prenom, 
            u.nom, 
            co.description AS cours,
            (SELECT contenu FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS dernier_message,
            (SELECT heure FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS date_message,
            (SELECT COUNT(*) FROM message WHERE id_conv = c.id_conv AND lu = 0 AND id_redacteur != :id_ens) AS nb_non_lus
        FROM conversation c
        JOIN utilisateurs u ON u.id_utilisateurs = c.id_eleve
        JOIN cours co ON co.id_cours = c.id_cours
        JOIN enseignant_matiere em ON em.id_em = co.id_em
        WHERE em.id_utilisateur = :id_ens2
        ORDER BY date_message DESC
    ");
    $stmt->execute([
        'id_ens' => $id_enseignant,
        'id_ens2' => $id_enseignant
    ]);
    
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cast values
    foreach ($conversations as &$conv) {
        $conv['id'] = (int)$conv['id'];
        $conv['id_conv'] = (int)$conv['id_conv'];
        $conv['nb_non_lus'] = (int)$conv['nb_non_lus'];
    }

    echo json_encode($conversations);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>
