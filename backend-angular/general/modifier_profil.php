<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../bdd/config.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['user_id'])) {
        echo json_encode(["erreur" => "ID utilisateur manquant"]);
        exit;
    }

    $user_id = intval($data['user_id']);

    $prenom       = isset($data['prenom']) ? $data['prenom'] : null;
    $nom          = isset($data['nom']) ? $data['nom'] : null;
    $email        = isset($data['email']) ? $data['email'] : null;
    $presentation = isset($data['presentation']) ? $data['presentation'] : '';
    $ancienMdp    = isset($data['ancienMdp']) ? $data['ancienMdp'] : null;
    $nouveauMdp   = isset($data['nouveauMdp']) ? $data['nouveauMdp'] : null;

    if ($prenom && $nom && $email) {

        if (!empty($nouveauMdp)) {
            $query = $db->prepare("UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email, presentation = :presentation, mot_de_passe = :mdp WHERE id_utilisateurs = :id");
            $resultat = $query->execute([
                'prenom'       => $prenom,
                'nom'          => $nom,
                'email'        => $email,
                'presentation' => $presentation,
                'mdp'          => $nouveauMdpHash,
                'id'           => $user_id
            ]);
        } else {
            $query = $db->prepare("UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email, presentation = :presentation WHERE id_utilisateurs = :id");
            $resultat = $query->execute([
                'prenom'       => $prenom,
                'nom'          => $nom,
                'email'        => $email,
                'presentation' => $presentation,
                'id'           => $user_id
            ]);
        }
    }else {
        echo json_encode(["succes" => false, "message" => "Aucune donnée valide reçue"]);
        exit;
    }

    if ($resultat) {
        echo json_encode(["succes" => true, "message" => "Profil mis à jour avec succès !"]);
    } else {
        echo json_encode(["succes" => false, "message" => "Erreur lors de la mise à jour"]);
    }

} catch (PDOException $e) {
    echo json_encode(["erreur" => "Erreur SQL : " . $e->getMessage()]);
}
?>