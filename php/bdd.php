<?php
try {
    $cnx = new PDO("mysql:host=localhost;dbname=propertyguessr;charset=utf8mb4", "root", "");
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cnx->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
