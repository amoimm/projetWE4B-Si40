<?php
session_start();
require_once('../config.php');

$idUser = $_SESSION['user_id'];

$sql = "SELECT COUNT(DISTINCT c.id_cours) AS nb_cours, COUNT(DISTINCT r.id_eleve) AS nb_eleves
        FROM cours c
        JOIN enseignant_matiere em ON c.id_em = em.id_em
        LEFT JOIN rdv r ON r.id_cours = c.id_cours
        WHERE em.id_utilisateur = ?";
$req = $db->prepare($sql);
$req->execute([$idUser]);
$stats = $req->fetch(PDO::FETCH_ASSOC);

$sql_messages = "SELECT m.id_conv, c.id_cours, m.id_redacteur, u.prenom, m.heure, m.contenu
                 FROM message m
                 JOIN conversation c ON m.id_conv = c.id_conv
                 JOIN utilisateurs u ON m.id_redacteur = u.id_utilisateurs
                 JOIN cours co ON c.id_cours = co.id_cours
                 JOIN enseignant_matiere em ON co.id_em = em.id_em
                 WHERE em.id_utilisateur = ? AND m.id_redacteur != ? AND m.lu = 0
                 ORDER BY m.heure DESC LIMIT 5";
$req_msg = $db->prepare($sql_messages);
$req_msg->execute([$idUser, $idUser]);
$messages_new = $req_msg->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'stats' => $stats,
    'messages_recents' => $messages_new
]);