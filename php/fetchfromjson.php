<?php
// Inclure le fichier de connexion à la base de données
include 'bdd.php';

// Requête pour obtenir toutes les propriétés de la base de données
$sql = "SELECT * FROM properties";
$stmt = $cnx->prepare($sql);
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retourner les données sous forme de JSON
header('Content-Type: application/json');
echo json_encode($properties);
?>
