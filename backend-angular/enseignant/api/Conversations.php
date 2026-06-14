<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$headers = getallheaders();
$id_enseignant = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : 0;

if ($id_enseignant <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Identifiant utilisateur manquant."]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT c.id_conv, c.id_eleve, c.id_cours,
               u.prenom, u.nom,
               co.description AS cours,
               (SELECT contenu FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS dernier_message,
               (SELECT heure FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS date_message,
               (SELECT COUNT(*) FROM message WHERE id_conv = c.id_conv AND lu = 0 AND id_redacteur != :id_ens) AS nb_non_lus
        FROM conversation c
        JOIN utilisateurs u ON u.id_utilisateurs = c.id_eleve
        JOIN cours co ON co.id_cours = c.id_cours
        JOIN enseignant_matiere em ON em.id_em = co.id_em
        WHERE em.id_utilisateur = :id_ens2
        AND NOT EXISTS (
            SELECT 1 FROM rdv r
            WHERE r.id_cours = c.id_cours
            AND r.id_eleve = c.id_eleve
            AND r.est_valide = 1
        )
        ORDER BY co.description, date_message DESC
    ");
    $stmt->execute([':id_ens' => $id_enseignant, ':id_ens2' => $id_enseignant]);
    
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Typage des données
    foreach ($conversations as &$conv) {
        $conv['id_conv'] = (int)$conv['id_conv'];
        $conv['id_eleve'] = (int)$conv['id_eleve'];
        $conv['id_cours'] = (int)$conv['id_cours'];
        $conv['nb_non_lus'] = (int)$conv['nb_non_lus'];
    }

    echo json_encode($conversations);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>