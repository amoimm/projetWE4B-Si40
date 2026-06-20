<?php
require_once '../bdd/config_mongodb.php';

// Agrégation MongoDB : Filtrer par STUDENT_SEARCH, grouper par matière, compter et trier
$pipeline = [
    ['$match' => ['category' => 'STUDENT_SEARCH']],
    ['$group' => ['_id' => '$details.matiere', 'count' => ['$sum' => 1]]],
    ['$sort' => ['count' => -1]],
    ['$limit' => 5] // Top 5
];

$cursor = $activitylogsCollection->aggregate($pipeline);
$matieresRecherchees = iterator_to_array($cursor);

echo json_encode([
    "matieres" => $matieresRecherchees
]);