<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours']) || !isset($data['id_eleve'])) {
    echo json_encode(["erreur" => "Données manquantes."]);
    exit;
}

$date_heure = $data['date_cours'] . ' ' . $data['heure_cours'] . ':00';
$duree_int = ($data['duree_cours'] === '30 min') ? 30 : 60;
$lieu = trim($data['lieu']);
$commentaire = "Langue : " . trim($data['langue_cours']);

try {
    $sql_rdv = "INSERT INTO rdv (id_cours, id_eleve, date_heure, duree, lieu, commentaire, est_valide) 
                VALUES (:id_cours, :id_eleve, :date_heure, :duree, :lieu, :commentaire, 0)";
    $stmt_rdv = $db->prepare($sql_rdv);
    $stmt_rdv->execute([
        'id_cours' => $data['id_cours'],
        'id_eleve' => $data['id_eleve'],
        'date_heure' => $date_heure,
        'duree' => $duree_int,
        'lieu' => $lieu,
        'commentaire' => $commentaire
    ]);

    //verif si une conv à été créée

    $sql_check = "SELECT id_conv FROM conversation WHERE id_cours = :id_cours AND id_eleve = :id_eleve";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute(['id_cours' => $data['id_cours'], 'id_eleve' => $data['id_eleve']]);
    
    if (!$stmt_check->fetch()) {
        // 3. Créer la conversation si elle n'existe pas
        $sql_conv = "INSERT INTO conversation (id_cours, id_eleve) VALUES (:id_cours, :id_eleve)";
        $stmt_conv = $db->prepare($sql_conv);
        $stmt_conv->execute(['id_cours' => $data['id_cours'], 'id_eleve' => $data['id_eleve']]);
    }

    echo json_encode(["succes" => true, "message" => "Rendez-vous demandé avec succès"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur.", "details" => $e->getMessage()]);
}