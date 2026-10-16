-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 16, 2026 at 09:35 AM
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
-- Table structure for table `boodschappenlijst`
--

CREATE TABLE `boodschappenlijst` (
                                     `id` int(11) NOT NULL,
                                     `gebruiker_id` int(11) NOT NULL,
                                     `ingredient_id` int(11) NOT NULL,
                                     `hoeveelheid` float DEFAULT NULL,
                                     `eenheid` varchar(20) DEFAULT NULL,
                                     `afgevinkt` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorieen`
--

CREATE TABLE `categorieen` (
                               `id` int(11) NOT NULL,
                               `naam` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `categorieen`
--

INSERT INTO `categorieen` (`id`, `naam`) VALUES
                                             (6, 'Dessert'),
                                             (3, 'Diner'),
                                             (9, 'Hoofdgerecht'),
                                             (2, 'Lunch'),
                                             (1, 'Ontbijt'),
                                             (4, 'Pasta'),
                                             (8, 'Snack'),
                                             (7, 'Soep'),
                                             (5, 'Vegetarisch');

-- --------------------------------------------------------

--
-- Table structure for table `favorieten`
--

CREATE TABLE `favorieten` (
                              `gebruiker_id` int(11) NOT NULL,
                              `recept_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `favorieten`
--

INSERT INTO `favorieten` (`gebruiker_id`, `recept_id`) VALUES
                                                           (5, 1),
                                                           (5, 2);

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

--
-- Dumping data for table `gebruikers`
--

INSERT INTO `gebruikers` (`id`, `naam`, `email`, `wachtwoord_hash`, `rol`, `aangemaakt_op`) VALUES
                                                                                                (1, 'Admin', 'admin@ontkoking.nl', 'x', 'admin', '2026-10-13 11:24:29'),
                                                                                                (2, 'Sanne de Vries', 'sanne@test.nl', 'x', 'user', '2026-10-13 11:24:29'),
                                                                                                (3, 'Mo Bakker', 'mo@test.nl', 'x', 'user', '2026-10-13 11:24:29'),
                                                                                                (4, 'Lisa Jansen', 'lisa@test.nl', 'x', 'user', '2026-10-13 11:24:29'),
                                                                                                (5, 'djalla123', 'djalla123@gmail.com', '$2y$10$Dw934j0AexOpoShrD30mUe3FflQiVZ5rOZZ2trVbjbUpwdlEhGaWC', 'user', '2026-10-16 09:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `ingredienten`
--

CREATE TABLE `ingredienten` (
                                `id` int(11) NOT NULL,
                                `naam` varchar(100) NOT NULL,
                                `standaard_eenheid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `ingredienten`
--

INSERT INTO `ingredienten` (`id`, `naam`, `standaard_eenheid`) VALUES
                                                                   (1, 'Spaghetti', 'gram'),
                                                                   (2, 'Tomaat', 'stuks'),
                                                                   (3, 'Knoflook', 'teen'),
                                                                   (4, 'Ui', 'stuks'),
                                                                   (5, 'Olijfolie', 'el'),
                                                                   (6, 'Zout', 'snufje'),
                                                                   (7, 'Peper', 'snufje'),
                                                                   (8, 'Ei', 'stuks'),
                                                                   (9, 'Melk', 'ml'),
                                                                   (10, 'Bloem', 'gram'),
                                                                   (11, 'Boter', 'gram'),
                                                                   (12, 'Suiker', 'gram'),
                                                                   (13, 'Kipfilet', 'gram'),
                                                                   (14, 'Rijst', 'gram'),
                                                                   (15, 'Paprika', 'stuks'),
                                                                   (16, 'Courgette', 'stuks'),
                                                                   (17, 'Rundergehakt', 'gram'),
                                                                   (18, 'Tomatenpuree', 'gram'),
                                                                   (19, 'Geraspte kaas', 'gram'),
                                                                   (20, 'Kookroom', 'ml'),
                                                                   (21, 'Spekjes', 'gram'),
                                                                   (22, 'Parmezaanse kaas', 'gram'),
                                                                   (23, 'Brood', 'sneetjes'),
                                                                   (24, 'Avocado', 'stuks'),
                                                                   (25, 'Banaan', 'stuks'),
                                                                   (26, 'Havermout', 'gram'),
                                                                   (27, 'Honing', 'el'),
                                                                   (28, 'Wortel', 'stuks'),
                                                                   (29, 'Aardappel', 'gram'),
                                                                   (30, 'Bouillonblokje', 'stuks'),
                                                                   (31, 'Rode linzen', 'gram'),
                                                                   (32, 'Kokosmelk', 'ml'),
                                                                   (33, 'Currypasta', 'el'),
                                                                   (34, 'Spinazie', 'gram'),
                                                                   (35, 'Champignons', 'gram'),
                                                                   (36, 'Sojasaus', 'el'),
                                                                   (37, 'Mie noedels', 'gram'),
                                                                   (38, 'Pure chocolade', 'gram'),
                                                                   (39, 'Bakpoeder', 'tl'),
                                                                   (40, 'Citroen', 'stuks'),
                                                                   (41, 'Verse basilicum', 'gram'),
                                                                   (42, 'Tortilla wraps', 'stuks'),
                                                                   (43, 'Zalmfilet', 'gram'),
                                                                   (44, 'Broccoli', 'gram'),
                                                                   (45, 'Komkommer', 'stuks'),
                                                                   (46, 'Risottorijst', 'gram'),
                                                                   (47, 'Gepelde tomaten (blik)', 'gram'),
                                                                   (48, 'Yoghurt', 'ml');

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
                            `gebruiker_id` int(11) NOT NULL,
                            `categorie_id` int(11) DEFAULT NULL,
                            `aangemaakt_op` datetime NOT NULL DEFAULT current_timestamp(),
                            `aangepast_op` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                            `vegetarisch` tinyint(1) NOT NULL DEFAULT 0,
                            `glutenvrij` tinyint(1) NOT NULL DEFAULT 0,
                            `afbeelding` mediumblob DEFAULT NULL,
                            `afbeelding_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `recepten`
--

INSERT INTO `recepten` (`id`, `titel`, `beschrijving`, `bereidingswijze`, `bereidingstijd_min`, `gebruiker_id`, `categorie_id`, `aangemaakt_op`, `aangepast_op`, `vegetarisch`, `glutenvrij`, `afbeelding`, `afbeelding_type`) VALUES
                                                                                                                                                                                                                                   (1, 'Spaghetti pomodoro', 'Simpele Italiaanse pasta met verse tomaat en basilicum.', '1. Kook de spaghetti volgens de verpakking.\n2. Snijd de tomaten in blokjes en de knoflook fijn.\n3. Fruit de knoflook in olijfolie.\n4. Voeg de tomaten toe en laat 10 minuten zachtjes koken.\n5. Meng de saus met de pasta en garneer met basilicum.', 20, 2, 4, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (2, 'Spaghetti carbonara', 'Romige pasta met spekjes, ei en Parmezaanse kaas.', '1. Kook de spaghetti.\n2. Bak de spekjes krokant.\n3. Klop de eieren los met de Parmezaanse kaas en peper.\n4. Giet de pasta af en meng direct met de spekjes.\n5. Haal de pan van het vuur en roer het eimengsel erdoor.', 25, 3, 4, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (3, 'Pannenkoeken', 'Klassieke Hollandse pannenkoeken, lekker met stroop of suiker.', '1. Doe de bloem in een kom en maak een kuiltje.\n2. Voeg de eieren en een deel van de melk toe en klop glad.\n3. Voeg de rest van de melk toe.\n4. Smelt wat boter in een pan en bak de pannenkoeken aan beide kanten goudbruin.', 30, 2, 1, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (4, 'Overnight oats met banaan', 'Makkelijk ontbijt dat je de avond ervoor klaarzet.', '1. Meng de havermout met melk en yoghurt in een pot.\n2. Prak de helft van de banaan erdoor.\n3. Zet een nacht in de koelkast.\n4. Top de volgende ochtend met de rest van de banaan en honing.', 10, 4, 1, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (5, 'Avocado toast met ei', 'Snelle en gezonde lunch.', '1. Rooster het brood.\n2. Prak de avocado met citroensap, zout en peper.\n3. Bak of pocheer de eieren.\n4. Smeer de avocado op het brood en leg het ei erop.', 10, 4, 2, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (6, 'Kip curry met rijst', 'Milde curry met kokosmelk en kip.', '1. Kook de rijst.\n2. Snijd de kip in blokjes en de ui fijn.\n3. Bak de ui en de kip in een pan.\n4. Voeg de currypasta toe en bak kort mee.\n5. Giet de kokosmelk erbij en laat 15 minuten zachtjes koken.\n6. Serveer met de rijst.', 35, 3, 3, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (7, 'Groentesoep', 'Voedzame soep vol groenten.', '1. Snijd alle groenten in kleine stukjes.\n2. Fruit de ui in wat olie.\n3. Voeg de groenten toe en bak kort mee.\n4. Voeg 1 liter water en het bouillonblokje toe.\n5. Laat 25 minuten zachtjes koken.', 40, 2, 7, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (8, 'Tomatensoep', 'Romige tomatensoep van tomaten uit blik.', '1. Fruit de ui en knoflook in olijfolie.\n2. Voeg de tomatenpuree toe en bak 1 minuut mee.\n3. Voeg de gepelde tomaten, 500 ml water en het bouillonblokje toe.\n4. Laat 20 minuten koken en pureer met een staafmixer.\n5. Roer de room erdoor.', 30, 4, 7, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (9, 'Pasta bolognese', 'Klassieke pastasaus met gehakt.', '1. Fruit ui en knoflook.\n2. Bak het gehakt rul.\n3. Voeg de wortel, tomatenpuree en gepelde tomaten toe.\n4. Laat minimaal 30 minuten zachtjes koken.\n5. Serveer met spaghetti en geraspte kaas.', 45, 3, 4, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (10, 'Roerbak noedels met kip', 'Snelle wokschotel met groenten.', '1. Kook de noedels.\n2. Snijd de kip en de groenten in reepjes.\n3. Wok de kip goudbruin.\n4. Voeg de groenten toe en wok 3 minuten.\n5. Voeg de noedels en sojasaus toe en schep om.', 20, 3, 3, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (11, 'Linzencurry', 'Vegetarische curry met rode linzen en spinazie.', '1. Fruit de ui en knoflook.\n2. Voeg de currypasta toe.\n3. Voeg de linzen, kokosmelk en 300 ml water toe.\n4. Laat 20 minuten koken tot de linzen zacht zijn.\n5. Roer de spinazie erdoor en serveer met rijst.', 35, 4, 5, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (12, 'Aardappel-broccoli ovenschotel', 'Makkelijke ovenschotel met kaas.', '1. Verwarm de oven voor op 200 graden.\n2. Kook de aardappels 10 minuten voor en snijd in plakjes.\n3. Kook de broccoli 3 minuten.\n4. Doe alles in een ovenschaal en giet de room erover.\n5. Bestrooi met kaas en bak 25 minuten.', 50, 2, 5, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (13, 'Brownies', 'Smeuïge chocoladebrownies.', '1. Verwarm de oven voor op 180 graden.\n2. Smelt de chocolade met de boter.\n3. Klop de eieren met de suiker luchtig.\n4. Meng de chocolade erdoor en spatel de bloem erdoor.\n5. Bak 25 minuten in een ingevette bakvorm.', 40, 2, 6, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (14, 'Kipwraps', 'Wraps met kip, paprika en komkommer.', '1. Snijd de kip in reepjes en bak gaar.\n2. Snijd paprika en komkommer in reepjes.\n3. Verwarm de wraps kort in een pan.\n4. Beleg met kip, groenten en een lepel yoghurt en rol op.', 15, 4, 2, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (15, 'Zalm uit de oven met broccoli', 'Gezond en snel avondeten.', '1. Verwarm de oven voor op 200 graden.\n2. Leg de zalm en broccoli op een bakplaat.\n3. Besprenkel met olijfolie, citroen, zout en peper.\n4. Bak 15 minuten in de oven.', 25, 3, 3, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (16, 'Champignonrisotto', 'Romige vegetarische risotto.', '1. Fruit ui en knoflook in boter.\n2. Voeg de risottorijst toe en bak 1 minuut.\n3. Voeg beetje bij beetje bouillon toe en blijf roeren.\n4. Bak de champignons apart en roer ze erdoor.\n5. Maak af met Parmezaanse kaas.', 40, 2, 5, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (17, 'Bananenbrood', 'Zoet brood van overrijpe bananen.', '1. Verwarm de oven voor op 175 graden.\n2. Prak de bananen.\n3. Meng met eieren, gesmolten boter en suiker.\n4. Spatel de bloem en het bakpoeder erdoor.\n5. Bak 55 minuten in een cakevorm.', 70, 4, 6, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (18, 'Omelet met spinazie', 'Snel eiwitrijk ontbijt.', '1. Klop de eieren los met zout en peper.\n2. Laat de spinazie slinken in een pan met boter.\n3. Giet de eieren erover.\n4. Bestrooi met kaas en bak tot het ei gestold is.', 10, 3, 1, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (19, 'Tosti kaas-tomaat', 'De klassieke snack, met een twist.', '1. Beleg een sneetje brood met kaas en plakjes tomaat.\n2. Leg er een tweede sneetje op.\n3. Besmeer de buitenkant met boter.\n4. Bak in een pan of tosti-ijzer tot de kaas gesmolten is.', 10, 2, 8, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL),
                                                                                                                                                                                                                                   (20, 'Gevulde paprika', 'Paprika gevuld met rijst en gehakt uit de oven.', '1. Verwarm de oven voor op 200 graden.\n2. Kook de rijst.\n3. Bak het gehakt met ui en tomatenpuree.\n4. Meng met de rijst en vul de gehalveerde paprikas.\n5. Bestrooi met kaas en bak 25 minuten.', 45, 3, 3, '2026-10-13 11:24:29', NULL, 0, 0, NULL, NULL);

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
-- Dumping data for table `recept_ingredienten`
--

INSERT INTO `recept_ingredienten` (`id`, `recept_id`, `ingredient_id`, `hoeveelheid`, `eenheid`) VALUES
                                                                                                     (1, 1, 1, 250, 'gram'),
                                                                                                     (2, 1, 2, 4, 'stuks'),
                                                                                                     (3, 1, 3, 2, 'teen'),
                                                                                                     (4, 1, 5, 2, 'el'),
                                                                                                     (5, 1, 41, 10, 'gram'),
                                                                                                     (6, 1, 6, 1, 'snufje'),
                                                                                                     (7, 2, 1, 250, 'gram'),
                                                                                                     (8, 2, 21, 150, 'gram'),
                                                                                                     (9, 2, 8, 3, 'stuks'),
                                                                                                     (10, 2, 22, 50, 'gram'),
                                                                                                     (11, 2, 7, 1, 'snufje'),
                                                                                                     (12, 3, 10, 250, 'gram'),
                                                                                                     (13, 3, 8, 2, 'stuks'),
                                                                                                     (14, 3, 9, 500, 'ml'),
                                                                                                     (15, 3, 11, 30, 'gram'),
                                                                                                     (16, 3, 6, 1, 'snufje'),
                                                                                                     (17, 4, 26, 60, 'gram'),
                                                                                                     (18, 4, 9, 100, 'ml'),
                                                                                                     (19, 4, 48, 100, 'ml'),
                                                                                                     (20, 4, 25, 1, 'stuks'),
                                                                                                     (21, 4, 27, 1, 'el'),
                                                                                                     (22, 5, 23, 2, 'sneetjes'),
                                                                                                     (23, 5, 24, 1, 'stuks'),
                                                                                                     (24, 5, 8, 2, 'stuks'),
                                                                                                     (25, 5, 40, 0.5, 'stuks'),
                                                                                                     (26, 5, 6, 1, 'snufje'),
                                                                                                     (27, 5, 7, 1, 'snufje'),
                                                                                                     (28, 6, 13, 300, 'gram'),
                                                                                                     (29, 6, 14, 200, 'gram'),
                                                                                                     (30, 6, 4, 1, 'stuks'),
                                                                                                     (31, 6, 33, 2, 'el'),
                                                                                                     (32, 6, 32, 400, 'ml'),
                                                                                                     (33, 6, 15, 1, 'stuks'),
                                                                                                     (34, 7, 4, 1, 'stuks'),
                                                                                                     (35, 7, 28, 2, 'stuks'),
                                                                                                     (36, 7, 16, 1, 'stuks'),
                                                                                                     (37, 7, 29, 200, 'gram'),
                                                                                                     (38, 7, 30, 1, 'stuks'),
                                                                                                     (39, 7, 5, 1, 'el'),
                                                                                                     (40, 8, 4, 1, 'stuks'),
                                                                                                     (41, 8, 3, 2, 'teen'),
                                                                                                     (42, 8, 18, 70, 'gram'),
                                                                                                     (43, 8, 47, 800, 'gram'),
                                                                                                     (44, 8, 30, 1, 'stuks'),
                                                                                                     (45, 8, 20, 100, 'ml'),
                                                                                                     (46, 9, 1, 300, 'gram'),
                                                                                                     (47, 9, 17, 300, 'gram'),
                                                                                                     (48, 9, 4, 1, 'stuks'),
                                                                                                     (49, 9, 3, 2, 'teen'),
                                                                                                     (50, 9, 28, 1, 'stuks'),
                                                                                                     (51, 9, 18, 70, 'gram'),
                                                                                                     (52, 9, 47, 400, 'gram'),
                                                                                                     (53, 9, 19, 50, 'gram'),
                                                                                                     (54, 10, 37, 250, 'gram'),
                                                                                                     (55, 10, 13, 300, 'gram'),
                                                                                                     (56, 10, 15, 1, 'stuks'),
                                                                                                     (57, 10, 28, 2, 'stuks'),
                                                                                                     (58, 10, 36, 3, 'el'),
                                                                                                     (59, 11, 31, 200, 'gram'),
                                                                                                     (60, 11, 32, 400, 'ml'),
                                                                                                     (61, 11, 33, 2, 'el'),
                                                                                                     (62, 11, 4, 1, 'stuks'),
                                                                                                     (63, 11, 3, 2, 'teen'),
                                                                                                     (64, 11, 34, 150, 'gram'),
                                                                                                     (65, 12, 29, 600, 'gram'),
                                                                                                     (66, 12, 44, 400, 'gram'),
                                                                                                     (67, 12, 20, 200, 'ml'),
                                                                                                     (68, 12, 19, 100, 'gram'),
                                                                                                     (69, 13, 38, 200, 'gram'),
                                                                                                     (70, 13, 11, 150, 'gram'),
                                                                                                     (71, 13, 8, 3, 'stuks'),
                                                                                                     (72, 13, 12, 200, 'gram'),
                                                                                                     (73, 13, 10, 100, 'gram'),
                                                                                                     (74, 14, 42, 4, 'stuks'),
                                                                                                     (75, 14, 13, 250, 'gram'),
                                                                                                     (76, 14, 15, 1, 'stuks'),
                                                                                                     (77, 14, 45, 0.5, 'stuks'),
                                                                                                     (78, 14, 48, 100, 'ml'),
                                                                                                     (79, 15, 43, 250, 'gram'),
                                                                                                     (80, 15, 44, 300, 'gram'),
                                                                                                     (81, 15, 5, 2, 'el'),
                                                                                                     (82, 15, 40, 1, 'stuks'),
                                                                                                     (83, 15, 6, 1, 'snufje'),
                                                                                                     (84, 15, 7, 1, 'snufje'),
                                                                                                     (85, 16, 46, 250, 'gram'),
                                                                                                     (86, 16, 35, 250, 'gram'),
                                                                                                     (87, 16, 4, 1, 'stuks'),
                                                                                                     (88, 16, 3, 1, 'teen'),
                                                                                                     (89, 16, 30, 1, 'stuks'),
                                                                                                     (90, 16, 11, 30, 'gram'),
                                                                                                     (91, 16, 22, 40, 'gram'),
                                                                                                     (92, 17, 25, 3, 'stuks'),
                                                                                                     (93, 17, 8, 2, 'stuks'),
                                                                                                     (94, 17, 11, 80, 'gram'),
                                                                                                     (95, 17, 12, 100, 'gram'),
                                                                                                     (96, 17, 10, 200, 'gram'),
                                                                                                     (97, 17, 39, 2, 'tl'),
                                                                                                     (98, 18, 8, 3, 'stuks'),
                                                                                                     (99, 18, 34, 100, 'gram'),
                                                                                                     (100, 18, 19, 30, 'gram'),
                                                                                                     (101, 18, 11, 10, 'gram'),
                                                                                                     (102, 18, 6, 1, 'snufje'),
                                                                                                     (103, 19, 23, 2, 'sneetjes'),
                                                                                                     (104, 19, 19, 40, 'gram'),
                                                                                                     (105, 19, 2, 1, 'stuks'),
                                                                                                     (106, 19, 11, 10, 'gram'),
                                                                                                     (107, 20, 15, 4, 'stuks'),
                                                                                                     (108, 20, 17, 250, 'gram'),
                                                                                                     (109, 20, 14, 100, 'gram'),
                                                                                                     (110, 20, 4, 1, 'stuks'),
                                                                                                     (111, 20, 18, 70, 'gram'),
                                                                                                     (112, 20, 19, 75, 'gram');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boodschappenlijst`
--
ALTER TABLE `boodschappenlijst`
    ADD PRIMARY KEY (`id`),
  ADD KEY `gebruiker_id` (`gebruiker_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `categorieen`
--
ALTER TABLE `categorieen`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `naam` (`naam`);

--
-- Indexes for table `favorieten`
--
ALTER TABLE `favorieten`
    ADD PRIMARY KEY (`gebruiker_id`,`recept_id`),
  ADD KEY `recept_id` (`recept_id`);

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
-- AUTO_INCREMENT for table `boodschappenlijst`
--
ALTER TABLE `boodschappenlijst`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categorieen`
--
ALTER TABLE `categorieen`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ingredienten`
--
ALTER TABLE `ingredienten`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `recepten`
--
ALTER TABLE `recepten`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `recept_ingredienten`
--
ALTER TABLE `recept_ingredienten`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boodschappenlijst`
--
ALTER TABLE `boodschappenlijst`
    ADD CONSTRAINT `boodschappenlijst_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `boodschappenlijst_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredienten` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorieten`
--
ALTER TABLE `favorieten`
    ADD CONSTRAINT `favorieten_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorieten_ibfk_2` FOREIGN KEY (`recept_id`) REFERENCES `recepten` (`id`) ON DELETE CASCADE;

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
