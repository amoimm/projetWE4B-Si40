<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Pour MongoDB
require_once __DIR__ . '/../bdd/config_mongodb.php';

// ==========================================
// 1. Récupération des données POST (Texte)
// ==========================================
$role = $_POST["role"] ?? '';
$nom = trim($_POST["nom"] ?? '');
$prenom = trim($_POST["prenom"] ?? '');
$email = trim($_POST["email"] ?? '');
$password = $_POST["password"] ?? '';
$presentation = trim($_POST["presentation"] ?? '');

if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    echo json_encode(["succes" => false, "message" => "Les champs obligatoires sont manquants."]);
    exit;
}

// Vérifier si l'email existe déjà
$checkEmail = $db->prepare("SELECT id_utilisateurs FROM utilisateurs WHERE email = :email");
$checkEmail->execute(['email' => $email]);
if ($checkEmail->fetch()) {
    echo json_encode(["succes" => false, "message" => "Cet email est déjà associé à un compte."]);
    exit;
}

try {
    // ==========================================
    // TRANSACTION SQL (Création de l'utilisateur)
    // ==========================================
    $db->beginTransaction();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $rang = ($role == "enseignant") ? 1 : (($role == "admin") ? 2 : 0);

    // On insère l'utilisateur avec sa présentation
    $insert = $db->prepare("INSERT INTO utilisateurs (email, nom, prenom, mdp, rang, presentation) VALUES (:email, :nom, :prenom, :mdp, :rang, :presentation)");
    $insert->execute([
        "email" => $email, "nom" => $nom, "prenom" => $prenom,
        "mdp" => $hash, "rang" => $rang, "presentation" => $presentation
    ]);

    // On récupère le nouvel ID généré par MySQL
    $id_utilisateur = $db->lastInsertId();

    // ==========================================
    // TRAITEMENT SPÉCIFIQUE SI C'EST UN PROF
    // ==========================================
    if ($role === 'enseignant') {
        $matieres_choisies = isset($_POST['matieres']) ? json_decode($_POST['matieres'], true) : [];
        $langues_choisies = isset($_POST['langues']) ? json_decode($_POST['langues'], true) : [];

        // --- SQL : Matières et Langues ---
        $ids_em = [];
        $stmt_get_matiere = $db->prepare("SELECT id_matiere FROM matiere WHERE nom = :nom");
        $stmt_insert_em   = $db->prepare("INSERT INTO enseignant_matiere (id_utilisateur, id_matiere) VALUES (:id_user, :id_mat)");

        foreach ($matieres_choisies as $nom_matiere) {
            $stmt_get_matiere->execute(['nom' => trim($nom_matiere)]);
            if ($matiere = $stmt_get_matiere->fetch(PDO::FETCH_ASSOC)) {
                $stmt_insert_em->execute(['id_user' => $id_utilisateur, 'id_mat' => $matiere['id_matiere']]);
                $ids_em[] = $db->lastInsertId();
            }
        }

        $ids_langues = [];
        $stmt_get_langue = $db->prepare("SELECT id_langue FROM langue WHERE nom = :nom");
        foreach ($langues_choisies as $nom_langue) {
            $stmt_get_langue->execute(['nom' => trim($nom_langue)]);
            if ($langue = $stmt_get_langue->fetch(PDO::FETCH_ASSOC)) {
                $ids_langues[] = $langue['id_langue'];
            }
        }

        $stmt_insert_el = $db->prepare("INSERT INTO enseignant_langue (id_el, id_em) VALUES (:id_langue, :id_em)");
        foreach ($ids_em as $id_em) {
            foreach ($ids_langues as $id_langue) {
                $stmt_insert_el->execute(['id_langue' => $id_langue, 'id_em' => $id_em]);
            }
        }

        // --- MONGODB : Certificats PDF avec Date personnalisée ---
        $certificatsMongo = [];
        if (isset($_FILES['certificats'])) {
            $totalFiles = count($_FILES['certificats']['name']);

            for ($i = 0; $i < $totalFiles; $i++) {
                $tmpFilePath = $_FILES['certificats']['tmp_name'][$i];
                $fileType = $_FILES['certificats']['type'][$i];

                if ($tmpFilePath != "" && $fileType === "application/pdf") {
                    $fileData = base64_encode(file_get_contents($tmpFilePath));

                    // 🌟 Utilisation exacte de ta logique de date française
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

        if (!empty($certificatsMongo)) {
            $collection = $mongoClient->selectCollection('coursconnect_nosql', 'certif_prof');

            $collection->insertOne([
                'id_utilisateur' => (int)$id_utilisateur,
                'statut_verification' => 'en_attente',
                'certificats' => $certificatsMongo
            ]);
        }
    }

    $db->commit();
    echo json_encode([
        "succes" => true,
        "message" => "Inscription réussie ! Connexion automatique en cours...",
        "utilisateur" => [
            "id" => (int)$id_utilisateur,
            "nom" => $nom,
            "prenom" => $prenom,
            "role" => $role,
            "theme" => "light"
        ]
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["succes" => false, "message" => "Erreur critique : " . $e->getMessage()]);
}
?>