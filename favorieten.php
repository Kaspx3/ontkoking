<?php
require_once 'db.php';
require_login();

$uid = current_user()['id'];

$stmt = $pdo->prepare(
    "SELECT r.*, c.naam AS categorie, 1 AS is_fav
     FROM favorieten f
     JOIN recepten r ON r.id = f.recept_id
     LEFT JOIN categorieen c ON c.id = r.categorie_id
     WHERE f.gebruiker_id = ?
     ORDER BY r.titel"
);
$stmt->execute([$uid]);
$recipes = $stmt->fetchAll();

$backUrl = 'favorieten.php';

$pageTitle  = 'Favorieten - MijnRecepten';
$activePage = 'favorieten';
include 'includes/header.php';
?>
    <section class="intro">
        <h1>Favorieten</h1>
        <p>Jouw bewaarde recepten – <?= count($recipes) ?> <?= count($recipes) === 1 ? 'favoriet' : 'favorieten' ?>.</p>
    </section>

    <main class="page">
        <?php if (!$recipes): ?>
            <div class="empty">
                <h3>Nog geen favorieten</h3>
                <p>Klik op het hartje bij een recept om het hier te bewaren. <a class="link" href="index.php">Bekijk alle recepten</a>.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($recipes as $recipe): ?>
                    <?php include 'includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<?php include 'includes/footer.php'; ?>
