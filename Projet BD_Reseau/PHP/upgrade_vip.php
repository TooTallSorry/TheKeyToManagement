<?php
include 'db_connect.php'; // Connexion à la base de données
session_start(); // Démarrer la session

// Rediriger les utilisateurs non connectés vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$idClient = $_SESSION['user_id'];

try {
    // Vérifier si l'utilisateur est déjà un client VIP
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientVIP WHERE idclient = ?");
    $stmt->execute([$idClient]);
    if ($stmt->fetchColumn() > 0) {
        echo "<p style='color: red;'>Vous êtes déjà un client VIP.</p>";
    } else {
        // Générer un code VIP unique
        $codeVip = 'VIP' . rand(100000000, 999999999);

        // Ajouter l'utilisateur à la table clientVIP
        $stmt = $pdo->prepare("INSERT INTO clientVIP (idclient, serviceexclusif, codevip) VALUES (?, 'Accès piscine', ?)");
        $stmt->execute([$idClient, $codeVip]);

        echo "<p style='color: green;'>Félicitations, vous êtes maintenant un client VIP avec le code : $codeVip !</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<a href="dashboard.php">Retour au tableau de bord</a>
