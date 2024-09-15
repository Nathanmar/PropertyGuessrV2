<?php
session_start();
session_destroy(); // Détruit la session
header('Location: index.php'); // Rediriger vers la page de connexion
exit;
