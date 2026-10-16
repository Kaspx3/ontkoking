<?php
require_once 'db.php';
require_login();

$user = current_user();
$id   = (int) ($_GET['id'] ?? 0);

$units = ['g', 'kg', 'mg', 'ml', 'dl', 'l', 'el', 'tl', 'stuk', 'stuks', 'teen', 'tenen', 'bos', 'blik', 'snufje', 'plak', 'plakken', 'pak', 'zak'];

// Bestaand recept laden (bij bewerken)
$recipe = [
    'titel' => '', 'beschrijving' => '', 'bereidingswijze' => '', 'bereidingstijd_min' => '',
    'afbeelding_url' => '', 'categorie_id' => '', 'vegetarisch' => 0, 'glutenvrij' => 0,
];
$ingredientText = '';

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM recepten WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing || ($existing['gebruiker_id'] != $user['id'] && $user['rol'] !== 'admin')) {
        header('Location: index.php');
        exit;
    }
    $recipe = $existing;

    $rows = $pdo->prepare(
        "SELECT i.naam, ri.hoeveelheid, ri.eenheid FROM recept_ingredienten ri
         JOIN ingredienten i ON i.id = ri.ingredient_id WHERE ri.recept_id = ? ORDER BY ri.id"
    );
    $rows->execute([$id]);
    $lines = [];
    foreach ($rows as $row) {
        $lines[] = trim(format_amount($row['hoeveelheid']) . ' ' . $row['eenheid'] . ' ' . $row['naam']);
    }
    $ingredientText = implode("\n", $lines);
}

$categories = $pdo->query('SELECT id, naam FROM categorieen ORDER BY naam')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe['titel']              = trim($_POST['titel'] ?? '');
    $recipe['beschrijving']       = trim($_POST['beschrijving'] ?? '');
    $recipe['bereidingswijze']    = trim($_POST['bereidingswijze'] ?? '');
    $recipe['bereidingstijd_min'] = trim($_POST['bereidingstijd_min'] ?? '');
    $recipe['afbeelding_url']     = trim($_POST['afbeelding_url'] ?? '');
    $recipe['categorie_id']       = $_POST['categorie_id'] ?? '';
    $recipe['vegetarisch']        = isset($_POST['vegetarisch']) ? 1 : 0;
    $recipe['glutenvrij']         = isset($_POST['glutenvrij']) ? 1 : 0;
    $ingredientText               = trim($_POST['ingredienten'] ?? '');

    if ($recipe['titel'] === '') {
        $error = 'Geef je recept een titel.';
    } elseif ($recipe['afbeelding_url'] !== '' && !preg_match('#^https?://#i', $recipe['afbeelding_url'])) {
        $error = 'De afbeelding moet een link zijn die begint met http:// of https://.';
    } else {
        $minutes = $recipe['bereidingstijd_min'] === '' ? null : max(0, (int) $recipe['bereidingstijd_min']);
        $catId   = $recipe['categorie_id'] === '' ? null : (int) $recipe['categorie_id'];
        $image   = $recipe['afbeelding_url'] === '' ? null : $recipe['afbeelding_url'];

        $pdo->beginTransaction();

        if ($id) {
            $pdo->prepare(
                'UPDATE recepten SET titel=?, beschrijving=?, bereidingswijze=?, bereidingstijd_min=?, afbeelding_url=?,
                 categorie_id=?, vegetarisch=?, glutenvrij=? WHERE id=?'
            )->execute([$recipe['titel'], $recipe['beschrijving'], $recipe['bereidingswijze'], $minutes, $image,
                        $catId, $recipe['vegetarisch'], $recipe['glutenvrij'], $id]);
            $pdo->prepare('DELETE FROM recept_ingredienten WHERE recept_id = ?')->execute([$id]);
        } else {
            $pdo->prepare(
                'INSERT INTO recepten (titel, beschrijving, bereidingswijze, bereidingstijd_min, afbeelding_url,
                 gebruiker_id, categorie_id, vegetarisch, glutenvrij) VALUES (?,?,?,?,?,?,?,?,?)'
            )->execute([$recipe['titel'], $recipe['beschrijving'], $recipe['bereidingswijze'], $minutes, $image,
                        $user['id'], $catId, $recipe['vegetarisch'], $recipe['glutenvrij']]);
            $id = (int) $pdo->lastInsertId();
        }

        // Ingrediënten: één per regel, bv. "200 g pasta" of "2 tomaten" of "zout"
        $findIng   = $pdo->prepare('SELECT id FROM ingredienten WHERE naam = ?');
        $insertIng = $pdo->prepare('INSERT INTO ingredienten (naam) VALUES (?)');
        $link      = $pdo->prepare('INSERT INTO recept_ingredienten (recept_id, ingredient_id, hoeveelheid, eenheid) VALUES (?,?,?,?)');

        foreach (preg_split('/\R/', $ingredientText) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if ($parts === [''] || !$parts) {
                continue;
            }
            $amount = null;
            $unit   = null;

            if (preg_match('/^\d+([.,]\d+)?$/', $parts[0])) {
                $amount = (float) str_replace(',', '.', array_shift($parts));
                if ($parts && in_array(mb_strtolower($parts[0]), $units, true)) {
                    $unit = mb_strtolower(array_shift($parts));
                }
            }
            $name = mb_strtolower(trim(implode(' ', $parts)));
            if ($name === '') {
                continue;
            }

            $findIng->execute([$name]);
            $ingId = $findIng->fetchColumn();
            if (!$ingId) {
                $insertIng->execute([$name]);
                $ingId = $pdo->lastInsertId();
            }
            $link->execute([$id, $ingId, $amount, $unit]);
        }

        $pdo->commit();
        header('Location: recept.php?id=' . $id);
        exit;
    }
}

$pageTitle  = ($id ? 'Recept bewerken' : 'Nieuw recept') . ' - MijnRecepten';
$activePage = '';
include 'includes/header.php';
?>
    <section class="intro">
        <h1><?= $id ? 'Recept bewerken' : 'Nieuw recept' ?></h1>
        <p>Vul de basis in, de rest kun je later altijd aanpassen.</p>
    </section>

    <main class="page page-narrow">
        <form class="panel form-panel" method="post">
            <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>

            <label class="field">
                <span>Titel</span>
                <input type="text" name="titel" value="<?= e($recipe['titel']) ?>" maxlength="150" required>
            </label>

            <div class="field-row">
                <label class="field">
                    <span>Categorie</span>
                    <select name="categorie_id">
                        <option value="">Geen categorie</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= (string) $recipe['categorie_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['naam']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field">
                    <span>Bereidingstijd (minuten)</span>
                    <input type="number" name="bereidingstijd_min" min="0" value="<?= e($recipe['bereidingstijd_min']) ?>">
                </label>
            </div>

            <label class="field">
                <span>Korte beschrijving</span>
                <textarea name="beschrijving" rows="2"><?= e($recipe['beschrijving']) ?></textarea>
            </label>

            <label class="field">
                <span>Ingrediënten (één per regel, bijvoorbeeld “200 g pasta”)</span>
                <textarea name="ingredienten" rows="6"><?= e($ingredientText) ?></textarea>
            </label>

            <label class="field">
                <span>Bereiding (één stap per regel)</span>
                <textarea name="bereidingswijze" rows="6"><?= e($recipe['bereidingswijze']) ?></textarea>
            </label>

            <label class="field">
                <span>Afbeelding (link, niet verplicht)</span>
                <input type="url" name="afbeelding_url" value="<?= e($recipe['afbeelding_url']) ?>" placeholder="https://…">
            </label>

            <div class="field-row">
                <label class="check"><input type="checkbox" name="vegetarisch" <?= $recipe['vegetarisch'] ? 'checked' : '' ?>> Vegetarisch</label>
                <label class="check"><input type="checkbox" name="glutenvrij" <?= $recipe['glutenvrij'] ? 'checked' : '' ?>> Glutenvrij</label>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Recept opslaan</button>
                <a class="btn btn-light" href="<?= $id ? 'recept.php?id=' . $id : 'index.php' ?>">Annuleren</a>
            </div>
        </form>
    </main>

<?php include 'includes/footer.php'; ?>
