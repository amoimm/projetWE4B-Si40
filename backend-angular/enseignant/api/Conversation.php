<?php
header('Content-Type: application/json');
session_start();
require_once('../src/models/config.php');
require_once('Verif_connection.php');

$id_enseignant = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT c.id_conv, u.prenom, u.nom, co.description AS cours,
           (SELECT contenu FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS dernier_message,
           (SELECT heure FROM message WHERE id_conv = c.id_conv ORDER BY heure DESC LIMIT 1) AS date_message
    FROM conversation c
    JOIN utilisateurs u ON u.id_utilisateurs = c.id_eleve
    JOIN cours co ON co.id_cours = c.id_cours
    JOIN enseignant_matiere em ON em.id_em = co.id_em
    WHERE em.id_utilisateur = :id_ens
    ORDER BY date_message DESC
");
$stmt->execute(['id_ens' => $id_enseignant]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));