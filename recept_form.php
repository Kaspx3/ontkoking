<?php
// Recept toevoegen (zonder ?id) of bewerken (met ?id=3). Eén formulier voor allebei.
require_once 'db.php';
require_login();

$user = current_user();
$id   = (int) ($_GET['id'] ?? 0);

$categorieen = $pdo->query('SELECT id, naam FROM categorieen ORDER BY naam')->fetchAll();
$maxFoto     = max_photo_bytes();
$maxFotoMb   = max(1, (int) floor($maxFoto / 1048576));

// ---------- Beginwaarden: een leeg formulier ----------
$titel        = '';
$beschrijving = '';
$tijd         = 0;
$categorieId  = 0;
$vegetarisch  = 0;
$glutenvrij   = 0;
$huidigeFoto  = null;
$ingredienten = [];
$stappen      = [];

// ---------- Bij bewerken: het bestaande recept invullen ----------
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM recepten WHERE id = ?');
    $stmt->execute([$id]);
    $bestaand = $stmt->fetch();

    // Alleen de eigenaar of een admin mag bewerken
    if (!$bestaand || ($bestaand['gebruiker_id'] != $user['id'] && $user['rol'] !== 'admin')) {
        header('Location: index.php');
        exit;
    }

    $titel        = $bestaand['titel'];
    $beschrijving = (string) $bestaand['beschrijving'];
    $tijd         = (int) $bestaand['bereidingstijd_min'];
    $categorieId  = (int) $bestaand['categorie_id'];
    $vegetarisch  = (int) $bestaand['vegetarisch'];
    $glutenvrij   = (int) $bestaand['glutenvrij'];
    $huidigeFoto  = $bestaand['afbeelding_url'];
    $stappen      = steps_from_text((string) $bestaand['bereidingswijze']);

    $rows = $pdo->prepare(
        'SELECT i.naam, ri.hoeveelheid, ri.eenheid
         FROM recept_ingredienten ri
         JOIN ingredienten i ON i.id = ri.ingredient_id
         WHERE ri.recept_id = ? ORDER BY ri.id'
    );
    $rows->execute([$id]);
    foreach ($rows as $row) {
        $ingredienten[] = ingredient_to_line($row);
    }
}

// ---------- Formulier verstuurd ----------
$fouten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Is het formulier groter dan post_max_size, dan is $_POST helemaal leeg
    if (!$_POST && !$_FILES) {
        $fouten[] = "Het formulier is te groot. Kies een kleinere foto (maximaal $maxFotoMb MB).";
    } else {
        $titel        = trim($_POST['titel'] ?? '');
        $beschrijving = trim($_POST['beschrijving'] ?? '');
        $tijd         = (int) ($_POST['bereidingstijd'] ?? 0);
        $categorieId  = (int) ($_POST['categorie'] ?? 0);
        $vegetarisch  = isset($_POST['vegetarisch']) ? 1 : 0;
        $glutenvrij   = isset($_POST['glutenvrij']) ? 1 : 0;
        $ingredienten = clean_lines($_POST['ingredienten'] ?? []);
        $stappen      = clean_lines($_POST['stappen'] ?? []);

        // ---- Controleren ----
        if ($titel === '' || mb_strlen($titel) > 150) {
            $fouten[] = 'Vul een titel in (maximaal 150 tekens).';
        }
        if ($tijd < 1 || $tijd > 1440) {
            $fouten[] = 'Vul de bereidingstijd in minuten in (1 tot 1440).';
        }
        if (!in_array($categorieId, array_column($categorieen, 'id'))) {
            $fouten[] = 'Kies een categorie.';
        }
        if (!$ingredienten) {
            $fouten[] = 'Voeg minstens één ingrediënt toe.';
        }
        if (!$stappen) {
            $fouten[] = 'Voeg minstens één stap toe.';
        }

        // Foto is optioneel
        $foto    = $_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE];
        $fotoExt = null;
        if ($foto['error'] !== UPLOAD_ERR_NO_FILE) {
            [$fotoExt, $fotoFout] = check_photo($foto, $maxFoto);
            if ($fotoFout) {
                $fouten[] = $fotoFout;
            }
        }

        // ---- Opslaan ----
        if (!$fouten) {
            $nieuweFoto = null;
            $receptId   = $id;

            try {
                $pdo->beginTransaction();

                $fotoPad = $huidigeFoto;                       // zonder nieuwe upload blijft de oude foto
                if ($fotoExt !== null) {
                    $nieuweFoto = store_photo($foto, $fotoExt);
                    $fotoPad    = $nieuweFoto;
                }
                $bereidingswijze = implode("\n", $stappen);
                $beschrijvingDb  = $beschrijving === '' ? null : $beschrijving;

                if ($id) {
                    $pdo->prepare(
                        'UPDATE recepten SET titel = ?, beschrijving = ?, bereidingswijze = ?, bereidingstijd_min = ?,
                         categorie_id = ?, vegetarisch = ?, glutenvrij = ?, afbeelding_url = ? WHERE id = ?'
                    )->execute([$titel, $beschrijvingDb, $bereidingswijze, $tijd,
                                $categorieId, $vegetarisch, $glutenvrij, $fotoPad, $id]);
                } else {
                    $pdo->prepare(
                        'INSERT INTO recepten (titel, beschrijving, bereidingswijze, bereidingstijd_min, categorie_id,
                         vegetarisch, glutenvrij, afbeelding_url, gebruiker_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    )->execute([$titel, $beschrijvingDb, $bereidingswijze, $tijd, $categorieId,
                                $vegetarisch, $glutenvrij, $fotoPad, $user['id']]);
                    $receptId = (int) $pdo->lastInsertId();
                }

                save_recipe_ingredients($pdo, $receptId, $ingredienten);

                $pdo->commit();

                if ($nieuweFoto !== null) {
                    delete_upload($huidigeFoto);               // vervangen foto opruimen
                }
                header('Location: recept.php?id=' . $receptId);   // voorkomt ook dubbel opslaan bij F5
                exit;

            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                delete_upload($nieuweFoto);                    // geen losse foto's laten staan
                $fouten[] = 'Opslaan is mislukt. Probeer het opnieuw.';
                if ($debug) {
                    $fouten[] = $ex->getMessage();
                }
            }
        }
    }
}

// Lege formulieren beginnen met een paar lege regels
if (!$ingredienten) $ingredienten = ['', '', ''];
if (!$stappen)      $stappen      = ['', ''];
$voorbeeldIng = ['Bijv. 200 g bloem', 'Bijv. 1 el honing', 'Bijv. snufje zout'];

$pageTitle  = ($id ? 'Recept bewerken' : 'Recept toevoegen') . ' - ReceptenBaas';
$activePage = '';
include 'includes/header.php';
?>
    <section class="intro">
        <h1><?= $id ? 'Recept bewerken' : 'Recept toevoegen' ?></h1>
        <p><?= $id ? 'Pas je recept aan en sla het op.' : 'Deel je favoriete gerecht met anderen.' ?></p>
    </section>

    <main class="page form-page">
        <form action="recept_form.php<?= $id ? '?id=' . $id : '' ?>" method="post" enctype="multipart/form-data">

            <?php if ($fouten): ?>
                <div class="form-error" role="alert">
                    <ul>
                        <?php foreach ($fouten as $fout): ?>
                            <li><?= e($fout) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-columns">

                <section class="col-left">

                    <label class="upload <?= $huidigeFoto ? 'is-filled' : '' ?>" for="foto" id="uploadVlak">
                        <img id="fotoPreview" class="upload-preview" alt="Voorbeeld van de gekozen foto"
                             <?= $huidigeFoto ? 'src="' . e($huidigeFoto) . '" data-origineel="' . e($huidigeFoto) . '"' : 'hidden' ?>>
                        <span class="bowl" aria-hidden="true"></span>
                        <span class="upload-text">Foto uploaden</span>
                        <input type="file" id="foto" name="foto" class="visually-hidden"
                               accept="image/jpeg,image/png,image/webp" data-max-bytes="<?= $maxFoto ?>">
                    </label>

                    <div class="field">
                        <label for="titel">Titel</label>
                        <input type="text" id="titel" name="titel" maxlength="150" value="<?= e($titel) ?>" required>
                    </div>

                    <div class="field">
                        <label for="beschrijving">Korte beschrijving</label>
                        <textarea id="beschrijving" name="beschrijving" rows="3"><?= e($beschrijving) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="bereidingstijd">Bereidingstijd (min)</label>
                        <input type="number" id="bereidingstijd" name="bereidingstijd" min="1" max="1440"
                               value="<?= $tijd ? e($tijd) : '' ?>" required>
                    </div>

                    <div class="field">
                        <label for="categorie">Categorie</label>
                        <select id="categorie" name="categorie" required>
                            <option value="" disabled <?= $categorieId ? '' : 'selected' ?>>Kies een categorie</option>
                            <?php foreach ($categorieen as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>" <?= (int) $cat['id'] === $categorieId ? 'selected' : '' ?>>
                                    <?= e($cat['naam']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="check-row">
                        <label class="check"><input type="checkbox" name="vegetarisch" <?= $vegetarisch ? 'checked' : '' ?>> Vegetarisch</label>
                        <label class="check"><input type="checkbox" name="glutenvrij" <?= $glutenvrij ? 'checked' : '' ?>> Glutenvrij</label>
                    </div>

                </section>

                <section class="col-right">

                    <h2>Ingrediënten</h2>
                    <ul class="rows" data-lijst="ingredienten">
                        <?php foreach ($ingredienten as $i => $tekst): ?>
                            <li class="row">
                                <input type="text" name="ingredienten[]" value="<?= e($tekst) ?>"
                                       aria-label="Ingrediënt <?= $i + 1 ?>"
                                       placeholder="<?= e($voorbeeldIng[$i] ?? 'Bijv. 2 eieren') ?>">
                                <button type="button" class="remove" aria-label="Ingrediënt <?= $i + 1 ?> verwijderen">×</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="add-btn" data-lijst="ingredienten">+ ingrediënt</button>

                    <h2 class="second-heading">Stappen</h2>
                    <ol class="rows rows-steps" data-lijst="stappen">
                        <?php foreach ($stappen as $i => $tekst): ?>
                            <li class="row">
                                <input type="text" name="stappen[]" value="<?= e($tekst) ?>"
                                       aria-label="Stap <?= $i + 1 ?>" placeholder="Beschrijf de stap…">
                                <button type="button" class="remove" aria-label="Stap <?= $i + 1 ?> verwijderen">×</button>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <button type="button" class="add-btn" data-lijst="stappen">+ stap</button>

                </section>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Recept opslaan</button>
                <a href="<?= $id ? 'recept.php?id=' . $id : 'index.php' ?>" class="btn btn-light">Annuleren</a>
            </div>

        </form>
    </main>

    <script src="assets/js/recept-form.js" defer></script>

<?php include 'includes/footer.php'; ?>
