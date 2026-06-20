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

$requete = $db->prepare("SELECT id_utilisateurs FROM utilisateurs WHERE email = :email");
$requete->execute(['email' => $email]);
$user = $requete->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["succes" => false, "message" => "Aucun utilisateur trouvé avec cette adresse email."]);
    exit;
}

$nouveauHash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE utilisateurs SET mdp = :mdp WHERE email = :email");
$result = $update->execute([
    'mdp' => $nouveauHash,
    'email' => $email
]);

if ($result) {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user_id = isset($user['id_utilisateurs']) ? (int)$user['id_utilisateurs'] : null;
        require_once __DIR__ . '/../bdd/config_mongodb.php';

        $dateFrance = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $activitylogsCollection->insertOne([
            'level' => 'INFO',
            'category' => 'USER',
            'action' => 'RESET_PASSWORD',
            'message' => "Réinitialisation mot de passe",
            'id_user' => $user_id,
            'timestamp' => $dateFrance->format('d-m-Y H:i:s'),
            'details' => ['email_cible' => $email]
        ]);
    } catch (Exception $e_mongo) {  }

    echo json_encode(["succes" => true, "message" => "Mot de passe réinitialisé avec succès !"]);
} else {
    echo json_encode(["succes" => false, "message" => "Une erreur est survenue."]);
}
exit;
?>
