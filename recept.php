<?php
require_once 'db.php';

$id   = (int) ($_GET['id'] ?? 0);
$user = current_user();
$uid  = $user['id'] ?? 0;

$stmt = $pdo->prepare(
    "SELECT r.*, c.naam AS categorie, g.naam AS auteur, (f.recept_id IS NOT NULL) AS is_fav
     FROM recepten r
     LEFT JOIN categorieen c ON c.id = r.categorie_id
     LEFT JOIN gebruikers g ON g.id = r.gebruiker_id
     LEFT JOIN favorieten f ON f.recept_id = r.id AND f.gebruiker_id = ?
     WHERE r.id = ?"
);
$stmt->execute([$uid, $id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    http_response_code(404);
    $pageTitle = 'Recept niet gevonden - MijnRecepten';
    $activePage = 'recepten';
    include 'includes/header.php';
    echo '<main class="page"><div class="empty"><h3>Recept niet gevonden</h3><p><a class="link" href="index.php">Terug naar alle recepten</a></p></div></main>';
    include 'includes/footer.php';
    exit;
}

$canEdit = $user && ($user['id'] == $recipe['gebruiker_id'] || $user['rol'] === 'admin');

// Verwijderen (alleen eigenaar of admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['actie'] ?? '') === 'verwijder' && $canEdit) {
    $pdo->prepare('DELETE FROM recepten WHERE id = ?')->execute([$id]);
    header('Location: index.php');
    exit;
}

$ing = $pdo->prepare(
    "SELECT i.naam, ri.hoeveelheid, ri.eenheid
     FROM recept_ingredienten ri
     JOIN ingredienten i ON i.id = ri.ingredient_id
     WHERE ri.recept_id = ?
     ORDER BY ri.id"
);
$ing->execute([$id]);
$ingredients = $ing->fetchAll();

$steps = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $recipe['bereidingswijze']))));

$backUrl = 'recept.php?id=' . $id;

$pageTitle  = $recipe['titel'] . ' - MijnRecepten';
$activePage = 'recepten';
include 'includes/header.php';
?>
    <section class="intro">
        <a class="back-link" href="index.php">← Alle recepten</a>
        <h1><?= e($recipe['titel']) ?></h1>
        <p>
            <?= e($recipe['categorie'] ?? 'Zonder categorie') ?>
            <?php if ($recipe['bereidingstijd_min']): ?> · <?= (int) $recipe['bereidingstijd_min'] ?> min<?php endif; ?>
            <?php if ($recipe['vegetarisch']): ?> · Vegetarisch<?php endif; ?>
            <?php if ($recipe['glutenvrij']): ?> · Glutenvrij<?php endif; ?>
            <?php if ($recipe['auteur']): ?> · door <?= e($recipe['auteur']) ?><?php endif; ?>
        </p>

        <div class="actions">
            <form action="favoriet.php" method="post">
                <input type="hidden" name="recept_id" value="<?= $id ?>">
                <input type="hidden" name="terug" value="<?= e($backUrl) ?>">
                <button class="btn btn-light" type="submit"><?= $recipe['is_fav'] ? '♥ In favorieten' : '♡ Favoriet' ?></button>
            </form>
            <form action="boodschappenlijst.php" method="post">
                <input type="hidden" name="actie" value="recept_toevoegen">
                <input type="hidden" name="recept_id" value="<?= $id ?>">
                <button class="btn" type="submit">Op boodschappenlijst</button>
            </form>
            <?php if ($canEdit): ?>
                <a class="btn btn-light" href="bewerken.php?id=<?= $id ?>">Bewerken</a>
                <form action="recept.php?id=<?= $id ?>" method="post" onsubmit="return confirm('Dit recept verwijderen?');">
                    <input type="hidden" name="actie" value="verwijder">
                    <button class="btn btn-danger" type="submit">Verwijderen</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <div class="detail-layout">
        <section class="panel">
            <h3>Ingrediënten</h3>
            <?php if ($ingredients): ?>
                <ul class="ingredient-list">
                    <?php foreach ($ingredients as $row): ?>
                        <li>
                            <span class="amount"><?= e(trim(format_amount($row['hoeveelheid']) . ' ' . $row['eenheid'])) ?></span>
                            <?= e($row['naam']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">Nog geen ingrediënten.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <?php if ($recipe['beschrijving']): ?>
                <p class="lead"><?= e($recipe['beschrijving']) ?></p>
            <?php endif; ?>
            <h3>Bereiding</h3>
            <?php if ($steps): ?>
                <ol class="steps">
                    <?php foreach ($steps as $step): ?>
                        <li><?= e($step) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="muted">Nog geen bereidingswijze.</p>
            <?php endif; ?>
        </section>
    </div>

<?php include 'includes/footer.php'; ?>
