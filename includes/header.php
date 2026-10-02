<?php
// Set $pageTitle and $activePage in the page before including this file
$pageTitle  = $pageTitle  ?? 'MijnRecepten';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <a class="logo" href="../index.php">Mijn<span>Recepten</span></a>

    <nav class="main-nav">
      <a href="../index.php" class="<?= $activePage === 'recepten' ? 'active' : '' ?>">Recepten</a>
      <a href="favorieten.php" class="<?= $activePage === 'favorieten' ? 'active' : '' ?>">Favorieten</a>
      <a href="boodschappenlijst.php" class="<?= $activePage === 'boodschappenlijst' ? 'active' : '' ?>">Boodschappenlijst</a>
    </nav>

    <form class="search" action="../index.php" method="get">
      <input type="search" name="q" placeholder="Zoeken…">
    </form>

    <a class="round-btn round-btn-light" href="bewerken.php" title="Nieuw recept">+</a>
    <a class="round-btn" href="#" title="Profiel">M</a>
  </header>
