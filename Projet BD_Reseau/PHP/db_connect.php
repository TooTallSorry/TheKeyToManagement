<?php
// Configuration de la base de données
$host = 'postgresql-tktm.alwaysdata.net';
$dbname = 'tktm_bd';
$username = 'tktm';
$password = 'Pokemon0000!@';

try {
    // Connexion à la base de données
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Activer le mode exception pour les erreurs
} catch (PDOException $e) {
    // Message d'erreur en cas de problème de connexion
    die("Erreur de connexion : " . $e->getMessage());
}
?>
