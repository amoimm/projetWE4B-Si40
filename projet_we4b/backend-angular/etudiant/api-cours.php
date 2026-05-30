<?php
// api-cours.php
header("Access-Control-Allow-Origin: *"); // Permet à Angular d'interroger ce script
header("Content-Type: application/json; charset=UTF-8");

require_once('../src/models/config.php'); // Ton fichier de connexion BDD

// On récupère les filtres envoyés par Angular (via l'URL ou le POST)
$recherche = $_GET['recherche'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';

// Ta requête SQL (simplifiée pour l'exemple, tu peux remettre tes jointures complètes !)
$sql = "SELECT c.id_cours, m.nom AS nom_matiere, u.nom AS nom_prof, u.prenom AS prenom_prof, 
               c.prix_heure, c.mode_cours, c.description, AVG(avis.note) AS noteMoyenne
        FROM cours c
        LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em 
        LEFT JOIN matiere m ON em.id_matiere = m.id_matiere
        LEFT JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
        LEFT JOIN avis_cours ac ON ac.id_cours = c.id_cours
        LEFT JOIN avis ON avis.id_avis = ac.id_avis
        WHERE 1=1";

$params = [];
if (!empty($recherche)) {
    $sql .= " AND (m.nom LIKE :recherche OR u.nom LIKE :recherche)";
    $params['recherche'] = '%' . $recherche . '%';
}
if (!empty($prix_max)) {
    $sql .= " AND c.prix_heure <= :prix_max";
    $params['prix_max'] = (float)$prix_max;
}

$sql .= " GROUP BY c.id_cours";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On envoie le résultat à Angular sous forme de JSON
echo json_encode($cours);