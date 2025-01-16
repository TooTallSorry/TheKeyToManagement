
# **The Key to Management**

## **Description**
The Key to Management (TKM) est une application conçue pour faciliter la gestion des réservations d'une agence d'hôtellerie. Ce projet intègre une base de données centralisée, des échanges réseau fiables, une interface utilisateur intuitive et un site web robuste. L'objectif principal est d'améliorer l'expérience client tout en optimisant les processus pour les gestionnaires.

## **Fonctionnalités**
- Gestion des clients (enregistrement, mise à jour, suppression).
- Réservations d'hôtel avec suivi en temps réel.
- Gestion des accès aux chambres (cartes magétiques ou appareils mobiles).
- Scénarios adaptés pour les utilisateurs (clients, employés, administrateurs).
- Sécurité renforcée pour la gestion des identités et des données sensibles.
- Tableau de bord utilisateur pour consulter et modifier les réservations.
- Page administrateur pour gérer les chambres, les utilisateurs et les réservations.

## **Technologies Utilisées**
### **Backend**
- **Python** : Gestion du serveur backend avec la bibliothèque `psycopg2` pour interagir avec la base de données PostgreSQL.
- **PHP** : Utilisation de la bibliothèque `PDO` pour connecter le site web à la base de données.
- **PostgreSQL** : Base de données centralisée pour stocker toutes les informations relatives aux hôtels, chambres, clients et réservations.

### **Frontend**
- **HTML/CSS** : Structure et design des pages web.
- **JavaScript** : Interactivité et gestion dynamique des éléments.

### **Réseaux**
- **TCP** : Protocole choisi pour assurer des échanges réseau fiables et ordonnés entre client et serveur.

## **Installation et Exécution**
### **Prérequis**
- Python 3.x
- PostgreSQL
- Serveur Web (e.g., Apache ou Nginx)
- Netcat (pour les tests réseau)

### **Instructions**
1. **Clôner le répertoire du projet :**
   ```bash
   git clone <url_du_dépôt>
   ```
2. **Installer les dépendances Python :**
   ```bash
   pip install psycopg2
   ```
3. **Importer la base de données :**
   - Se connecter à PostgreSQL.
   - Exécuter le fichier SQL fourni (à localiser dans le répertoire `/sql`).
4. **Démarrer le serveur Python :**
   ```bash
   python server.py
   ```
5. **Configurer le site web :**
   - Déposer les fichiers PHP dans le répertoire racine de votre serveur web.
   - Modifier les paramètres de connexion à la base de données dans `db_connect.php`.

### **Tests**
- Utiliser Netcat pour simuler des interactions client-serveur.
- Tester les différents rôles : client, employé, administrateur.

## **Structure du Projet**
```
Projet_TKM/
|— backend/
|   |— server.py
|   |— db_connect.php
|— frontend/
|   |— index.html
|   |— dashboard.html
|— sql/
|   |— schema.sql
|   |— data.sql
|— tests/
    |— netcat_tests.txt
```

## **Contributeurs**
- CHEBALLAH Jawed
- ANAGONOU Pirès
- HUANG Yanmo

## **Licence**
Ce projet est sous licence MIT. Vous êtes libre de l'utiliser, de le modifier et de le redistribuer, à condition de mentionner les auteurs originaux.

---
