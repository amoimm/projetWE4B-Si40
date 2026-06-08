<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../bdd/config.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if(empty($email) || empty($password)){
    echo json_encode(["succes" => false, "message" => "Veuillez renseigner tous les champs."]);
    exit;
}

$requete = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email");
$requete->execute(['email' => $email]);
$user = $requete->fetch(PDO::FETCH_ASSOC);

if($user && password_verify($password, $user['mdp'])){
    // Déduction du rôle
    if ($user['rang'] == 2) { $role = 'admin'; }
    elseif ($user['rang'] == 1) { $role = 'enseigant'; }
    else { $role = 'etudiant'; }

    echo json_encode([
        "succes" => true,
        "utilisateur" => [
            "id" => (int)$user['id_utilisateurs'],
            "nom" => $user['nom'],
            "prenom" => $user['prenom'],
            "role" => $role,
            "theme" => !empty($user['theme']) ? $user['theme'] : 'light'
        ]
    ]);
} else {
    echo json_encode(["succes" => false, "message" => "Email ou mot de passe incorrect."]);
}
?>