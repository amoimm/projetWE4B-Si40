<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=NOM_DE_TA_BASE', 'root', '');
$stmt = $pdo->query("SELECT * FROM cours");
$resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultat);
?>