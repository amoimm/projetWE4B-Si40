<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bdd/config.php';

try {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 7;

    $query = $db->prepare("SELECT * FROM utilisateurs WHERE id_utilisateurs = :id");
    $query->execute(['id' => $user_id]);
    $etudiant = $query->fetch(PDO::FETCH_ASSOC);

    if ($etudiant) {
        echo json_encode($etudiant);
    } else {
        echo json_encode([
            "id_utilisateurs" => $user_id,
            "prenom" => "Utilisateur",
            "nom" => "Inconnu",
            "email" => "non-trouve@utbm.fr",
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>