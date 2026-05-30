<?php
// Autorise Angular à communiquer avec ce fichier
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../bdd/config.php';

// =========================================================
// 1. RÉCUPÉRATION DES FILTRES DEPUIS ANGULAR
// (Angular utilise HttpParams en méthode GET)
// =========================================================
$recherche = $_GET['recherche'] ?? '';
$langue_filtre = $_GET['filtre_langue'] ?? '';
$matiere_filtre = $_GET['filtre_matiere'] ?? '';
$avis_filtre = $_GET['filtre_avis'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$mode_filtre = $_GET['filtre_mode'] ?? '';
$suivi_filtre = $_GET['filtre_suivi'] ?? '';

// =========================================================
// 2. CONSTRUCTION DYNAMIQUE DE LA REQUÊTE
// =========================================================
$sql = "SELECT 
            c.description,
            c.prix_heure,
            c.mode_cours,
            c.camera_obligatoire,
            c.suivi,
            m.nom AS nom_matiere,
            u.nom AS nom_prof,
            u.prenom AS prenom_prof,
            GROUP_CONCAT(DISTINCT lg.nom SEPARATOR ', ') AS langues,
            c.id_cours AS id_cours,
            AVG(avis.note) AS noteMoyenne 
        FROM cours c
        LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em 
        LEFT JOIN matiere m ON em.id_matiere = m.id_matiere
        LEFT JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
        LEFT JOIN enseignant_langue el ON c.id_em = el.id_em
        LEFT JOIN langue lg ON el.id_el = lg.id_langue
        LEFT JOIN avis_cours ac ON ac.id_cours = c.id_cours
        LEFT JOIN avis ON avis.id_avis = ac.id_avis
        WHERE 1=1";

$params = [];

// Ajout dynamique des conditions
if (!empty($recherche)) {
    $sql .= " AND (m.nom LIKE :recherche OR u.prenom LIKE :recherche OR u.nom LIKE :recherche OR lg.nom LIKE :recherche)";
    $params['recherche'] = '%' . $recherche . '%';
}

if (!empty($langue_filtre)) {
    $sql .= " AND lg.id_langue = :langue";
    $params['langue'] = $langue_filtre;
}

if (!empty($matiere_filtre)) {
    $sql .= " AND m.id_matiere = :matiere";
    $params['matiere'] = $matiere_filtre;
}

if ($prix_max !== '') {
    $sql .= " AND c.prix_heure <= :prix_max";
    $params['prix_max'] = (float)$prix_max;
}

if (!empty($mode_filtre)) {
    $sql .= " AND c.mode_cours = :mode";
    $params['mode'] = $mode_filtre;
}

if ($suivi_filtre !== '') {
    $sql .= " AND c.suivi = :suivi";
    $params['suivi'] = (int)$suivi_filtre;
}

// Groupement obligatoire à cause des jointures et fonctions d'agrégation
$sql .= " GROUP BY c.id_cours, c.description, c.prix_heure, c.mode_cours, c.camera_obligatoire, c.suivi, m.nom, u.nom, u.prenom";

// Ajout du tri par avis
if (!empty($avis_filtre)) {
    $sens = ($avis_filtre === 'croissant') ? 'ASC' : 'DESC';
    $sql .= " ORDER BY noteMoyenne $sens";
} else {
    // Tri par défaut
    $sql .= " ORDER BY c.id_cours DESC";
}

// =========================================================
// 3. EXÉCUTION ET RENVOI DU JSON
// =========================================================
try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $coursTrouves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // On convertit les types pour éviter qu'Angular ait des surprises (surtout pour les nombres)
    foreach ($coursTrouves as &$cours) {
        $cours['prix_heure'] = (float)$cours['prix_heure'];
        $cours['camera_obligatoire'] = (int)$cours['camera_obligatoire'];
        $cours['suivi'] = (int)$cours['suivi'];
        $cours['noteMoyenne'] = $cours['noteMoyenne'] !== null ? (float)$cours['noteMoyenne'] : null;
    }

    // On renvoie le résultat propre à Angular
    echo json_encode($coursTrouves);

} catch (Exception $e) {
    http_response_code(500); // Code d'erreur serveur
    echo json_encode(["erreur" => "Erreur lors de l'exécution de la recherche", "details" => $e->getMessage()]);
}