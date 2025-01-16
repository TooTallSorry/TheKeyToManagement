<?php
include 'db_connect.php';
session_start();

// Mot de passe statique pour protéger la page admin
define('ADMIN_PASSWORD', '123456789');

// Vérification du mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        setcookie('admin_access', 'granted', time() + 3600); // Accès valide pendant 1 heure
        header('Location: indexAdmin.php');
        exit();
    } else {
        $error = 'Mot de passe incorrect.';
    }
}

if (!isset($_COOKIE['admin_access']) || $_COOKIE['admin_access'] !== 'granted') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion Admin</title>
    </head>
    <body>
        <h1>Connexion Admin</h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="admin_password">Mot de passe :</label>
            <input type="password" name="admin_password" required>
            <button type="submit">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// Variables pour les messages et les données
$message = "";
$chambres = [];
$typesChambres = [];
$hotels = [];
$reservationsList = [];
$searchResults = [];
$reservations = [];

// Liste des types de chambres
try {
    $stmt = $pdo->query("SELECT idtypechambre, nomtype FROM TypeChambre");
    $typesChambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des types de chambres : " . htmlspecialchars($e->getMessage());
}

// Liste des hôtels
try {
    $stmt = $pdo->query("SELECT idhotel, adresse FROM Hotel");
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des hôtels : " . htmlspecialchars($e->getMessage());
}

// Liste des chambres
try {
    $stmt = $pdo->query("
        SELECT Chambre.idchambre, Chambre.numerochambre, TypeChambre.nomtype AS typechambre, Chambre.etatchambre
        FROM Chambre
        LEFT JOIN TypeChambre ON Chambre.idtypechambre = TypeChambre.idtypechambre
    ");
    $chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des chambres : " . htmlspecialchars($e->getMessage());
}

// Liste des réservations globales
try {
    $stmt = $pdo->query("
        SELECT Reservation.idreservation, Client.nomclient, Client.prenomclient, Chambre.numerochambre, Reservation.datedebut, Reservation.datefin
        FROM Reservation
        JOIN Client ON Reservation.idclient = Client.idclient
        JOIN Chambre ON Reservation.idchambre = Chambre.idchambre
    ");
    $reservationsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des réservations : " . htmlspecialchars($e->getMessage());
}

// Création d'une chambre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_room') {
    $idChambre = $_POST['idchambre'] ?? null;
    $numeroChambre = $_POST['numerochambre'] ?? null;
    $etatChambre = $_POST['etatchambre'] ?? null;
    $idTypeChambre = $_POST['idtypechambre'] ?? null;
    $idHotel = $_POST['idhotel'] ?? null;

    if ($idChambre && $numeroChambre && $etatChambre && $idTypeChambre && $idHotel) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Chambre (idchambre, numerochambre, etatchambre, idtypechambre, idhotel)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$idChambre, $numeroChambre, $etatChambre, $idTypeChambre, $idHotel]);
            $message = "Chambre créée avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la création de la chambre : " . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = "Veuillez remplir tous les champs pour créer une chambre.";
    }
}

// Recherche d'un client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_client') {
    $searchValue = $_POST['search_value'] ?? null;

    if ($searchValue) {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM Client 
                WHERE idclient = :search 
                   OR LOWER(nomclient) LIKE LOWER(:searchLike)
                   OR LOWER(prenomclient) LIKE LOWER(:searchLike)
                   OR LOWER(mail) LIKE LOWER(:searchLike)
                   OR tel LIKE :search
            ");
            $stmt->execute([
                ':search' => $searchValue,
                ':searchLike' => "%$searchValue%"
            ]);
            $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($searchResults)) {
                $idClient = $searchResults[0]['idclient'];
                $stmt = $pdo->prepare("
                    SELECT Reservation.idreservation, Chambre.numerochambre, Reservation.datedebut, Reservation.datefin 
                    FROM Reservation
                    JOIN Chambre ON Reservation.idchambre = Chambre.idchambre
                    WHERE Reservation.idclient = ?
                ");
                $stmt->execute([$idClient]);
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $message = "Aucun client trouvé.";
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de la recherche : " . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = "Veuillez entrer une valeur de recherche.";
    }
}

// Suppression d'une réservation spécifique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_reservation') {
    $idReservation = $_POST['idreservation'] ?? null;

    if ($idReservation) {
        try {
            // Récupération de l'ID de la chambre liée à la réservation
            $stmt = $pdo->prepare("SELECT idchambre FROM Reservation WHERE idreservation = ?");
            $stmt->execute([$idReservation]);
            $idChambre = $stmt->fetchColumn();

            // Mettre l'état de la chambre à 'free'
            if ($idChambre) {
                $stmt = $pdo->prepare("UPDATE Chambre SET etatchambre = 'free' WHERE idchambre = ?");
                $stmt->execute([$idChambre]);
            }

            // Suppression de la réservation
            $stmt = $pdo->prepare("DELETE FROM Reservation WHERE idreservation = ?");
            $stmt->execute([$idReservation]);

            $message = "Réservation supprimée avec succès et la chambre est de nouveau disponible.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression de la réservation : " . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = "Aucune réservation sélectionnée.";
    }
}


// Suppression d'un client et de toutes ses réservations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_client') {
    $idClient = $_POST['idclient'] ?? null;

    if ($idClient) {
        try {
            // Suppression des réservations associées au client
            $stmt = $pdo->prepare("DELETE FROM Reservation WHERE idclient = ?");
            $stmt->execute([$idClient]);

            // Suppression du client
            $stmt = $pdo->prepare("DELETE FROM Client WHERE idclient = ?");
            $stmt->execute([$idClient]);

            $message = "Client et ses réservations supprimés avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression du client : " . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = "Aucun client sélectionné.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styleadmin.css">
    <title>Admin - Gestion des Chambres, Clients et Réservations</title>
</head>
<body>
    <h1>Page Admin - Gestion des Chambres, Clients et Réservations</h1>
    <p><?php echo htmlspecialchars($message); ?></p>

    <!-- Création d'une chambre -->
    <h2>Créer une nouvelle chambre</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create_room">
        <label for="idchambre">ID Chambre :</label>
        <input type="text" name="idchambre" maxlength="8" required><br>
        <label for="numerochambre">Numéro de chambre :</label>
        <input type="text" name="numerochambre" maxlength="4" required><br>
        <label for="etatchambre">État de la chambre :</label>
        <select name="etatchambre" required>
            <option value="free">Libre</option>
            <option value="used">Occupée</option>
        </select><br>
        <label for="idtypechambre">Type de chambre :</label>
        <select name="idtypechambre" required>
            <?php foreach ($typesChambres as $type): ?>
                <option value="<?php echo htmlspecialchars($type['idtypechambre']); ?>">
                    <?php echo htmlspecialchars($type['nomtype']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <label for="idhotel">Hôtel :</label>
        <select name="idhotel" required>
            <?php foreach ($hotels as $hotel): ?>
                <option value="<?php echo htmlspecialchars($hotel['idhotel']); ?>">
                    <?php echo htmlspecialchars($hotel['adresse']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit">Créer</button>
    </form>

    <!-- Liste des chambres -->
    <h2>Liste des chambres</h2>
    <table border="1">
        <tr>
            <th>ID Chambre</th>
            <th>Numéro</th>
            <th>Type</th>
            <th>État</th>
        </tr>
        <?php foreach ($chambres as $chambre): ?>
            <tr>
                <td><?php echo htmlspecialchars($chambre['idchambre']); ?></td>
                <td><?php echo htmlspecialchars($chambre['numerochambre']); ?></td>
                <td><?php echo htmlspecialchars($chambre['typechambre']); ?></td>
                <td><?php echo htmlspecialchars($chambre['etatchambre']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Recherche d'un client -->
    <h2>Rechercher un client</h2>
    <form method="POST">
        <input type="hidden" name="action" value="search_client">
        <label for="search_value">Rechercher par ID, nom, prénom, email ou téléphone :</label>
        <input type="text" name="search_value" required>
        <button type="submit">Rechercher</button>
    </form>

    <?php if (!empty($searchResults)): ?>
        <h3>Informations du client</h3>
        <table border="1">
            <tr>
                <th>ID Client</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($searchResults as $client): ?>
                <tr>
                    <td><?php echo htmlspecialchars($client['idclient']); ?></td>
                    <td><?php echo htmlspecialchars($client['nomclient']); ?></td>
                    <td><?php echo htmlspecialchars($client['prenomclient']); ?></td>
                    <td><?php echo htmlspecialchars($client['mail']); ?></td>
                    <td><?php echo htmlspecialchars($client['tel']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_client">
                            <input type="hidden" name="idclient" value="<?php echo htmlspecialchars($client['idclient']); ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Réservations du client</h3>
        <table border="1">
            <tr>
                <th>ID Réservation</th>
                <th>Numéro Chambre</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['idreservation']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['numerochambre']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['datedebut']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['datefin']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_reservation">
                            <input type="hidden" name="idreservation" value="<?php echo htmlspecialchars($reservation['idreservation']); ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Liste globale des réservations -->
    <h2>Toutes les réservations</h2>
    <table border="1">
        <tr>
            <th>ID Réservation</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Numéro Chambre</th>
            <th>Date Début</th>
            <th>Date Fin</th>
        </tr>
        <?php foreach ($reservationsList as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['idreservation']); ?></td>
                <td><?php echo htmlspecialchars($reservation['nomclient']); ?></td>
                <td><?php echo htmlspecialchars($reservation['prenomclient']); ?></td>
                <td><?php echo htmlspecialchars($reservation['numerochambre']); ?></td>
                <td><?php echo htmlspecialchars($reservation['datedebut']); ?></td>
                <td><?php echo htmlspecialchars($reservation['datefin']); ?></td>
            </tr>
        <?php endforeach; ?>
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
