<?php
session_start();
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="./tktm.png">
    <title>Accueil - Site de Réservation</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<h1>Bienvenue sur notre plateforme de gestion hôtelière</h1>
<p>
    Simplifiez votre expérience de gestion et de réservation grâce à notre interface intuitive et efficace. Que vous soyez un client souhaitant réserver une chambre, 
    un administrateur gérant l'organisation des réservations ou encore un utilisateur cherchant à devenir membre VIP, tout est à portée de clic !
</p>
<p>
    Découvrez nos chambres confortables, adaptées à tous les besoins, et profitez des services exclusifs offerts à nos membres VIP. 
    Grâce à notre plateforme, vous pouvez :
</p>
<ol>
    <li>Consulter la disponibilité des chambres en temps réel.</li>
    <li>Effectuer des réservations en quelques étapes simples.</li>
    <li>Gérer vos réservations directement depuis votre tableau de bord.</li>
    <li>Accéder à des services exclusifs en devenant membre VIP.</li>
</ol>
<p>
    Notre objectif est de rendre votre séjour aussi agréable que possible, avec une gestion rapide, sécurisée et sans souci.
    Merci de votre confiance, et à très bientôt dans notre établissement !
</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Si l'utilisateur est connecté -->
    <p>Connecté en tant que : <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
<?php else: ?>
    <!-- Si l'utilisateur n'est pas connecté -->
    <p>Connectez-vous ou inscrivez-vous pour commencer :</p>
    <div class="button-container">
        <a href="login.php" class="btn">Se Connecter</a>
        <a href="register.php" class="btn">S'inscrire</a>
    </div>
    <p>Vous pouvez également consulter les chambres disponibles :</p>
    <div class="button-container">
        <a href="view_rooms.php" class="btn">Chambres Disponibles</a>
    </div>
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
