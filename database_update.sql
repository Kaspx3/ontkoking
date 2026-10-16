-- Voer dit één keer uit in phpMyAdmin (database `ontkoking`, tab SQL).

-- 1. Filters "vegetarisch" en "glutenvrij" per recept
ALTER TABLE `recepten`
  ADD COLUMN `vegetarisch` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `glutenvrij`  TINYINT(1) NOT NULL DEFAULT 0;

-- 2. Favorieten
CREATE TABLE `favorieten` (
  `gebruiker_id` int(11) NOT NULL,
  `recept_id` int(11) NOT NULL,
  PRIMARY KEY (`gebruiker_id`, `recept_id`),
  KEY `recept_id` (`recept_id`),
  CONSTRAINT `favorieten_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorieten_ibfk_2` FOREIGN KEY (`recept_id`) REFERENCES `recepten` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- 3. Boodschappenlijst
CREATE TABLE `boodschappenlijst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gebruiker_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `hoeveelheid` float DEFAULT NULL,
  `eenheid` varchar(20) DEFAULT NULL,
  `afgevinkt` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `gebruiker_id` (`gebruiker_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `boodschappenlijst_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boodschappenlijst_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredienten` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- 4. De drie categorieën uit het filter
INSERT IGNORE INTO `categorieen` (`naam`) VALUES ('Ontbijt'), ('Hoofdgerecht'), ('Dessert');
