-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Dim 04 Janvier 2026 à 15:20
-- Version du serveur :  10.1.16-MariaDB
-- Version de PHP :  7.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `salon_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Structure de la table `lieux`
--

CREATE TABLE `lieux` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `ville` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `lieux`
--

INSERT INTO `lieux` (`id`, `nom`, `ville`) VALUES
(1, 'Agence Bali (Niveau KOE)', 'Douala'),
(2, 'Agence Bastos', 'Yaoundé'),
(3, 'Metrollex Logbessou', 'Douala');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `client_nom` varchar(100) NOT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_tel` varchar(20) NOT NULL,
  `service_id` int(11) NOT NULL,
  `lieu` varchar(100) DEFAULT NULL,
  `date_rdv` date NOT NULL,
  `heure_rdv` time NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `message` text,
  `notes_internes` text,
  `statut_rdv` varchar(20) DEFAULT 'PRÉVU',
  `statut_paiement` varchar(50) DEFAULT 'EN ATTENTE',
  `reference_paiement` varchar(100) DEFAULT NULL,
  `token_annulation` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `reservations`
--

INSERT INTO `reservations` (`id`, `client_nom`, `client_email`, `client_tel`, `service_id`, `lieu`, `date_rdv`, `heure_rdv`, `date_creation`, `message`, `notes_internes`, `statut_rdv`, `statut_paiement`, `reference_paiement`, `token_annulation`) VALUES
(1, 'Jazzy', NULL, '676399179', 1, NULL, '2026-01-12', '14:15:00', '2026-01-03 13:46:14', NULL, NULL, 'PRÉVU', 'EN ATTENTE', NULL, NULL),
(2, 'Jazzy', NULL, '676399179', 7, NULL, '2026-01-24', '19:07:00', '2026-01-03 14:01:41', NULL, NULL, 'ANNULÉ', 'EN ATTENTE', NULL, NULL),
(3, 'Jazzy', NULL, '+237 676399177', 1, 'Agence Bali (Niveau KOE)', '2026-01-24', '16:12:00', '2026-01-03 16:07:04', NULL, NULL, 'TERMINÉ', 'EN ATTENTE', NULL, NULL),
(4, 'Jazzy', NULL, '+237 698745123', 1, 'Agence Bali (Niveau KOE)', '2026-01-24', '17:15:00', '2026-01-04 13:15:48', NULL, NULL, 'TERMINÉ', 'EN ATTENTE', NULL, NULL),
(5, 'donald', NULL, '+237 688521475', 6, 'Agence Bastos', '2026-02-01', '13:33:00', '2026-01-04 13:30:11', NULL, NULL, 'PRÉVU', 'EN ATTENTE', NULL, NULL),
(6, 'dhtf', NULL, '+237 677989809', 5, 'Metrollex Logbessou', '2026-02-07', '14:16:00', '2026-01-04 14:11:57', NULL, NULL, 'PRÉVU', 'ATTENTE WHATSAPP', 'OZ-9214', 'c39397035ff19cb02ab6090202d5adad');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text,
  `duree` int(11) NOT NULL,
  `prix` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `services`
--

INSERT INTO `services` (`id`, `nom`, `description`, `duree`, `prix`) VALUES
(1, 'Coiffure Simple', 'Coupe homme/femme classique', 30, '1500.00'),
(2, 'Coiffure Complète', 'Coupe + Black + Soins + Séchage', 60, '2000.00'),
(3, 'Soins de Barbe', 'Taille, traçage et hydratation', 30, '2500.00'),
(4, 'Coiffure Marié(e)', 'Prestation d''exception pour le jour J', 120, '5000.00'),
(5, 'Soins du Visage', 'Gommage, vapeur et masque éclat', 45, '3000.00'),
(6, 'Soins Corporels', 'Massage et gommage corps', 60, '5000.00'),
(7, 'Pédicure / Manucure', 'Soins complets des ongles', 45, '2500.00'),
(8, 'Blanchiment Dentaire', 'Sourire éclatant garanti', 45, '5000.00'),
(9, 'Teinture / Coloration', 'Coloration professionnelle', 60, '3000.00'),
(10, 'Formation Pro', 'Module complet de formation coiffure (Certification Oz)', 0, '50000.00');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `lieux`
--
ALTER TABLE `lieux`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `lieux`
--
ALTER TABLE `lieux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
