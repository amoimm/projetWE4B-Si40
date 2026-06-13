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

$data = json_decode(file_get_contents("php://input"), true);
$id_enseignant = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($data['user_id']) ? (int)$data['user_id'] : 0);

if ($id_enseignant <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. Session expirée ou ID utilisateur manquant."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matiere = (int) ($data['matiere'] ?? 0);
    $langues_selectionnees = $data['langues'] ?? [];
    $prix_heure = floatval($data['prix_heure'] ?? 0);
    $mode_cours = htmlspecialchars(trim($data['mode_cours'] ?? ''));
    $camera_obligatoire = isset($data['camera_obligatoire']) && $data['camera_obligatoire'] ? 1 : 0;
    $suivi = isset($data['suivi']) && $data['suivi'] ? 1 : 0;
    $description = htmlspecialchars(trim($data['description'] ?? ''));

    $erreurs = [];

    if ($matiere <= 0) {
        $erreurs[] = "La matière est obligatoire.";
    }

    if (empty($langues_selectionnees)) {
        $erreurs[] = "Vous devez sélectionner au moins une langue.";
    }

    if ($prix_heure <= 0) {
        $erreurs[] = "Le prix par heure doit être un nombre positif.";
    }

    if (empty($mode_cours)) {
        $erreurs[] = "Veuillez sélectionner un mode de cours.";
    }

    if (empty($description)) {
        $erreurs[] = "La description est obligatoire.";
    } elseif (strlen($description) < 20) {
        $erreurs[] = "La description doit contenir au moins 20 caractères.";
    }

    if (!empty($erreurs)) {
        http_response_code(400);
        echo json_encode(["success" => false, "errors" => $erreurs]);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Liaison enseignant - matière
        $sql = "INSERT INTO enseignant_matiere (id_utilisateur, id_matiere) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_enseignant, $matiere]);
        $em = $db->lastInsertId();

        // 2. Création du cours
        $sql = "INSERT INTO cours (id_em, prix_heure, mode_cours, camera_obligatoire, suivi, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$em, $prix_heure, $mode_cours, $camera_obligatoire, $suivi, $description]);

        // 3. Liaison langues
        $sql = "INSERT INTO enseignant_langue (id_el, id_em) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        foreach ($langues_selectionnees as $id_langue) {
            $stmt->execute([(int)$id_langue, $em]);
        }

        $db->commit();
        echo json_encode(["success" => true, "message" => "Le cours a été créé avec succès !"]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(["success" => false, "errors" => ["Erreur lors de la création du cours : " . $e->getMessage()]]);
    }
}
?>
