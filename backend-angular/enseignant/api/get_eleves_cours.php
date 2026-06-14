<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once('../../bdd/config.php');

$headers = getallheaders();
$id_enseignant = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : 0;
$id_cours = isset($_GET['id_cours']) ? (int)$_GET['id_cours'] : 0;

if ($id_enseignant <= 0 || $id_cours <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Données manquantes."]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT u.id_utilisateurs AS id_eleve,
               r.id_cours,
               u.prenom, u.nom,
               CONCAT(UPPER(LEFT(u.prenom,1)), UPPER(LEFT(u.nom,1))) AS initiales,
               c.id_conv,
               (SELECT contenu FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS dernier_message,
               (SELECT COUNT(*) FROM message WHERE id_conv = c.id_conv AND lu = 0 AND id_redacteur != :id_ens) AS nb_non_lus
        FROM rdv r
        JOIN utilisateurs u ON u.id_utilisateurs = r.id_eleve
        JOIN cours co ON co.id_cours = r.id_cours
        JOIN enseignant_matiere em ON em.id_em = co.id_em
        LEFT JOIN conversation c ON c.id_eleve = r.id_eleve AND c.id_cours = r.id_cours
        WHERE r.id_cours = :id_cours
            AND em.id_utilisateur = :id_ens
            AND r.est_valide = 1
        GROUP BY u.id_utilisateurs, c.id_conv
        ORDER BY u.nom
    ");
    $stmt->execute([':id_cours' => $id_cours, ':id_ens' => $id_enseignant]);
    
    $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Typage des données
    foreach ($eleves as &$e) {
        $e['id_eleve'] = (int)$e['id_eleve'];
        $e['id_cours'] = (int)$e['id_cours'];
        $e['id_conv'] = $e['id_conv'] ? (int)$e['id_conv'] : null;
        $e['nb_non_lus'] = (int)$e['nb_non_lus'];
    }

    echo json_encode($eleves);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}