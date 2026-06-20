-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 20 juin 2026 à 22:51
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projetwe4a-si40`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id_avis` int(11) NOT NULL,
  `avis` varchar(255) NOT NULL,
  `note` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis_cours`
--

CREATE TABLE `avis_cours` (
  `id_avis` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversation`
--

CREATE TABLE `conversation` (
  `id_conv` int(11) NOT NULL,
  `id_eleve` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `id_cours` int(11) NOT NULL,
  `id_em` int(11) NOT NULL,
  `prix_heure` decimal(10,2) NOT NULL,
  `mode_cours` varchar(30) NOT NULL,
  `camera_obligatoire` tinyint(1) NOT NULL,
  `suivi` tinyint(1) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id_cours`, `id_em`, `prix_heure`, `mode_cours`, `camera_obligatoire`, `suivi`, `description`) VALUES
(19, 23, 10.00, 'présentiel', 0, 1, 'Leçon grammaire + vocabulaire');

-- --------------------------------------------------------

--
-- Structure de la table `enseignant_langue`
--

CREATE TABLE `enseignant_langue` (
  `id_el` int(11) NOT NULL,
  `id_em` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enseignant_langue`
--

INSERT INTO `enseignant_langue` (`id_el`, `id_em`) VALUES
(1, 21),
(1, 22),
(1, 23),
(2, 21),
(2, 22),
(2, 23);

-- --------------------------------------------------------

--
-- Structure de la table `enseignant_matiere`
--

CREATE TABLE `enseignant_matiere` (
  `id_em` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_matiere` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enseignant_matiere`
--

INSERT INTO `enseignant_matiere` (`id_em`, `id_utilisateur`, `id_matiere`) VALUES
(21, 19, 5),
(22, 19, 2),
(23, 19, 5);

-- --------------------------------------------------------

--
-- Structure de la table `langue`
--

CREATE TABLE `langue` (
  `id_langue` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `langue`
--

INSERT INTO `langue` (`id_langue`, `nom`) VALUES
(1, 'francais'),
(2, 'anglais'),
(3, 'allemand'),
(4, 'russe'),
(5, 'chinois'),
(6, 'italien'),
(7, 'espagnol'),
(8, 'japonais'),
(9, 'koréen');

-- --------------------------------------------------------

--
-- Structure de la table `matiere`
--

CREATE TABLE `matiere` (
  `id_matiere` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `matiere`
--

INSERT INTO `matiere` (`id_matiere`, `nom`) VALUES
(1, 'maths'),
(2, 'info'),
(3, 'svt'),
(4, 'histoire'),
(5, 'français'),
(6, 'physique-chimie'),
(7, 'anglais');

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_conv` int(11) NOT NULL,
  `id_redacteur` int(11) NOT NULL,
  `heure` datetime NOT NULL DEFAULT current_timestamp(),
  `contenu` text NOT NULL,
  `lu` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rdv`
--

CREATE TABLE `rdv` (
  `id_rdv` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_eleve` int(11) NOT NULL,
  `date_heure` datetime NOT NULL,
  `duree` int(11) NOT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `commentaire` varchar(255) DEFAULT NULL,
  `est_valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateurs` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `rang` int(2) NOT NULL,
  `theme` varchar(10) DEFAULT 'light',
  `presentation` varchar(500) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateurs`, `email`, `nom`, `prenom`, `mdp`, `rang`, `theme`, `presentation`) VALUES
(10, 'admin@utbm.fr', 'test', 'admin', '$2y$10$s4lCG214kokOLyGECKfjJOnnhAmLu/.H1rO2mnWyRzYuO9bNKI4dO', 2, 'light', ''),
(19, 'prof@utbm.fr', 'test', 'prof', '$2y$10$0PnM2kq0sfy776mOFfx38eMbLVSIYXa8MtNk5jRdxcZdLpfJAS3y.', 1, 'light', ''),
(21, 'etudiant@utbm.fr', 'test', 'etudiant', '$2y$10$bnLT0wnrYwA105WPNNoJPeKy0C2hNaPXDwgUcHIztqYp4/2XAyVXq', 0, 'light', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id_avis`);

--
-- Index pour la table `avis_cours`
--
ALTER TABLE `avis_cours`
  ADD PRIMARY KEY (`id_avis`,`id_cours`),
  ADD KEY `fk_cascade_avis_cours_pont` (`id_cours`);

--
-- Index pour la table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id_conv`),
  ADD KEY `fk_conv_cours` (`id_cours`),
  ADD KEY `fk_conv_eleve` (`id_eleve`);

--
-- Index pour la table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`),
  ADD KEY `fk_cascade_cours_em` (`id_em`);

--
-- Index pour la table `enseignant_langue`
--
ALTER TABLE `enseignant_langue`
  ADD PRIMARY KEY (`id_el`,`id_em`),
  ADD KEY `fk_cascade_el_em` (`id_em`);

--
-- Index pour la table `enseignant_matiere`
--
ALTER TABLE `enseignant_matiere`
  ADD PRIMARY KEY (`id_em`),
  ADD KEY `fk_cascade_em_matiere` (`id_matiere`),
  ADD KEY `fk_cascade_em_utilisateurs` (`id_utilisateur`);

--
-- Index pour la table `langue`
--
ALTER TABLE `langue`
  ADD PRIMARY KEY (`id_langue`);

--
-- Index pour la table `matiere`
--
ALTER TABLE `matiere`
  ADD PRIMARY KEY (`id_matiere`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `fk_msg_conversation` (`id_conv`),
  ADD KEY `fk_msg_redacteur` (`id_redacteur`);

--
-- Index pour la table `rdv`
--
ALTER TABLE `rdv`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `fk_cascade_rdv_utilisateurs` (`id_eleve`),
  ADD KEY `fk_rdv_cours` (`id_cours`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateurs`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id_avis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id_conv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `cours`
--
ALTER TABLE `cours`
  MODIFY `id_cours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `enseignant_matiere`
--
ALTER TABLE `enseignant_matiere`
  MODIFY `id_em` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `langue`
--
ALTER TABLE `langue`
  MODIFY `id_langue` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `matiere`
--
ALTER TABLE `matiere`
  MODIFY `id_matiere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `rdv`
--
ALTER TABLE `rdv`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateurs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis_cours`
--
ALTER TABLE `avis_cours`
  ADD CONSTRAINT `fk_cascade_avis_cours_pont` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cascade_avis_pont` FOREIGN KEY (`id_avis`) REFERENCES `avis` (`id_avis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `fk_conv_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_conv_eleve` FOREIGN KEY (`id_eleve`) REFERENCES `utilisateurs` (`id_utilisateurs`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `fk_cascade_cours_em` FOREIGN KEY (`id_em`) REFERENCES `enseignant_matiere` (`id_em`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `enseignant_langue`
--
ALTER TABLE `enseignant_langue`
  ADD CONSTRAINT `fk_cascade_el_em` FOREIGN KEY (`id_em`) REFERENCES `enseignant_matiere` (`id_em`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cascade_el_langue` FOREIGN KEY (`id_el`) REFERENCES `langue` (`id_langue`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `enseignant_matiere`
--
ALTER TABLE `enseignant_matiere`
  ADD CONSTRAINT `fk_cascade_em_matiere` FOREIGN KEY (`id_matiere`) REFERENCES `matiere` (`id_matiere`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cascade_em_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateurs`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_msg_conversation` FOREIGN KEY (`id_conv`) REFERENCES `conversation` (`id_conv`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateurs` (`id_utilisateurs`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `rdv`
--
ALTER TABLE `rdv`
  ADD CONSTRAINT `fk_cascade_rdv_utilisateurs` FOREIGN KEY (`id_eleve`) REFERENCES `utilisateurs` (`id_utilisateurs`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rdv_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
