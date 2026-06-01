<?php
header('Content-Type: application/json');
session_start();
require_once('../src/models/config.php');
require_once('Verif_connection.php');

$id_conv = (int) ($_GET['id_conv'] ?? 0);
$id_utilisateur = (int) $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT m.contenu, m.heure, m.id_redacteur, u.prenom, u.nom
    FROM message m
    JOIN utilisateurs u ON u.id_utilisateurs = m.id_redacteur
    WHERE m.id_conv = ?
    ORDER BY m.heure ASC
");
$stmt->execute([$id_conv]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));