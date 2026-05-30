<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

try {
    // On récupère l'ID proprement (par défaut 8 si non fourni)
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 7;

    // ATTENTION : Vérifie bien le nom de ta colonne ci-dessous (id_utilisateurs ou id ?)
    $query = $db->prepare("SELECT * FROM utilisateurs WHERE id_utilisateurs = :id");
    $query->execute(['id' => $user_id]);
    $etudiant = $query->fetch(PDO::FETCH_ASSOC);

    if ($etudiant) {
        echo json_encode($etudiant);
    } else {
        // Au lieu de planter en 404, on répond proprement un objet vide ou une erreur JSON
        echo json_encode([
            "id_utilisateurs" => $user_id,
            "prenom" => "Utilisateur",
            "nom" => "Inconnu",
            "email" => "non-trouve@utbm.fr",
            "theme" => "Clair"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>