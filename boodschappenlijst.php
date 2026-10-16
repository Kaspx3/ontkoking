<?php
require_once 'db.php';
require_login();

$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actie = $_POST['actie'] ?? '';
    $itemId = (int) ($_POST['item_id'] ?? 0);

    if ($actie === 'recept_toevoegen') {
        // Alle ingrediënten van een recept op de lijst zetten
        $receptId = (int) ($_POST['recept_id'] ?? 0);
        $rows = $pdo->prepare('SELECT ingredient_id, hoeveelheid, eenheid FROM recept_ingredienten WHERE recept_id = ?');
        $rows->execute([$receptId]);

        $find   = $pdo->prepare('SELECT id FROM boodschappenlijst WHERE gebruiker_id = ? AND ingredient_id = ? AND afgevinkt = 0 AND eenheid <=> ?');
        $update = $pdo->prepare('UPDATE boodschappenlijst SET hoeveelheid = COALESCE(hoeveelheid, 0) + ? WHERE id = ?');
        $insert = $pdo->prepare('INSERT INTO boodschappenlijst (gebruiker_id, ingredient_id, hoeveelheid, eenheid) VALUES (?, ?, ?, ?)');

        foreach ($rows as $row) {
            $find->execute([$uid, $row['ingredient_id'], $row['eenheid']]);
            $existing = $find->fetch();
            if ($existing && $row['hoeveelheid'] !== null) {
                $update->execute([$row['hoeveelheid'], $existing['id']]);
            } elseif (!$existing) {
                $insert->execute([$uid, $row['ingredient_id'], $row['hoeveelheid'], $row['eenheid']]);
            }
        }
    } elseif ($actie === 'afvinken') {
        $pdo->prepare('UPDATE boodschappenlijst SET afgevinkt = 1 - afgevinkt WHERE id = ? AND gebruiker_id = ?')->execute([$itemId, $uid]);
    } elseif ($actie === 'verwijder') {
        $pdo->prepare('DELETE FROM boodschappenlijst WHERE id = ? AND gebruiker_id = ?')->execute([$itemId, $uid]);
    } elseif ($actie === 'afgevinkte_weg') {
        $pdo->prepare('DELETE FROM boodschappenlijst WHERE gebruiker_id = ? AND afgevinkt = 1')->execute([$uid]);
    } elseif ($actie === 'leegmaken') {
        $pdo->prepare('DELETE FROM boodschappenlijst WHERE gebruiker_id = ?')->execute([$uid]);
    }

    header('Location: boodschappenlijst.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT b.id, b.hoeveelheid, b.eenheid, b.afgevinkt, i.naam
     FROM boodschappenlijst b
     JOIN ingredienten i ON i.id = b.ingredient_id
     WHERE b.gebruiker_id = ?
     ORDER BY b.afgevinkt, i.naam"
);
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

$pageTitle  = 'Boodschappenlijst - MijnRecepten';
$activePage = 'boodschappenlijst';
include 'includes/header.php';
?>
    <section class="intro">
        <h1>Boodschappenlijst</h1>
        <p>Alles wat je nodig hebt voor je volgende recept.</p>
    </section>

    <main class="page page-narrow">
        <?php if (!$items): ?>
            <div class="empty">
                <h3>Je lijst is leeg</h3>
                <p>Open een recept en kies “Op boodschappenlijst”. <a class="link" href="index.php">Bekijk recepten</a>.</p>
            </div>
        <?php else: ?>
            <ul class="shopping-list">
                <?php foreach ($items as $item): ?>
                    <li class="<?= $item['afgevinkt'] ? 'done' : '' ?>">
                        <form action="boodschappenlijst.php" method="post">
                            <input type="hidden" name="actie" value="afvinken">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="tick" type="submit" aria-label="Afvinken"><?= $item['afgevinkt'] ? '✓' : '' ?></button>
                        </form>
                        <span class="item-name">
                            <?php $amount = trim(format_amount($item['hoeveelheid']) . ' ' . $item['eenheid']); ?>
                            <?php if ($amount): ?><span class="amount"><?= e($amount) ?></span><?php endif; ?>
                            <?= e($item['naam']) ?>
                        </span>
                        <form action="boodschappenlijst.php" method="post">
                            <input type="hidden" name="actie" value="verwijder">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="remove" type="submit" aria-label="Verwijderen">&times;</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="actions">
                <form action="boodschappenlijst.php" method="post">
                    <input type="hidden" name="actie" value="afgevinkte_weg">
                    <button class="btn btn-light" type="submit">Afgevinkte verwijderen</button>
                </form>
                <form action="boodschappenlijst.php" method="post" onsubmit="return confirm('De hele lijst leegmaken?');">
                    <input type="hidden" name="actie" value="leegmaken">
                    <button class="btn btn-light" type="submit">Lijst leegmaken</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

<?php include 'includes/footer.php'; ?>
