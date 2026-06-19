<?php
// 1. Autorisation CORS complète, incluant X-User-Id
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$idUser = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

if ($idUser === 0) {
    $headers = getallheaders();
    $idUser = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : (isset($headers['x-user-id']) ? (int)$headers['x-user-id'] : 0);
}

if ($idUser <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. ID utilisateur manquant dans les entêtes."]);
    exit;
}

$userNom = 'Enseignant';
$stmtUser = $db->prepare("SELECT prenom, nom FROM utilisateurs WHERE id_utilisateurs = ?");
$stmtUser->execute([$idUser]);
$u = $stmtUser->fetch(PDO::FETCH_ASSOC);
if ($u) {
    $userNom = $u['prenom'] . ' ' . $u['nom'];
}

try {
    $req = $db->prepare("
        SELECT 
            COUNT(DISTINCT c.id_cours) AS nb_cours, 
            COUNT(DISTINCT CASE 
                WHEN r.est_valide = 1 AND r.date_heure < NOW() 
                THEN r.id_rdv 
                ELSE NULL 
            END) AS nb_cours_effectues,
            COUNT(DISTINCT CASE 
                WHEN r.est_valide = 1 AND r.date_heure >= NOW() 
                THEN r.id_rdv 
                ELSE NULL 
            END) AS nb_cours_a_venir
        FROM cours c 
        JOIN enseignant_matiere em ON c.id_em = em.id_em 
        LEFT JOIN rdv r ON r.id_cours = c.id_cours 
        WHERE em.id_utilisateur = ?
    ");
    $req->execute([$idUser]);
    $stats = $req->fetch(PDO::FETCH_ASSOC);

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