<?php
include 'db_connect.php'; // Connexion à la base de données
session_start(); // Démarrer la session

// Rediriger les utilisateurs non connectés vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$idClient = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationId = $_POST['reservation_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$reservationId || !$action) {
        echo "<p style='color: red;'>Informations invalides.</p>";
        exit();
    }

    try {
        if ($action === 'cancel') {
            // Annuler une réservation
            $stmt = $pdo->prepare("SELECT idchambre FROM Reservation WHERE idreservation = ? AND idclient = ?");
            $stmt->execute([$reservationId, $idClient]);
            $idChambre = $stmt->fetchColumn();

            if ($idChambre) {
                // Supprimer la réservation et libérer la chambre
                $stmt = $pdo->prepare("DELETE FROM Reservation WHERE idreservation = ? AND idclient = ?");
                $stmt->execute([$reservationId, $idClient]);

                $stmt = $pdo->prepare("UPDATE Chambre SET etatchambre = 'free' WHERE idchambre = ?");
                $stmt->execute([$idChambre]);

                echo "<p style='color: green;'>Réservation annulée et chambre libérée.</p>";
            } else {
                echo "<p style='color: red;'>Aucune chambre associée à cette réservation.</p>";
            }
        } elseif ($action === 'modify') {
            // Modifier une réservation
            $newEndDate = $_POST['new_end_date'] ?? null;

            if (!$newEndDate) {
                echo "<h3>Modifier la réservation</h3>";
                echo "<form method='POST'>";
                echo "<input type='hidden' name='reservation_id' value='" . htmlspecialchars($reservationId) . "'>";
                echo "<label for='new_end_date'>Nouvelle date de fin :</label>";
                echo "<input type='date' name='new_end_date' required>";
                echo "<button type='submit' name='action' value='modify'>Enregistrer</button>";
                echo "</form>";
                exit();
            }

            // Vérifier que la nouvelle date de fin est valide
            $stmt = $pdo->prepare("SELECT datedebut FROM Reservation WHERE idreservation = ? AND idclient = ?");
            $stmt->execute([$reservationId, $idClient]);
            $startDate = $stmt->fetchColumn();

            if (strtotime($newEndDate) <= strtotime($startDate)) {
                echo "<p style='color: red;'>La date de fin doit être supérieure à la date de début.</p>";
                exit();
            }

            // Mettre à jour la réservation
            $stmt = $pdo->prepare("UPDATE Reservation SET datefin = ? WHERE idreservation = ? AND idclient = ?");
            $stmt->execute([$newEndDate, $reservationId, $idClient]);

            echo "<p style='color: green;'>Réservation modifiée avec succès.</p>";
        } else {
            echo "<p style='color: red;'>Action non reconnue.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
<a href="view_reservations.php">Retour à Mes Réservations</a>
