<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si non connecté
    header('Location: index.php');
    exit;
}

// Chemin vers le fichier JSON
$jsonFilePath = '../../seloger-scraper/results/detailed_properties.json';

// Vérifier si le fichier existe
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    if ($jsonContent === false) {
        $errorMessage = "Erreur lors de la lecture du fichier JSON.";
    } else {
        $properties = json_decode($jsonContent, true); // true pour convertir en tableau associatif
        if ($properties === null) {
            $errorMessage = "Erreur lors du décodage du JSON.";
        } else {
            // Extraire les propriétés spécifiques
            $filteredProperties = array_map(function($property) {
                return [
                    'id' => $property['listing']['listingDetail']['id'],
                    'propertyType' => $property['listing']['listingDetail']['propertyType'],
                    'city' => $property['listing']['listingDetail']['address']['city'],
                    'descriptive' => $property['listing']['listingDetail']['descriptive'],
                    'roomCount' => $property['listing']['listingDetail']['roomCount'],
                    'bedroomCount' => $property['listing']['listingDetail']['bedroomCount'],
                    'price' => $property['listing']['listingDetail']['listingPrice']['price'],
                    'pricePerSquareMeter' => $property['listing']['listingDetail']['listingPrice']['pricePerSquareMeter'],
                    'propertyPricesUrl' => $property['listing']['listingDetail']['listingPrice']['propertyPricesUrl'],
                    'latitude' => $property['listing']['listingDetail']['coordinates']['latitude'],
                    'longitude' => $property['listing']['listingDetail']['coordinates']['longitude'],
                    'street' => $property['listing']['listingDetail']['coordinates']['street'],
                    'totalPhotoCount' => $property['listing']['listingDetail']['media']['totalPhotoCount'],
                    'shortDescription' => $property['listing']['listingDetail']['shortDescription'],
                    'livingspace' => isset($property['listing']['listingDetail']['featureCategories']['header']['features'][2]['title']) 
                        ? $property['listing']['listingDetail']['featureCategories']['header']['features'][2]['title'] 
                        : null,
                    'overallspace' => isset($property['listing']['listingDetail']['featureCategories']['header']['features'][3]['title']) 
                        ? $property['listing']['listingDetail']['featureCategories']['header']['features'][3]['title'] 
                        : null,
                    'photos' => $property['listing']['listingDetail']['media']['photos']
                ];
            }, $properties);
        }
    }
} else {
    $errorMessage = "Fichier non trouvé : " . htmlspecialchars($jsonFilePath);
}

function savePropertiesToDatabase($properties) {
    include '../../php/bdd.php';

    foreach ($properties as $property) {
        // Préparer la requête SQL pour insérer les données des propriétés
        $sql = "INSERT INTO properties (id, propertyType, city, descriptive, roomCount, bedroomCount, price, pricePerSquareMeter, propertyPricesUrl, latitude, longitude, street, totalPhotoCount, shortDescription, livingspace, overallspace)
                VALUES (:id, :propertyType, :city, :descriptive, :roomCount, :bedroomCount, :price, :pricePerSquareMeter, :propertyPricesUrl, :latitude, :longitude, :street, :totalPhotoCount, :shortDescription, :livingspace, :overallspace)";
        
        $stmt = $cnx->prepare($sql);

        // Lier les paramètres aux valeurs
        $stmt->bindParam(':id', $property['id']);
        $stmt->bindParam(':propertyType', $property['propertyType']);
        $stmt->bindParam(':city', $property['city']);
        $stmt->bindParam(':descriptive', $property['descriptive']);
        $stmt->bindParam(':roomCount', $property['roomCount']);
        $stmt->bindParam(':bedroomCount', $property['bedroomCount']);
        $stmt->bindParam(':price', $property['price']);
        $stmt->bindParam(':pricePerSquareMeter', $property['pricePerSquareMeter']);
        $stmt->bindParam(':propertyPricesUrl', $property['propertyPricesUrl']);
        $stmt->bindParam(':latitude', $property['latitude']);
        $stmt->bindParam(':longitude', $property['longitude']);
        $stmt->bindParam(':street', $property['street']);
        $stmt->bindParam(':totalPhotoCount', $property['totalPhotoCount']);
        $stmt->bindParam(':shortDescription', $property['shortDescription']);
        $stmt->bindParam(':livingspace', $property['livingspace']);
        $stmt->bindParam(':overallspace', $property['overallspace']);

        // Exécuter la requête
        $stmt->execute();

        // Enregistrer les photos
        savePhotosToDatabase($property['id'], $property['photos']);
    }
}

function savePhotosToDatabase($propertyId, $photos) {
    include '../../php/bdd.php';

    foreach ($photos as $photo) {
        // Préparer la requête SQL pour insérer les données des photos
        $sql = "INSERT INTO photo (id, url, property_id)
                VALUES (:id, :url, :property_id)";
        
        $stmt = $cnx->prepare($sql);

        // Lier les paramètres aux valeurs
        $stmt->bindParam(':id', $photo['id']);
        $stmt->bindParam(':url', $photo['defaultUrl']);
        $stmt->bindParam(':property_id', $propertyId);

        // Exécuter la requête
        $stmt->execute();
    }
}

// Vérifier si le formulaire de sauvegarde a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_properties'])) {
    try {
        savePropertiesToDatabase($filteredProperties);
        $successMessage = "Données sauvegardées avec succès.";
    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de l'insertion dans la base de données : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Données des Propriétés</title>
</head>
<body>
    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['login']); ?> !</h1>
    <p>Ceci est une page protégée.</p>
    <a href="logout.php">Déconnexion</a>

    <h2>Données des propriétés</h2>

    <?php if (isset($successMessage)): ?>
        <p><?php echo htmlspecialchars($successMessage); ?></p>
    <?php elseif (isset($errorMessage)): ?>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form method="post">
        <button type="submit" name="save_properties">Sauvegarder les propriétés dans la base de données</button>
    </form>

    <?php if (isset($filteredProperties) && !empty($filteredProperties)): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type de Propriété</th>
                    <th>Ville</th>
                    <th>Description</th>
                    <th>Nombre de Pièces</th>
                    <th>Nombre de Chambres</th>
                    <th>Prix</th>
                    <th>Prix au m²</th>
                    <th>URL des Prix</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Adresse</th>
                    <th>Total Photos</th>
                    <th>Description Courte</th>
                    <th>Superficie</th>
                    <th>Superficie Totale</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filteredProperties as $property): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($property['id']); ?></td>
                        <td><?php echo htmlspecialchars($property['propertyType']); ?></td>
                        <td><?php echo htmlspecialchars($property['city']); ?></td>
                        <td><?php echo htmlspecialchars($property['descriptive']); ?></td>
                        <td><?php echo htmlspecialchars($property['roomCount']); ?></td>
                        <td><?php echo htmlspecialchars($property['bedroomCount']); ?></td>
                        <td><?php echo htmlspecialchars($property['price']); ?></td>
                        <td><?php echo htmlspecialchars($property['pricePerSquareMeter']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($property['propertyPricesUrl']); ?>" target="_blank">Voir les prix</a></td>
                        <td><?php echo htmlspecialchars($property['latitude']); ?></td>
                        <td><?php echo htmlspecialchars($property['longitude']); ?></td>
                        <td><?php echo htmlspecialchars($property['street']); ?></td>
                        <td><?php echo htmlspecialchars($property['totalPhotoCount']); ?></td>
                        <td><?php echo htmlspecialchars($property['shortDescription']); ?></td>
                        <td><?php echo htmlspecialchars($property['livingspace']); ?></td>
                        <td><?php echo htmlspecialchars($property['overallspace']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune donnée disponible.</p>
    <?php endif; ?>
</body>
</html>
