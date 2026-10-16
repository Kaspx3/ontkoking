<?php
require 'db.php';

// Het id uit de URL halen (detail.php?id=1)
$id = $_GET['id'] ?? 0;

// Recept ophalen, met de naam van de categorie
$stmt = $pdo->prepare("
    SELECT recepten.*, categorieen.naam AS categorie
    FROM recepten
    LEFT JOIN categorieen ON recepten.categorie_id = categorieen.id
    WHERE recepten.id = ?
");
$stmt->execute([$id]);
$recept = $stmt->fetch();

// Bestaat het recept niet? Stoppen.
if (!$recept) {
    die('Recept niet gevonden');
}

// Ingrediënten van dit recept ophalen
$stmt = $pdo->prepare("
    SELECT recept_ingredienten.hoeveelheid, recept_ingredienten.eenheid, ingredienten.naam
    FROM recept_ingredienten
    JOIN ingredienten ON recept_ingredienten.ingredient_id = ingredienten.id
    WHERE recept_ingredienten.recept_id = ?
");
$stmt->execute([$id]);
$ingredienten = $stmt->fetchAll();

// Bereidingswijze opsplitsen in losse stappen (elke regel = 1 stap)
$stappen = array_filter(array_map('trim', explode("\n", $recept['bereidingswijze'])));

$pageTitle  = $recept['titel'];
$activePage = 'recepten';
include 'includes/header.php';
?>

    <link rel="stylesheet" href="detail.css">

    <section class="hero">
        <img src="https://placehold.co/400x300" alt="Recept foto" class="recept-foto">
    </section>

    <main class="recept">

        <div class="recept-kop">
            <div>
                <h1><?= $recept['titel'] ?></h1>
                <div class="labels">
                    <span class="label"><?= $recept['bereidingstijd_min'] ?> min</span>
                    <?php if ($recept['categorie']): ?>
                        <span class="label"><?= $recept['categorie'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="acties">
                <a href="#" class="knop" onclick="window.print(); return false;">Print</a>
                <a href="#" class="knop">Delen</a>
                <a href="#" class="knop knop-groen">Bewerk</a>
            </div>
        </div>

        <div class="recept-inhoud">

            <section class="bereiding">
                <h2>Bereiding</h2>
                <?php $nummer = 1; ?>
                <?php foreach ($stappen as $stap): ?>
                    <div class="stap">
                        <span class="stap-nummer"><?= $nummer++ ?></span>
                        <p><?= $stap ?></p>
                    </div>
                <?php endforeach; ?>
            </section>

            <aside class="ingredienten">
                <h2>Ingrediënten</h2>
                <ul>
                    <?php foreach ($ingredienten as $ingredient): ?>
                        <li>
                            <?= $ingredient['hoeveelheid'] ?>
                            <?= $ingredient['eenheid'] ?>
                            <?= $ingredient['naam'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button class="knop-lijst">+ Boodschappenlijst</button>
            </aside>

        </div>
    </main>

<?php include 'includes/footer.php'; ?>