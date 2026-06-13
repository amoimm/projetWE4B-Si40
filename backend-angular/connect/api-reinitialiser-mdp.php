<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$nouveauMdp = $data["nouveauMdp"] ?? "";

if (empty($email) || empty($nouveauMdp)) {
    echo json_encode(["succes" => false, "message" => "Veuillez renseigner tous les champs."]);
    exit;
}

// Vérifier si l'utilisateur existe
$requete = $db->prepare("SELECT id_utilisateurs FROM utilisateurs WHERE email = :email");
$requete->execute(['email' => $email]);
$user = $requete->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["succes" => false, "message" => "Aucun utilisateur trouvé avec cette adresse email."]);
    exit;
}

// Hacher le nouveau mot de passe et mettre à jour
$nouveauHash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE utilisateurs SET mdp = :mdp WHERE email = :email");
$result = $update->execute([
    'mdp' => $nouveauHash,
    'email' => $email
]);

if ($result) {
    echo json_encode(["succes" => true, "message" => "Mot de passe réinitialisé avec succès !"]);
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $admin_id = $_SESSION['user_id'] ?? 'admin';

        require_once __DIR__ . '/../bdd/config_mongodb.php';
        $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $activitylogsCollection->insertOne([
            'level' => 'INFO',
            'category' => 'ADMIN',
            'action' => 'ADD_LANGUE',
            'message' => "L'administrateur a ajouté une langue",
            'id_user' => $admin_id,
            'timestamp' => $dateFrance->format('d-m-Y H:i:s'),
            'details' => [
                'nom_langue' => $nom_langue
            ]
        ]);
    } catch (Exception $e_mongo) {
        // Ignorer en cas d'erreur de log
    }
} else {
    echo json_encode(["succes" => false, "message" => "Une erreur est survenue lors de la mise à jour."]);
}
?>
