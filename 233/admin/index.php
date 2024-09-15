<?php
session_start(); // Démarre la session

// Inclure la connexion à la base de données
include '../../php/bdd.php';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Valider les entrées
    if (!empty($login) && !empty($password)) {
        // Crypter le mot de passe en MD5
        $encrypted_password = md5($password);

        // Préparer la requête pour vérifier si l'utilisateur existe
        $sql = "SELECT * FROM users WHERE login = ? AND password = ?";
        $stmt = $cnx->prepare($sql);
        $stmt->execute([$login, $encrypted_password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si un utilisateur est trouvé
        if ($user) {
            // Stocker les informations dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];

            // Redirection vers une page protégée après connexion
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Connexion</h2>

    <?php if (isset($error)) : ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="login">Nom d'utilisateur :</label>
        <input type="text" id="login" name="login" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
