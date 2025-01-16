<?php
include 'db_connect.php'; // Inclure la connexion à la base de données
session_start(); // Démarrer la session
include 'menu.php'; // Inclure le menu de navigation

// Rediriger l'utilisateur vers la page de connexion s'il n'est pas authentifié
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$idClient = $_SESSION['user_id']; // Récupérer l'ID du client depuis la session
$vipInfo = null;

try {
    // Vérifier si l'utilisateur est un client VIP
    $stmt = $pdo->prepare("SELECT serviceexclusif, codevip FROM clientVIP WHERE idclient = ?");
    $stmt->execute([$idClient]);
    $vipInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Afficher un message d'erreur en cas de problème
    echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Tableau de bord</title>
</head>
<body>
    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</h1>

    <?php if ($vipInfo): ?>
        <!-- Affichage des informations VIP si l'utilisateur est un client VIP -->
        <h2>Statut VIP</h2>
        <p>Service exclusif : <?php echo htmlspecialchars($vipInfo['serviceexclusif']); ?></p>
        <p>Code VIP : <?php echo htmlspecialchars($vipInfo['codevip']); ?></p>
    <?php else: ?>
        <!-- Option pour devenir VIP -->
        <h2>Options VIP</h2>
        <form method="POST" action="upgrade_vip.php">
            <button type="submit">Devenir VIP</button>
        </form>
    <?php endif; ?>
    
    <footer>
        <div class="footer-section">
            &copy; 2024 Gestion Admin - Tous droits réservés.
        </div>
        <div class="footer-section">
            Cheballah Jawed | Huang Yanmo | Anagonou Hervé
        </div>
        <div class="footer-section">
            Projet BD/Réseaux
        </div>
    </footer>
</body>
</html>
