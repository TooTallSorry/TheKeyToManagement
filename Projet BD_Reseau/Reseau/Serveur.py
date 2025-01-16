import socket
import psycopg2
import logging
import time

# Configurations globales
HOST = "127.0.0.1"
PORT = 8008
BUFFER_SIZE = 1024
INACTIVITY_TIMEOUT = 60  # Temps en secondes avant fermeture pour inactivité

# Configurer les logs pour le serveur
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s', filename='server.log')

def handle_client(conn, addr, cursor):
    """Gestion des interactions avec un client ou un employé."""
    try:
        logging.info(f"Nouvelle connexion de {addr}")
        conn.settimeout(5)

        # Réception des données
        try:
            data = conn.recv(BUFFER_SIZE).decode("utf-8").strip()
            if not data:
                raise ValueError("Données vides reçues du client")
            logging.info(f"Données reçues : {data}")
        except socket.timeout:
            logging.warning(f"Client {addr} n'a pas répondu dans les délais. Connexion fermée.")
            conn.close()
            return
        except (socket.error, ValueError) as e:
            logging.error(f"Erreur lors de la réception des données du client {addr} : {str(e)}")
            conn.close()
            return

        # Analyse et validation des données
        try:
            userid, room_number, idreservation, role = data.split(',')
            if len(userid) > 10 or len(room_number) > 4 or len(idreservation) > 12 or len(role) > 10:
                raise ValueError("Données trop longues")
        except ValueError as e:
            logging.error(f"Données mal formatées ou invalides de la part du client {addr} : {str(e)}")
            conn.sendall("Erreur : Données invalides.".encode("utf-8"))
            return

        # Gestion des rôles
        if role == "admin":
            # Administrateur
            cursor.execute("""
                SELECT idemploye 
                FROM employee 
                WHERE idemploye = %s
            """, (userid,))
            result = cursor.fetchone()

            if result:
                response = f"Bienvenue administrateur {userid} !"
            else:
                response = "Erreur : Identifiant administrateur incorrect."

        elif role == "client":
            # Client
            cursor.execute("""
                SELECT chambre.numerochambre, reservation.idreservation
                FROM reservation
                JOIN chambre ON reservation.idchambre = chambre.idchambre
                WHERE reservation.idclient = %s
                  AND reservation.etatreserver = 'rsv'
            """, (userid,))
            result = cursor.fetchone()

            if result:
                db_room_number, db_id_reservation = result
                if db_room_number == room_number and db_id_reservation == idreservation:
                    response = f"Bienvenue client {userid} !"
                else:
                    response = "Erreur : Numéro de chambre ou réservation incorrect."
            else:
                response = "Erreur : Aucun enregistrement trouvé pour cet identifiant client."

        elif role == "employee":
            # Employé (Nettoyage)
            cursor.execute("""
                SELECT nettoyer.idnettoyage, chambre.numerochambre, employee.idemploye
                FROM nettoyer
                JOIN chambre ON nettoyer.idchambre = chambre.idchambre
                JOIN employee ON nettoyer.idemploye = employee.idemploye
                WHERE nettoyer.idnettoyage = %s AND chambre.numerochambre = %s AND employee.idemploye = %s
            """, (idreservation, room_number, userid))
            result = cursor.fetchone()

            if result:
                response = f"Bienvenue employé {userid} ! Tâche de nettoyage autorisée pour la chambre {room_number}."
            else:
                response = "Erreur : Aucun enregistrement trouvé ou données incorrectes."

        else:
            response = "Erreur : Rôle non reconnu. Les rôles valides sont : admin, client, employee."

        # Envoi de la réponse au client
        conn.sendall(response.encode("utf-8"))
        logging.info(f"Réponse envoyée à {addr} : {response}")

    except Exception as e:
        logging.error(f"Erreur inattendue avec le client {addr} : {str(e)}")
        conn.sendall("Erreur : Problème dans la communication.".encode("utf-8"))
    finally:
        conn.close()
        logging.info(f"Connexion fermée avec {addr}")

def main():
    """Démarrer le serveur."""
    try:
        # Connexion à la base de données PostgreSQL
        conn_db = psycopg2.connect("host='postgresql-tktm.alwaysdata.net' dbname='tktm_bd' user='tktm' password='Pokemon0000!@'")
        cursor = conn_db.cursor()

        # Création du socket serveur
        server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        try:
            server_socket.bind((HOST, PORT))
        except OSError as e:
            logging.error(f"Port {PORT} déjà utilisé. Veuillez vérifier.")
            return

        server_socket.listen(5)
        logging.info(f"Serveur démarré sur {HOST}:{PORT}")

        last_activity_time = time.time()  # Enregistre l'heure de la dernière activité

        while True:
            # Vérification du temps d'inactivité
            current_time = time.time()
            if current_time - last_activity_time > INACTIVITY_TIMEOUT:
                logging.info("Fermeture du serveur après période d'inactivité.")
                break

            try:
                # Accepter une connexion client avec un timeout ajusté
                server_socket.settimeout(INACTIVITY_TIMEOUT - (current_time - last_activity_time))
                conn, addr = server_socket.accept()
                last_activity_time = time.time()  # Mise à jour de l'heure de la dernière activité
                handle_client(conn, addr, cursor)
            except socket.timeout:
                continue  # Aucun client ne s'est connecté, retourner à la boucle principale

    except psycopg2.Error as db_error:
        logging.error(f"Erreur base de données : {str(db_error)}")
    except Exception as e:
        logging.error(f"Erreur serveur : {str(e)}")
    finally:
        if conn_db:
            conn_db.close()
        server_socket.close()
        logging.info("Serveur arrêté.")

if __name__ == "__main__":
    main()
