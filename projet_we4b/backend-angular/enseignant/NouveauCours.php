<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
require_once('../../src/models/config.php');
require_once('../Verif_connection.php');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt_matiere = $db->query('SELECT id_matiere, nom FROM matiere');
    $matieres = $stmt_matiere->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_langue = $db->query('SELECT id_langue, nom FROM langue');
    $langues = $stmt_langue->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['matieres' => $matieres, 'langues' => $langues]);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_enseignant = (int) $_SESSION['user_id'];
    
    try {
        $sql = "INSERT INTO enseignant_matiere (id_utilisateur, id_matiere) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_enseignant, $data['matiere']]);
        $em = $db->lastInsertId();

        $sql = "INSERT INTO cours (id_em, prix_heure, mode_cours, camera_obligatoire, suivi, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$em, $data['prix_heure'], $data['mode_cours'], $data['camera_obligatoire'] ? 1 : 0, $data['suivi'] ? 1 : 0, $data['description']]);

        $sql = "INSERT INTO enseignant_langue (id_el, id_em) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        foreach ($data['langues'] as $id_langue) {
            $stmt->execute([$id_langue, $em]);
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}