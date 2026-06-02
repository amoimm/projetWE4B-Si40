<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
// ATTENTION : On ne force plus le Content-Type en JSON ici pour les requêtes entrantes car c'est du multipart/form-data
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
// Inclure l'autoloader de Composer pour MongoDB (ajuste le chemin selon ton projet)
require_once __DIR__ . '/../vendor/autoload.php';

// 1. Récupération des données POST (Texte)
$id_utilisateur = $_POST['id_utilisateur'] ?? null;
$matieres_choisies = isset($_POST['matieres']) ? json_decode($_POST['matieres'], true) : [];
$langues_choisies = isset($_POST['langues']) ? json_decode($_POST['langues'], true) : [];

if (!$id_utilisateur || empty($matieres_choisies) || empty($langues_choisies)) {
    echo json_encode(["succes" => false, "message" => "Données manquantes."]);
    exit;
}

// 2. Traitement des fichiers PDF pour MongoDB
$certificatsMongo = [];
if (isset($_FILES['certificats'])) {
    $totalFiles = count($_FILES['certificats']['name']);

    for ($i = 0; $i < $totalFiles; $i++) {
        $tmpFilePath = $_FILES['certificats']['tmp_name'][$i];
        $fileType = $_FILES['certificats']['type'][$i];

        if ($tmpFilePath != "" && $fileType === "application/pdf") {
            $fileData = base64_encode(file_get_contents($tmpFilePath));

            $certificatsMongo[] = [
                'nom_fichier' => $_FILES['certificats']['name'][$i],
                'type' => 'application/pdf',
                'donnees_base64' => $fileData,
                'date_ajout' => new MongoDB\BSON\UTCDateTime()
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
        $mongoClient = new MongoDB\Client("mongodb://localhost:27017");

        $collection = $mongoClient->selectCollection('projet_we4b_nosql', 'certifications_profs');

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