<?php
// Vérifiez si l'utilisateur est connecté pour afficher les options appropriées
$isLoggedIn = isset($_SESSION['user_id']);
?>
<nav>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <?php if ($isLoggedIn): ?>
            <!-- Liens visibles uniquement pour les utilisateurs connectés -->
            <li><a href="dashboard.php">Tableau de Bord</a></li>
            <li><a href="view_reservations.php">Mes Réservations</a></li>
            <li><a href="view_rooms.php">Chambres Disponibles</a></li>
            <li><a href="logout.php">Se Déconnecter</a></li>
        <?php else: ?>
            <!-- Liens visibles uniquement pour les utilisateurs non connectés -->
            <li><a href="login.php">Se Connecter</a></li>
            <li><a href="register.php">S'inscrire</a></li>
        <?php endif; ?>
    </ul>
</nav>
<hr>
