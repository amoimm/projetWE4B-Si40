<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../../src/models/config.php');
require_once('../Verif_connection.php');

$idUser = (int) $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);



if (isset($data['supp_cours']) || isset($_POST['supp_cours'])) {
    $idCours = isset($data['supp_cours']) ? (int)$data['supp_cours'] : (int)$_POST['supp_cours'];

    try {
        $stmt = $db->prepare("
            SELECT c.id_em 
            FROM cours c
            JOIN enseignant_matiere em ON c.id_em = em.id_em
            WHERE c.id_cours = :id AND em.id_utilisateur = :utilisateur
        ");
        $stmt->execute([
            'id' => $idCours,
            'utilisateur' => $idUser
        ]);

        $result = $stmt->fetch();
        if (!$result) {
            throw new Exception("Cours introuvable ou non autorisé");
        }
        $id_em = $result['id_em'];

        $db->prepare("DELETE FROM cours WHERE id_cours = :id")->execute(['id' => $idCours]);
        $db->prepare("DELETE FROM enseignant_langue WHERE id_em = :id_em")->execute(['id_em' => $id_em]);
        $db->prepare("DELETE FROM enseignant_matiere WHERE id_em = :id_em")->execute(['id_em' => $id_em]);

        echo json_encode(['success' => true, 'message' => 'Cours supprimé avec succès.']);
        exit();

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Erreur suppression : " . $e->getMessage()]);
        exit();
    }
}


try {
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
                AVG(avis.note) AS noteMoyenne,
                COUNT(DISTINCT r.id_rdv) AS nb_rdv
            FROM cours c
            LEFT JOIN enseignant_matiere em ON c.id_em = em.id_em 
            LEFT JOIN matiere m ON em.id_matiere = m.id_matiere
            LEFT JOIN utilisateurs u ON em.id_utilisateur = u.id_utilisateurs
            LEFT JOIN enseignant_langue el ON c.id_em = el.id_em
            LEFT JOIN langue lg ON el.id_el = lg.id_langue
            LEFT JOIN avis_cours ac ON ac.id_cours = c.id_cours
            LEFT JOIN avis ON avis.id_avis = ac.id_avis
            LEFT JOIN rdv r ON r.id_cours = c.id_cours
            WHERE u.id_utilisateurs = :utilisateur
            GROUP BY c.id_cours, c.description, m.nom, u.nom, u.prenom";

    $stmt = $db->prepare($sql);
    $stmt->execute([':utilisateur' => $idUser]);
    $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cours);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Erreur lors de la récupération des cours : " . $e->getMessage()]);
}