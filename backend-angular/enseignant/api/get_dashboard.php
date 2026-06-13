<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once('../../bdd/config.php');
require_once('../../connect/Verif_connection.php');

$idUser = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);

if ($idUser <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Session expirée ou ID utilisateur manquant."]);
    exit;
}

// Récupérer le nom de l'utilisateur si la session est vide
$userNom = $_SESSION['user_nom'] ?? 'Enseignant';
if ($userNom === 'Enseignant') {
    $stmtUser = $db->prepare("SELECT prenom, nom FROM utilisateurs WHERE id_utilisateurs = ?");
    $stmtUser->execute([$idUser]);
    $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $userNom = $u['prenom'] . ' ' . $u['nom'];
    }
}

try {
    // 1. Stats : nombre de cours actifs et d'élèves uniques
    $req = $db->prepare("
        SELECT 
            COUNT(DISTINCT c.id_cours) AS nb_cours, 
            COUNT(DISTINCT r.id_eleve) AS nb_eleves 
        FROM cours c 
        JOIN enseignant_matiere em ON c.id_em = em.id_em 
        LEFT JOIN rdv r ON r.id_cours = c.id_cours 
        WHERE em.id_utilisateur = ?
    ");
    $req->execute([$idUser]);
    $stats = $req->fetch(PDO::FETCH_ASSOC);

    // 2. Stats messages non lus
    $req1 = $db->prepare("
        SELECT COUNT(*) AS nb_nouveau_messages 
        FROM message m 
        JOIN conversation c ON m.id_conv = c.id_conv 
        JOIN cours co ON c.id_cours = co.id_cours 
        JOIN enseignant_matiere em ON co.id_em = em.id_em 
        WHERE em.id_utilisateur = ? AND m.id_redacteur != ? AND m.lu = 0
    ");
    $req1->execute([$idUser, $idUser]);
    $messages = $req1->fetch(PDO::FETCH_ASSOC);

    // 3. Prochains RDVs validés
    $req3 = $db->prepare("
        SELECT 
            r.date_heure, 
            u.nom, 
            u.prenom, 
            m.nom AS matiere 
        FROM rdv r 
        JOIN cours c ON r.id_cours = c.id_cours 
        JOIN enseignant_matiere em ON c.id_em = em.id_em 
        JOIN utilisateurs u ON r.id_eleve = u.id_utilisateurs 
        JOIN matiere m ON em.id_matiere = m.id_matiere 
        WHERE em.id_utilisateur = ? 
          AND r.date_heure >= NOW() 
          AND r.est_valide = 1 
        ORDER BY r.date_heure ASC 
        LIMIT 5
    ");
    $req3->execute([$idUser]);
    $rdvs = $req3->fetchAll(PDO::FETCH_ASSOC);

    // 4. Messages récents non lus (liste)
    $req2 = $db->prepare("
        SELECT 
            m.id_conv, 
            c.id_cours, 
            m.id_redacteur, 
            u.prenom, 
            DATE_FORMAT(m.heure, '%H:%i') AS heure, 
            m.contenu
        FROM message m
        JOIN conversation c ON m.id_conv = c.id_conv
        JOIN cours co ON c.id_cours = co.id_cours
        JOIN enseignant_matiere em ON co.id_em = em.id_em
        JOIN utilisateurs u ON m.id_redacteur = u.id_utilisateurs
        WHERE em.id_utilisateur = ? AND m.id_redacteur != ? AND m.lu = 0
        ORDER BY m.heure DESC
        LIMIT 5
    ");
    $req2->execute([$idUser, $idUser]);
    $messages_new = $req2->fetchAll(PDO::FETCH_ASSOC);

    // 5. Demandes de rendez-vous en attente
    $req4 = $db->prepare("
        SELECT COUNT(*) AS nb_rdv_en_attente
        FROM rdv r
        JOIN cours co ON co.id_cours = r.id_cours
        JOIN enseignant_matiere em ON em.id_em = co.id_em
        WHERE em.id_utilisateur = ? AND r.est_valide = 0
    ");
    $req4->execute([$idUser]);
    $nb_rdv_en_attente = $req4->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "stats" => $stats,
        "messages" => $messages,
        "messages_new" => $messages_new,
        "rdvs" => $rdvs,
        "nb_rdv_en_attente" => $nb_rdv_en_attente,
        "user_nom" => $userNom
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>