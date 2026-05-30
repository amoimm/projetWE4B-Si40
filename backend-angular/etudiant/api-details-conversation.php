<?php
// api-details-conversation.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../bdd/config.php';

$id_cours = isset($_GET['id_cours']) ? (int)$_GET['id_cours'] : 0;
$id_eleve = isset($_GET['id_eleve']) ? (int)$_GET['id_eleve'] : 1; // ID de test

if ($id_cours === 0) {
    echo json_encode(["erreur" => "ID du cours manquant."]);
    exit;
}

try {
    // 1. Récupérer les infos du cours et de la conversation
    $sql_info = "SELECT c.description, conv.id_conv, c.id_em
                 FROM cours c 
                 LEFT JOIN conversation conv ON c.id_cours = conv.id_cours AND conv.id_eleve = :id_eleve
                 WHERE c.id_cours = :id_cours";
    $stmt_info = $db->prepare($sql_info);
    $stmt_info->execute(['id_eleve' => $id_eleve, 'id_cours' => $id_cours]);
    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    $messages = [];
    $id_conv = $info ? $info['id_conv'] : null;

    // 2. Si une conversation existe, on récupère les messages
    if ($id_conv) {
        // Mise à jour des accusés de lecture
        $sql_lu = "UPDATE message SET lu = 1 WHERE id_conv = :id_conv AND id_redacteur != :mon_id AND lu = 0";
        $stmt_lu = $db->prepare($sql_lu);
        $stmt_lu->execute(['id_conv' => $id_conv, 'mon_id' => $id_eleve]);

        // Récupération de l'historique
        $sql_msg = "SELECT id_redacteur, contenu, heure FROM message WHERE id_conv = :id_conv ORDER BY heure ASC";
        $stmt_msg = $db->prepare($sql_msg);
        $stmt_msg->execute(['id_conv' => $id_conv]);
        $messages = $stmt_msg->fetchAll(PDO::FETCH_ASSOC);
    }

    $langues_prof = [];
    if ($info && $info['id_em']) {
        $sql_langues = "SELECT lg.id_langue, lg.nom FROM enseignant_langue el JOIN langue lg ON el.id_el = lg.id_langue WHERE el.id_em = :id_em";
        $stmt_langues = $db->prepare($sql_langues);
        $stmt_langues->execute(['id_em' => $info['id_em']]);
        $langues_prof = $stmt_langues->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NOUVEAU : Récupération des rendez-vous futurs ---
    $date_actuelle = date("Y-m-d H:i:s");
    $sql_rdv = "SELECT date_heure, est_valide, id_rdv, lieu FROM rdv WHERE id_cours = :id_cours AND date_heure > :date_now ORDER BY date_heure ASC";
    $stmt_rdv = $db->prepare($sql_rdv);
    $stmt_rdv->execute(['id_cours' => $id_cours, 'date_now' => $date_actuelle]);
    $rdvs = $stmt_rdv->fetchAll(PDO::FETCH_ASSOC);

    // On renvoie TOUT à Angular
    echo json_encode([
        "info_cours" => $info,
        "messages" => $messages,
        "langues_prof" => $langues_prof,
        "rdvs" => $rdvs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur", "details" => $e->getMessage()]);
}