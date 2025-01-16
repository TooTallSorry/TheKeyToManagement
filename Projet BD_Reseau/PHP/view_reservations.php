<?php
include 'db_connect.php'; // Connexion à la base de données
session_start(); // Démarrer la session
include 'menu.php'; // Inclure le menu de navigation

// Rediriger les utilisateurs non connectés vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$idClient = $_SESSION['user_id'];

try {
    // Récupérer les réservations de l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT Reservation.idreservation, Chambre.numerochambre, Reservation.datedebut, Reservation.datefin, Reservation.etatreserver 
                           FROM Reservation 
                           JOIN Chambre ON Reservation.idchambre = Chambre.idchambre 
                           WHERE Reservation.idclient = ?");
    $stmt->execute([$idClient]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Mes Réservations</title>
</head>
<body>
    <h1>Mes Réservations</h1>
    <?php if (empty($reservations)): ?>
        <!-- Message si l'utilisateur n'a pas de réservations -->
        <p>Vous n'avez pas de réservations.</p>
    <?php else: ?>
        <!-- Affichage des réservations -->
        <table border="1">
            <tr>
                <th>Numéro de Chambre</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['numerochambre']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['datedebut']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['datefin']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['etatreserver']); ?></td>
                    <td>
                        <form method="POST" action="modify_reservation.php">
                            <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['idreservation']); ?>">
                            <button type="submit" name="action" value="cancel">Annuler</button>
                            <button type="submit" name="action" value="modify">Modifier</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
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
