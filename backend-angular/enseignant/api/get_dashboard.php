<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
session_start();
require_once('../src/models/config.php');

// Vérification sécurité (tu peux réutiliser ta logique de Verif_connection.php)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé"]);
    exit;
}

$idUser = $_SESSION['user_id'];

// 1. Stats
$req = $db->prepare("SELECT COUNT(DISTINCT c.id_cours) AS nb_cours, COUNT(DISTINCT r.id_eleve) AS nb_eleves FROM cours c JOIN enseignant_matiere em ON c.id_em = em.id_em LEFT JOIN rdv r ON r.id_cours = c.id_cours WHERE em.id_utilisateur = ?");
$req->execute([$idUser]);
$stats = $req->fetch(PDO::FETCH_ASSOC);

// 2. Messages
$req1 = $db->prepare("SELECT COUNT(*) AS nb_nouveau_messages FROM message m JOIN conversation c ON m.id_conv = c.id_conv JOIN cours co ON c.id_cours = co.id_cours JOIN enseignant_matiere em ON co.id_em = em.id_em WHERE em.id_utilisateur = ? AND m.id_redacteur != ? AND m.lu = 0");
$req1->execute([$idUser, $idUser]);
$messages = $req1->fetch(PDO::FETCH_ASSOC);

// 3. RDVs
$req3 = $db->prepare("SELECT r.date_heure, u.nom, u.prenom, m.nom AS matiere FROM rdv r JOIN cours c ON r.id_cours = c.id_cours JOIN enseignant_matiere em ON c.id_em = em.id_em JOIN utilisateurs u ON r.id_eleve = u.id_utilisateurs JOIN matiere m ON em.id_matiere = m.id_matiere WHERE em.id_utilisateur = ? AND r.date_heure >= NOW() AND r.est_valide = 1 ORDER BY r.date_heure ASC LIMIT 5");
$req3->execute([$idUser]);
$rdvs = $req3->fetchAll(PDO::FETCH_ASSOC);

// Réponse JSON unique
echo json_encode([
    "stats" => $stats,
    "messages" => $messages,
    "rdvs" => $rdvs
]);
?>