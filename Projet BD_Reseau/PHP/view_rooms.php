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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idchambre'])) {
    $idChambre = $_POST['idchambre'];

    try {
        // Générer un nouvel ID de réservation
        $stmt = $pdo->query("SELECT idreservation FROM Reservation ORDER BY idreservation DESC LIMIT 1");
        $lastId = $stmt->fetchColumn();
        $newId = $lastId ? 'R' . str_pad(substr($lastId, 1) + 1, 3, '0', STR_PAD_LEFT) : 'R001';

        // Ajouter une nouvelle réservation
        $stmt = $pdo->prepare("INSERT INTO Reservation (idreservation, datedebut, datefin, typereserver, etatreserver, idclient, idchambre)
                               VALUES (?, CURRENT_DATE, CURRENT_DATE + INTERVAL '7 days', 'srp', 'rsv', ?, ?)");
        $stmt->execute([$newId, $idClient, $idChambre]);

        // Mettre à jour l'état de la chambre
        $stmt = $pdo->prepare("UPDATE Chambre SET etatchambre = 'rsrv' WHERE idchambre = ?");
        $stmt->execute([$idChambre]);

        echo "<p style='color: green;'>Réservation réussie !</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Chambres Disponibles</title>
</head>
<body>
    <h1>Chambres Disponibles</h1>
    <table border="1">
        <tr>
            <th>Numéro</th>
            <th>Type</th>
            <th>Prix</th>
            <th>Action</th>
        </tr>
        <?php
        $stmt = $pdo->query("
            SELECT 
                Chambre.idchambre, 
                Chambre.numerochambre, 
                TypeChambre.nomtype AS typechambre, 
                TypeChambre.prix, 
                Chambre.etatchambre 
            FROM Chambre 
            LEFT JOIN TypeChambre ON Chambre.idtypechambre = TypeChambre.idtypechambre
            WHERE Chambre.etatchambre = 'free'
        ");
        while ($room = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($room['numerochambre']); ?></td>
                <td><?php echo isset($room['typechambre']) ? htmlspecialchars($room['typechambre']) : 'Non spécifié'; ?></td>
                <td><?php echo htmlspecialchars($room['prix']); ?> €</td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="idchambre" value="<?php echo htmlspecialchars($room['idchambre']); ?>">
                        <button type="submit">Réserver</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
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
