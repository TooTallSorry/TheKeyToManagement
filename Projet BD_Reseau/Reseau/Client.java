package com.example.credentials;

import java.io.*;
import java.net.Socket;
import java.net.SocketTimeoutException;

public class Client {
    private String serverAddress;
    private int serverPort;

    // Constructeur
    public Client(String serverAddress, int serverPort) {
        this.serverAddress = serverAddress;
        this.serverPort = serverPort;
    }

    // Méthode pour envoyer les informations d'identification
    public void sendCredentials(UserCredentials credentials) {
        try (Socket socket = new Socket(serverAddress, serverPort)) {
            socket.setSoTimeout(5000); // Timeout de 5 secondes pour la lecture

            try (BufferedWriter writer = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream()));
                 BufferedReader reader = new BufferedReader(new InputStreamReader(socket.getInputStream()))) {

                System.out.println("Connexion au serveur établie.");

                // Format des données à envoyer
                String data = credentials.getFormattedCredentials();
                if (data.length() > 100) {
                    System.err.println("Erreur : Les données sont trop volumineuses pour être envoyées.");
                    return;
                }

                writer.write(data);
                writer.newLine();
                writer.flush();
                System.out.println("Données envoyées : " + data);

                // Lecture de la réponse du serveur
                try {
                    String response = reader.readLine();
                    if (response == null) {
                        System.err.println("Erreur : Le serveur a fermé la connexion.");
                    } else {
                        System.out.println("Réponse du serveur : " + response);
                    }
                } catch (SocketTimeoutException e) {
                    System.err.println("Erreur : Le serveur met trop de temps à répondre.");
                }

            }
        } catch (IOException e) {
            System.err.println("Erreur lors de la communication avec le serveur : " + e.getMessage());
        }
    }

    public static void main(String[] args) {
        // Paramètres du serveur
        String serverIP = "127.0.0.1";
        int serverPort = 8008;

        // Informations d'identification
        UserCredentials credentials = new UserCredentials("CL2807", "101", "R001", "client");

        // Envoi des données
        Client client = new Client(serverIP, serverPort);
        client.sendCredentials(credentials);
    }
}
