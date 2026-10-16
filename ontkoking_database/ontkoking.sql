-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 09, 2026 at 09:20 AM
-- Server version: 10.6.23-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ontkoking`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorieen`
--

CREATE TABLE `categorieen` (
  `id` int(11) NOT NULL,
  `naam` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gebruikers`
--

CREATE TABLE `gebruikers` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `wachtwoord_hash` varchar(255) NOT NULL,
  `rol` enum('user','admin') NOT NULL DEFAULT 'user',
  `aangemaakt_op` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredienten`
--

CREATE TABLE `ingredienten` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `standaard_eenheid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recepten`
--

CREATE TABLE `recepten` (
  `id` int(11) NOT NULL,
  `titel` varchar(150) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `bereidingswijze` text DEFAULT NULL,
  `bereidingstijd_min` int(11) DEFAULT NULL,
  `afbeelding_url` varchar(255) DEFAULT NULL,
  `gebruiker_id` int(11) NOT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `aangemaakt_op` datetime NOT NULL DEFAULT current_timestamp(),
  `aangepast_op` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recept_ingredienten`
--

CREATE TABLE `recept_ingredienten` (
  `id` int(11) NOT NULL,
  `recept_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `hoeveelheid` float DEFAULT NULL,
  `eenheid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorieen`
--
ALTER TABLE `categorieen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `naam` (`naam`);

--
-- Indexes for table `gebruikers`
--
ALTER TABLE `gebruikers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ingredienten`
--
ALTER TABLE `ingredienten`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `naam` (`naam`);

--
-- Indexes for table `recepten`
--
ALTER TABLE `recepten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gebruiker_id` (`gebruiker_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Indexes for table `recept_ingredienten`
--
ALTER TABLE `recept_ingredienten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recept_id` (`recept_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorieen`
--
ALTER TABLE `categorieen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredienten`
--
ALTER TABLE `ingredienten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recepten`
--
ALTER TABLE `recepten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recept_ingredienten`
--
ALTER TABLE `recept_ingredienten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recepten`
--
ALTER TABLE `recepten`
  ADD CONSTRAINT `recepten_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recepten_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categorieen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recept_ingredienten`
--
ALTER TABLE `recept_ingredienten`
  ADD CONSTRAINT `recept_ingredienten_ibfk_1` FOREIGN KEY (`recept_id`) REFERENCES `recepten` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recept_ingredienten_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredienten` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
