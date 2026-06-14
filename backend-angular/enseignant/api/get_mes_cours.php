<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../../bdd/config.php');

$idUser = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

if ($idUser === 0) {
    $headers = getallheaders();
    $idUser = isset($headers['X-User-Id']) ? (int)$headers['X-User-Id'] : (isset($headers['x-user-id']) ? (int)$headers['x-user-id'] : 0);
}

if ($idUser <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé. ID utilisateur manquant dans les entêtes."]);
    exit;
}

try {
    $recherche = $_GET['recherche'] ?? '';
    $langue = $_GET['langue'] ?? '';
    $matiere = $_GET['matiere'] ?? '';
    $avis = $_GET['avis'] ?? '';

    $sql = "SELECT
                c.prix_heure,
                c.mode_cours,
                c.camera_obligatoire,
                c.suivi,
                c.description,
                m.nom AS nom_matiere,
                u.nom AS nom_prof,
                u.prenom AS prenom_prof,
                GROUP_CONCAT(DISTINCT lg.nom SEPARATOR ', ') AS langues,
                c.id_cours AS id_cours,
                AVG(ac_avis.note) AS noteMoyenne,
                COUNT(DISTINCT r.id_rdv) AS nb_rdv
            FROM cours c
            LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em 
            LEFT JOIN matiere m ON em.id_matiere = m.id_matiere
            LEFT JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
            LEFT JOIN enseignant_langue el ON c.id_em = el.id_em
            LEFT JOIN langue lg ON el.id_el = lg.id_langue
            LEFT JOIN avis_cours ac ON ac.id_cours = c.id_cours
            LEFT JOIN avis ac_avis ON ac_avis.id_avis = ac.id_avis
            LEFT JOIN rdv r ON r.id_cours = c.id_cours
            WHERE u.id_utilisateurs = :utilisateur
    ";

    $params = [':utilisateur' => $idUser];

    if (!empty($recherche)) {
        $sql .= " AND (m.nom LIKE :recherche 
            OR u.prenom LIKE :recherche 
            OR u.nom LIKE :recherche 
            OR lg.nom LIKE :recherche 
            OR c.description LIKE :recherche)";
        $params[':recherche'] = '%' . $recherche . '%';
    }

    if (!empty($langue)) {
        $sql .= " AND lg.id_langue = :langue";
        $params[':langue'] = $langue;
    }

    if (!empty($matiere)) {
        $sql .= " AND m.id_matiere = :matiere";
        $params[':matiere'] = $matiere;
    }

    $sql .= " GROUP BY c.id_cours, c.description, m.nom, u.nom, u.prenom";

    if (!empty($avis)) {
        $sens = ($avis === 'croissant') ? 'ASC' : 'DESC';
        $sql .= " ORDER BY noteMoyenne $sens";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cours as &$c) {
        $c['id_cours'] = (int)$c['id_cours'];
        $c['prix_heure'] = (float)$c['prix_heure'];
        $c['camera_obligatoire'] = (int)$c['camera_obligatoire'] === 1;
        $c['suivi'] = (int)$c['suivi'] === 1;
        $c['nb_rdv'] = (int)$c['nb_rdv'];
        $c['noteMoyenne'] = $c['noteMoyenne'] ? (float)$c['noteMoyenne'] : null;
    }

    echo json_encode($cours);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>