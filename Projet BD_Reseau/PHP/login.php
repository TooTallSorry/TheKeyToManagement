<?php
include 'db_connect.php'; // Connexion à la base de données
session_start(); // Démarrer la session

$message = ''; // Variable pour stocker les messages d'erreur ou de succès
$is_logged_in = false; // Indicateur pour vérifier si l'utilisateur est connecté

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); // Récupérer l'email depuis le formulaire
    $password = trim($_POST['password'] ?? ''); // Récupérer le mot de passe

    try {
        // Rechercher un utilisateur correspondant dans la base de données
        $stmt = $pdo->prepare("SELECT idclient, prenomclient, password FROM Client WHERE mail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérifier si les colonnes idClient et nomClient existent
            if (isset($user['idclient']) && isset($user['prenomclient'])) {
                // Comparer les mots de passe
                if ($user['password'] === $password) { // Comparaison simple
                    $_SESSION['user_id'] = $user['idclient'];
                    $_SESSION['user_name'] = $user['prenomclient'];
                    $is_logged_in = true;
                } else {
                    $message = "Mot de passe incorrect.";
                }
            } else {
                $message = "Données utilisateur incomplètes dans la base de données.";
            }
        } else {
            $message = "Aucun utilisateur trouvé avec cet email.";
        }
    } catch (PDOException $e) {
        $message = "Erreur : " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php if ($is_logged_in): ?>
        <!-- Message de succès si l'utilisateur est connecté -->
        <p style="color: green;">Connexion réussie !</p>
        <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
        <div class="button-container">
            <a href="dashboard.php" class="btn">Accéder à votre tableau de bord</a>
        </div>
    <?php else: ?>
        <!-- Affichage des messages d'erreur -->
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required><br>
            <label>Mot de passe:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Se Connecter</button>
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
