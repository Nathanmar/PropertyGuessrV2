<?php
// Connexion à la base de données
include 'bdd.php';

// Vérification que l'ID est bien passé en paramètre GET
if (isset($_GET['id'])) {
    $propertyId = $_GET['id'];

    // Requête pour récupérer les détails de la propriété
    $query = $cnx->prepare("SELECT * FROM properties WHERE id = :id");
    $query->bindParam(':id', $propertyId, PDO::PARAM_INT);
    $query->execute();
    $property = $query->fetch(PDO::FETCH_ASSOC);

    // Si la propriété est trouvée
    if ($property) {
        $propertyType = $property['propertyType'];
        $city = $property['city'];
        $street = $property['street'];
        $shortDescription = $property['shortDescription'];
        $totalPhotoCount = $property['totalPhotoCount'];
        $livingspace = $property['livingspace'];
        $overallspace = $property['overallspace'];
        $descriptive = $property['descriptive'];
        $price = $property['price'];
        $latitude = $property['latitude'];
        $longitude = $property['longitude'];
        $roomCount = $property['roomCount'];
        $bedroomCount = $property['bedroomCount'];  
    } else {
        echo "Propriété non trouvée.";
        exit;
    }
} else {
    echo "Aucune propriété sélectionnée.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la propriété</title>
    <link rel="stylesheet" href="../css/details.css">
</head>
<body>
    <header>
        <h1>Détails de la Propriété</h1>
    </header>
    <main>
        <?php if ($property): ?>
            <h2><?= htmlspecialchars($propertyType) ?></h2>
            <p><strong>Ville :</strong> <?= htmlspecialchars($city) ?></p>
            <p><strong>Prix :</strong> <?= htmlspecialchars(number_format($price)) ?> €</p>
            <p><strong>Espace habitable :</strong> <?= htmlspecialchars($livingspace) ?> </p>
            <p><strong>Espace total :</strong> <?= htmlspecialchars($overallspace) ?></p>
            <p><strong>Nombre de chambres :</strong> <?= htmlspecialchars($bedroomCount) ?></p>
            <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($roomCount) ?></p>
            <p><strong>Description courte :</strong> <?= htmlspecialchars($shortDescription) ?></p>
            <p><strong>Description détaillée :</strong> <?= htmlspecialchars($descriptive) ?></p>
        <?php endif; ?>
    </main>
</body>
</html>
