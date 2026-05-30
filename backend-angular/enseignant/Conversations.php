<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../../src/models/config.php');
require_once('Verif_connection.php');

$id_enseignant = $_SESSION['user_id'];

if (isset($_GET['id_cours'])) {
    $id_cours = (int)$_GET['id_cours'];
    $req = $db->prepare("SELECT DISTINCT u.id_utilisateurs, u.nom, u.prenom FROM utilisateurs u JOIN rdv r ON r.id_eleve = u.id_utilisateurs WHERE r.id_cours = ?");
    $req->execute([$id_cours]);
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

$req1 = $db->prepare("
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
$req1->execute([':id_ens' => $id_enseignant, ':id_ens2' => $id_enseignant]);
$conversations = $req1->fetchAll(PDO::FETCH_ASSOC);

$conversations_active = [];
foreach ($conversations as $conv) {
    $conversations_active[$conv['cours']][] = $conv;
}

$req2 = $db->prepare("
    SELECT co.id_cours, co.description
    FROM cours co
    JOIN enseignant_matiere em ON em.id_em = co.id_em
    WHERE em.id_utilisateur = ?
    ORDER BY co.description
");
$req2->execute([$id_enseignant]);
$mes_cours = $req2->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'conversations_active' => $conversations_active,
    'mes_cours' => $mes_cours
]);