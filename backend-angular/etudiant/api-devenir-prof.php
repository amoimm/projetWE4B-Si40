<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
require_once __DIR__ . '/../bdd/config_mongodb.php';
require_once __DIR__ . '/../vendor/autoload.php';

$id_utilisateur =  $_POST['id_utilisateur'] ?? null;
$matieres_choisies = isset($_POST['matieres']) ? json_decode($_POST['matieres'], true) : [];
$langues_choisies = isset($_POST['langues']) ? json_decode($_POST['langues'], true) : [];

if (!$id_utilisateur || empty($matieres_choisies) || empty($langues_choisies)) {
    echo json_encode(["succes" => false, "message" => "Données manquantes."]);
    exit;
}

$certificatsMongo = [];
if (isset($_FILES['certificats'])) {
    $totalFiles = count($_FILES['certificats']['name']);

    for ($i = 0; $i < $totalFiles; $i++) {
        $tmpFilePath = $_FILES['certificats']['tmp_name'][$i];
        $fileType = $_FILES['certificats']['type'][$i];

        if ($tmpFilePath != "" && $fileType === "application/pdf") {
            $fileData = base64_encode(file_get_contents($tmpFilePath));
            $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $certificatsMongo[] = [
                'nom_fichier' => $_FILES['certificats']['name'][$i],
                'type' => 'application/pdf',
                'donnees_base64' => $fileData,
                'date_ajout' => $dateFrance->format('d-m-Y H:i:s')
            ];
        }
    }
}

try {
    // ==========================================
    // TRANSACTION SQL (Matières, Langues, Rôle)
    // ==========================================
    $db->beginTransaction();

    $stmt_role = $db->prepare("UPDATE utilisateurs SET rang = 1 WHERE id_utilisateurs = :id_user");
    $stmt_role->execute(['id_user' => $id_utilisateur]);

    $db->commit();

    // ==========================================
    // INSERTION MONGODB (Les PDF)
    // ==========================================
    if (!empty($certificatsMongo)) {

        $collection = $mongoClient->selectCollection('coursconnect_nosql', 'certif_prof');

        $collection->insertOne([
            'id_utilisateur' => (int)$id_utilisateur,
            'statut_verification' => 'en_attente',
            'certificats' => $certificatsMongo
        ]);
    }

    echo json_encode(["succes" => true, "message" => "Félicitations, vous êtes professeur et vos PDF ont été sauvegardés !"]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["succes" => false, "message" => "Erreur critique : " . $e->getMessage()]);
}