# COMMENTAIRES EN TETE :
# version phpmyadmin
# client
# date finale de création
# version du serveur : MariaDB
# version php 

# définir toutes les tables : commentaires sur ce qu'elle fait
CREATE TABLE Departements (
  departement varchar(3) NOT NULL, 
  pays        varchar(25) NOT NULL, 
  PRIMARY KEY (departement, pays)); 

# définir
CREATE TABLE CodesPostaux (
  villes                 varchar(150) NOT NULL, 
  Departementsdepartement varchar(3), 
  codepostal             char(5), 
  Departementspays       varchar(25), 
  PRIMARY KEY (villes),
  FOREIGN KEY (Departementsdepartement, Departementspays) REFERENCES Departements (departement, pays) ON DELETE SET NULL ON UPDATE CASCADE );

# définir
CREATE TABLE Adresses (
  idAdresse            int(8) NOT NULL AUTO_INCREMENT,
  numero               varchar(6), 
  rue                  varchar(100) NOT NULL, 
  CodesPostauxvilles    varchar(150), 
  PRIMARY KEY (idAdresse),
  FOREIGN KEY (CodesPostauxvilles) REFERENCES CodesPostaux (villes) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX (CodesPostauxvilles)); 

# définir 
CREATE TABLE Patients (
  numSS             char(15) NOT NULL, 
  nom               varchar(25) NOT NULL, 
  prenom            varchar(25) NOT NULL, 
  dateNaissance     date NOT NULL, 
  telephone         varchar(15), 
  mail              varchar(60), 
  sexe              char(1) NOT NULL, 
  taille_cm         int(3), 
  poids_kg          int(3), 
  commentaires      text, 
  AdressesidAdresse int(8), 
  PRIMARY KEY (numSS), 
  FOREIGN KEY (AdressesidAdresse) REFERENCES Adresses (idAdresse) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX (nom, prenom));

# définir 
CREATE TABLE CompteUtilisateurs (
  idEmploye varchar(10) NOT NULL, 
  passwd    varchar(255) NOT NULL, 
  PRIMARY KEY (idEmploye));

# définir 
CREATE TABLE LocalisationServices (
  idLocalisation varchar(10) NOT NULL, 
  batiment       varchar(15) NOT NULL, 
  aile           varchar(10), 
  etage          varchar(2), 
  porte          varchar(5), 
  PRIMARY KEY (idLocalisation));

# définir 
CREATE TABLE Services (
  nomService                  varchar(20) NOT NULL, 
  telephone                   varchar(15), 
  mail                        varchar(60), 
  horaire_ouverture          varchar(5), # Format 08:00
  horaire_fermeture          varchar(5), # Format 18:00
  LocalisationServicesidLocalisation varchar(10), 
  PRIMARY KEY (nomService),
  FOREIGN KEY (LocalisationServicesidLocalisation) REFERENCES LocalisationServices (idLocalisation) ON DELETE SET NULL ON UPDATE CASCADE );

# définir 
CREATE TABLE Employes (
  CompteUtilisateursidEmploye varchar(10) NOT NULL, 
  nom                         varchar(25) NOT NULL, 
  prenom                      varchar(25) NOT NULL, 
  telephone                   varchar(15), 
  mail                        varchar(60), 
  ServicesnomService          varchar(20), 
  AdressesidAdresse           int(8), 
  PRIMARY KEY (CompteUtilisateursidEmploye), 
  FOREIGN KEY (ServicesnomService) REFERENCES Services (nomService) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (AdressesidAdresse) REFERENCES Adresses (idAdresse) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (CompteUtilisateursidEmploye) REFERENCES CompteUtilisateurs (idEmploye) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (ServicesnomService),
  INDEX (nom));

# définir 
CREATE TABLE ChefServices (
  EmployesCompteUtilisateursidEmploye varchar(10) NOT NULL, 
  ServicesnomService                  varchar(20) NOT NULL, 
  PRIMARY KEY (EmployesCompteUtilisateursidEmploye,ServicesnomService),
  FOREIGN KEY (ServicesnomService) REFERENCES Services (nomService) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (EmployesCompteUtilisateursidEmploye) REFERENCES Employes (CompteUtilisateursidEmploye) ON DELETE CASCADE ON UPDATE CASCADE);

# définir 
CREATE TABLE Pathologies (
  idPatho       varchar(100) NOT NULL, 
  nomPathologie varchar(100) NOT NULL, 
  origine       varchar(35), 
  souche        varchar(35), 
  developpement varchar(10), 
  transmission  varchar(35), 
  precautions   text, 
  PRIMARY KEY (idPatho), 
  INDEX (nomPathologie));

# définir 
CREATE TABLE Interventions (
  idIntervention                       varchar(50) NOT NULL, 
  acte                                 varchar(15) NOT NULL, 
  ServicesnomService                   varchar(20),  
  PRIMARY KEY (idIntervention),
  FOREIGN KEY (ServicesnomService) REFERENCES Services (nomService) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX (ServicesnomService));

# définir
CREATE TABLE Tarifications (
  InterventionsidIntervention varchar(50) NOT NULL, 
  tarif                       float UNSIGNED, 
  PRIMARY KEY (InterventionsidIntervention),
  FOREIGN KEY (InterventionsidIntervention) REFERENCES Interventions (idIntervention) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (InterventionsidIntervention));

# définir 
CREATE TABLE CreneauxInterventions (
  date_rdv                            date NOT NULL, 
  heure                               time NOT NULL, 
  InterventionsidIntervention         varchar(50), 
  niveauUrgence                       tinyint(1) UNSIGNED NOT NULL DEFAULT 0, 
  statut                              varchar(10) NOT NULL DEFAULT 'test', 
  pathologie                          varchar(100), 
  commentaires                        text, 
  VerifCoherencePathologieRef         varchar(100), 
  PatientsnumSS                        char(15), 
  EmployesCompteUtilisateursidEmploye varchar(10), 
  PRIMARY KEY (date_rdv, heure),  
  FOREIGN KEY (InterventionsidIntervention) REFERENCES Interventions (idIntervention) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (PatientsnumSS) REFERENCES Patients (numSS) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (EmployesCompteUtilisateursidEmploye) REFERENCES Employes (CompteUtilisateursidEmploye) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX (InterventionsidIntervention), 
  INDEX (PatientsnumSS), 
  INDEX (EmployesCompteUtilisateursidEmploye),
  INDEX (pathologie));

# définir 
CREATE TABLE InterventionsPatho (
  PathologiesidPatho          varchar(100) NOT NULL, 
  InterventionsidIntervention varchar(50) NOT NULL, 
  niveauUrgenceMax            tinyint(1), 
  niveauUrgenceMin            tinyint(1), 
  PRIMARY KEY (PathologiesidPatho, InterventionsidIntervention),
  FOREIGN KEY (InterventionsidIntervention) REFERENCES Interventions (idIntervention) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (PathologiesidPatho) REFERENCES Pathologies (idPatho) ON DELETE CASCADE ON UPDATE CASCADE );

# Premier insert dans la table du futur admin de la base de données
INSERT INTO `CompteUtilisateurs` (`idEmploye`, `passwd`) VALUES ('Admin', '$2y$10$aXQcmkz0JlTy3kX/gOFb3u.K5VhyaPs5V5kxMGxu1np2mFuw//SfC') ; 
