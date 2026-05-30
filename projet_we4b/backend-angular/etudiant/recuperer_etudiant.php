<?php
// 1. Autoriser Angular (Port 4200) à récupérer les données de la BDD
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

// 2. Inclure config.php en utilisant un chemin absolu basé sur le dossier actuel
require_once __DIR__ . '/config.php';

try {
    // 3. Récupérer l'ID de l'étudiant envoyé par Angular (ex: ?user_id=7)
    $user_id = 7;

    // 4. Exécution de la requête SQL
    $query = $db->prepare("SELECT * FROM utilisateurs WHERE id_utilisateurs = :id");
    $query->execute(['id' => $user_id]);
    $etudiant = $query->fetch(PDO::FETCH_ASSOC);

    if ($etudiant) {
        // Renvoie l'étudiant en JSON
        echo json_encode($etudiant);
    } else {
        echo json_encode(["erreur" => "Étudiant introuvable pour l'ID " . $user_id]);
    }

} catch (PDOException $e) {
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>