<?php
require 'db.php';

// Het id uit de URL halen (detail.php?id=1)
$id = $_GET['id'];

// Recept ophalen
$stmt = $pdo->prepare("SELECT * FROM recepten WHERE id = ?");
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

$pageTitle  = $recept['titel'];
$activePage = 'recepten';
include 'includes/header.php';
?>

    <link rel="stylesheet" href="detail.css">

    <main class="recept">
        <img src="https://placehold.co/800x400" alt="Recept foto" class="recept-foto">

        <h1><?= $recept['titel'] ?></h1>
        <p class="recept-info"><?= $recept['bereidingstijd_min'] ?> min</p>

        <p><?= $recept['beschrijving'] ?></p>

        <section class="ingredienten">
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
        </section>

        <section class="bereiding">
            <h2>Bereiding</h2>
            <p><?= nl2br($recept['bereidingswijze']) ?></p>
        </section>
    </main>

<?php include 'includes/footer.php'; ?>