<?php
// Zet $pageTitle en $activePage in de pagina voordat je dit bestand include
require_once __DIR__ . '/../db.php';

$pageTitle  = $pageTitle  ?? 'MijnRecepten';
$activePage = $activePage ?? '';
$user       = current_user();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <header class="site-header">
    <a class="logo" href="index.php">Mijn<span>Recepten</span></a>

    <nav class="main-nav">
      <a href="index.php" class="<?= $activePage === 'recepten' ? 'active' : '' ?>">Recepten</a>
      <a href="favorieten.php" class="<?= $activePage === 'favorieten' ? 'active' : '' ?>">Favorieten</a>
      <a href="boodschappenlijst.php" class="<?= $activePage === 'boodschappenlijst' ? 'active' : '' ?>">Boodschappenlijst</a>
    </nav>

    <form class="search" action="index.php" method="get">
      <input type="search" name="q" placeholder="Zoeken…" value="<?= e($_GET['q'] ?? '') ?>">
    </form>

    <a class="round-btn round-btn-light" href="recept_form.php" title="Nieuw recept">+</a>

    <?php if ($user): ?>
      <a class="header-link" href="logout.php">Uitloggen</a>
      <span class="round-btn" title="Ingelogd als <?= e($user['naam']) ?>"><?= e(mb_strtoupper(mb_substr($user['naam'], 0, 1))) ?></span>
    <?php else: ?>
      <a class="header-link" href="login.php">Inloggen</a>
      <a class="round-btn" href="register.php" title="Account maken">M</a>
    <?php endif; ?>
  </header>
