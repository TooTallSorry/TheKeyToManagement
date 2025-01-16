<?php
// Inclusion de la connexion à la base de données
include 'db_connect.php';

// Démarrer la session
session_start();

// Message par défaut pour l'utilisateur
$message = '';

// Inclusion du menu de navigation
include 'menu.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire avec des valeurs par défaut si non définies
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $password = $_POST['password'] ?? '';

    // Générer un ID unique pour le client
    $idClient = 'CL' . rand(1000, 9999);

    try {
        // Vérifier si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Client WHERE mail = ?");
        $stmt->execute([$email]);

        if ($stmt->fetchColumn() > 0) {
            // Message d'erreur si l'email est déjà utilisé
            $message = "Cet email est déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur dans la table Client
            $stmt = $pdo->prepare("INSERT INTO Client (idClient, nomClient, prenomClient, tel, mail, password) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$idClient, $nom, $prenom, $tel, $email, $password]);

            // Message de succès et démarrer une session pour l'utilisateur
            $message = "Inscription réussie !";
            $_SESSION['user_id'] = $idClient;
            $_SESSION['user_name'] = $nom . ' ' . $prenom;

            // Rediriger l'utilisateur vers le tableau de bord
            header('Location: dashboard.php');
            exit();
        }
    } catch (PDOException $e) {
        // Message d'erreur en cas de problème avec la base de données
        $message = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <!-- Titre de la page -->
    <h1>Inscription</h1>

    <!-- Message pour l'utilisateur -->
    <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>

    <!-- Formulaire d'inscription -->
    <form method="POST">
        <label>Nom:</label><input type="text" name="nom" required><br>
        <label>Prénom:</label><input type="text" name="prenom" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Téléphone:</label><input type="text" name="tel" required><br>
        <label>Mot de passe:</label><input type="password" name="password" required><br>
        <button type="submit">S'inscrire</button>
    </form>

    <!-- Footer de la page -->
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
