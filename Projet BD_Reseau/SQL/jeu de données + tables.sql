CREATE TABLE Hotel (
    idHotel CHAR(8) PRIMARY KEY,
    adresse VARCHAR(100) NOT NULL,
    telHotel CHAR(12) NOT NULL,
    mailHotel VARCHAR(100) NOT NULL
);
CREATE TABLE Employee (
    idEmploye CHAR(8) PRIMARY KEY,
    nomEmploye VARCHAR(40) NOT NULL,
    prenomEmploye VARCHAR(40) NOT NULL,
    telEmploye CHAR(12) NOT NULL,
    postEmploye VARCHAR(40) NOT NULL,
    idHotel CHAR(8),
    FOREIGN KEY (idHotel) REFERENCES Hotel(idHotel)
);
CREATE TABLE TypeChambre (
    idTypeChambre CHAR(8) PRIMARY KEY,
    nomType VARCHAR(40) NOT NULL,
    prix FLOAT NOT NULL
);
CREATE TABLE Chambre (
    idChambre CHAR(8) PRIMARY KEY,
    numeroChambre VARCHAR(4) NOT NULL,
    etatChambre CHAR(4) NOT NULL CHECK (etatChambre IN ('rsrv', 'free')),
    idTypeChambre CHAR(8),
    idHotel CHAR(8),
    FOREIGN KEY (idTypeChambre) REFERENCES TypeChambre(idTypeChambre),
    FOREIGN KEY (idHotel) REFERENCES Hotel(idHotel)
);
CREATE TABLE Client (
    idClient CHAR(10) PRIMARY KEY,
    nomClient VARCHAR(40) NOT NULL,
    prenomClient VARCHAR(40) NOT NULL,
    tel CHAR(12) NOT NULL,
    mail VARCHAR(100) NOT NULL
);
CREATE TABLE ClientVIP (
    idClient CHAR(10) PRIMARY KEY,
    serviceExclusif VARCHAR(40),
    codeVIP CHAR(12) NOT NULL,
    FOREIGN KEY (idClient) REFERENCES Client(idClient)
);
CREATE TABLE ClientRegulier (
    idClient CHAR(10) PRIMARY KEY,
    pointFidelite INT NOT NULL,
    promotion FLOAT NOT NULL CHECK (promotion > 0 AND promotion < 1),
    FOREIGN KEY (idClient) REFERENCES Client(idClient)
);
CREATE TABLE Reservation (
    idReservation CHAR(12) PRIMARY KEY,
    dateDebut DATE NOT NULL,
    dateFin DATE NOT NULL,
    typeReserver CHAR(3) NOT NULL CHECK (typeReserver IN ('srp', 'enl')),
    etatReserver CHAR(3) NOT NULL CHECK (etatReserver IN ('rsv', 'att', 'ann')),
    idClient CHAR(10),
    idChambre CHAR(8),
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idChambre) REFERENCES Chambre(idChambre)
);
CREATE TABLE Nettoyage (
    idNettoyage CHAR(8) PRIMARY KEY,
    typeNettoyage VARCHAR(20) NOT NULL,
    prixNettoyage FLOAT NOT NULL
);
CREATE TABLE Demande (
    idDemande CHAR(8) PRIMARY KEY,
    idNettoyage CHAR(8),
    idClient CHAR(10),
    dateDemander DATE NOT NULL,
    FOREIGN KEY (idNettoyage) REFERENCES Nettoyage(idNettoyage),
    FOREIGN KEY (idClient) REFERENCES Client(idClient)
);
CREATE TABLE Nettoyer (
    idNettoyage CHAR(8),
    idChambre CHAR(8),
    idEmploye CHAR(8),
    dateNettoyer DATE NOT NULL,
    PRIMARY KEY (idNettoyage, idChambre, idEmploye),
    FOREIGN KEY (idNettoyage) REFERENCES Nettoyage(idNettoyage),
    FOREIGN KEY (idChambre) REFERENCES Chambre(idChambre),
    FOREIGN KEY (idEmploye) REFERENCES Employee(idEmploye)
);

--                       Jeu de données                                 --

INSERT INTO Hotel (idHotel, adresse, telHotel, mailHotel)
VALUES 
('H001', '75 rue Edmond Jaloux, Cergy 95800', '+33156453236', 'jaloux.hotel@theKTM.com'),
('H002', '10 Avenue des Champs, Paris 75008', '+33144783344', 'champs.hotel@theKTM.com');

INSERT INTO Employee (idEmploye, nomEmploye, prenomEmploye, telEmploye, postEmploye, idHotel)
VALUES 
('E001', 'James', 'Lebron', '+33689564236', 'Femme de ménage', 'H001'),
('E002', 'Dupont', 'Marie', '+33612345678', 'Réceptionniste', 'H002');

INSERT INTO TypeChambre (idTypeChambre, nomType, prix)
VALUES 
('T001', 'Suite 2 personnes', 875.99),
('T002', 'Chambre simple', 200.50);

INSERT INTO Chambre (idChambre, numeroChambre, etatChambre, idTypeChambre, idHotel)
VALUES 
('C001', '101', 'free', 'T001', 'H001'),
('C002', '102', 'rsrv', 'T002', 'H001'),
('C003', '201', 'free', 'T001', 'H002');

INSERT INTO Client (idClient, nomClient, prenomClient, tel, mail)
VALUES 
('CL001', 'Dupont', 'Paul', '+33765426338', 'paul.dupont@gmail.com'),
('CL002', 'Martin', 'Sophie', '+33678965432', 'sophie.martin@yahoo.com');

INSERT INTO ClientVIP (idClient, serviceExclusif, codeVIP)
VALUES 
('CL001', 'Accès piscine', 'VIP123456789');

INSERT INTO ClientRegulier (idClient, pointFidelite, promotion)
VALUES 
('CL002', 75000, 0.30);

INSERT INTO Reservation (idReservation, dateDebut, dateFin, typeReserver, etatReserver, idClient, idChambre)
VALUES 
('R001', '2024-10-01', '2024-10-08', 'srp', 'rsv', 'CL001', 'C002'),
('R002', '2024-10-15', '2024-10-20', 'enl', 'att', 'CL002', 'C001');

INSERT INTO Nettoyage (idNettoyage, typeNettoyage, prixNettoyage)
VALUES 
('N001', 'Profond', 150.00),
('N002', 'Superficiel', 75.00);

INSERT INTO Demande (idDemande, idNettoyage, idClient, dateDemander)
VALUES 
('D001', 'N001', 'CL001', '2024-10-02'),
('D002', 'N002', 'CL002', '2024-10-15');

INSERT INTO Nettoyer (idNettoyage, idChambre, idEmploye, dateNettoyer)
VALUES 
('N001', 'C002', 'E001', '2024-10-09'),
('N002', 'C001', 'E002', '2024-10-21');
