<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Session non identifiee.']);
    exit();
}

function verifierEtudiant() {
    if ($_SESSION['user_role'] !== 'etudiant' && $_SESSION['user_role'] !== 'prof' && $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Autorisation refusee.']);
        exit();
    }
}

function verifierEnseignantOuAdmin() {
    if ($_SESSION['user_role'] !== 'prof' && $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Autorisation refusee. Zone enseignant.']);
        exit();
    }
}

function verifierAdmin() {
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Autorisation refusee. Zone administrateur.']);
        exit();
    }
}