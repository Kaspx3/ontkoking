<?php
require_once 'db.php';
session_start();

// TIJDELIJK: tot login.php klaar is wordt gebruiker 1 gebruikt
$gebruikerId = $_SESSION['gebruiker_id'] ?? 1;

function e($tekst): string {
    return htmlspecialchars((string)$tekst, ENT_QUOTES, 'UTF-8');
}

// "400 g spaghetti" -> [400, 'g', 'spaghetti']
function leesIngredient(string $regel): array {
    $eenheden = 'kg|g|gr|ml|cl|dl|l|el|tl|kopjes?|teentjes?|snufjes?|stuks?|blikjes?|blikken?'
        . '|plakjes?|plakken|bosjes?|takjes?|handjes?|pakjes?|zakjes?|scheutjes?';
    $patroon = '/^\s*(?:(\d+(?:[.,]\d+)?)\s*)?(?:(' . $eenheden . ')(?=\s|$)\s*)?(.+)$/iu';

    if (!preg_match($patroon, $regel, $m)) {
        return [null, null, $regel];
    }
    $hoeveelheid = $m[1] !== '' ? (float)str_replace(',', '.', $m[1]) : null;
    $eenheid     = $m[2] !== '' ? mb_strtolower($m[2]) : null;
    return [$hoeveelheid, $eenheid, trim($m[3])];
}

$categorieen = $pdo->query('SELECT id, naam FROM categorieen ORDER BY naam')->fetchAll();

$fouten = [];
$titel = '';
$tijd = '';
$categorieId = 0;
$ingredienten = ['', '', ''];
$stappen = ['', ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel       = trim($_POST['titel'] ?? '');
    $tijd        = (int)($_POST['bereidingstijd'] ?? 0);
    $categorieId = (int)($_POST['categorie'] ?? 0);

    $ruwIng  = is_array($_POST['ingredienten'] ?? null) ? $_POST['ingredienten'] : [];
    $ruwStap = is_array($_POST['stappen'] ?? null) ? $_POST['stappen'] : [];
    $ingredienten = array_values(array_filter(array_map('trim', $ruwIng), 'strlen'));
    $stappen      = array_values(array_filter(array_map('trim', $ruwStap), 'strlen'));

    // ---- Validatie ----
    if ($titel === '' || mb_strlen($titel) > 150) $fouten[] = 'Vul een titel in (maximaal 150 tekens).';
    if ($tijd < 1) $fouten[] = 'Vul de bereidingstijd in minuten in.';
    if (!in_array($categorieId, array_column($categorieen, 'id'))) $fouten[] = 'Kies een categorie.';
    if (!$ingredienten) $fouten[] = 'Voeg minstens één ingrediënt toe.';
    if (!$stappen)      $fouten[] = 'Voeg minstens één stap toe.';

    // ---- Foto controleren (optioneel) ----
    $fotoNaam = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $toegestaan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $fouten[] = 'De foto kon niet worden geüpload.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['foto']['tmp_name']);
            if (!isset($toegestaan[$mime])) {
                $fouten[] = 'Upload een jpg-, png- of webp-foto.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $fouten[] = 'De foto mag maximaal 5 MB zijn.';
            } else {
                $fotoNaam = bin2hex(random_bytes(8)) . '.' . $toegestaan[$mime];
            }
        }
    }

    // ---- Opslaan ----
    if (!$fouten) {
        $fotoPad = null;
        try {
            if ($fotoNaam) {
                $map = __DIR__ . '/uploads';
                if (!is_dir($map)) mkdir($map, 0755, true);
                $fotoPad = $map . '/' . $fotoNaam;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPad)) {
                    throw new RuntimeException('Foto opslaan mislukt.');
                }
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO recepten
                   (titel, bereidingswijze, bereidingstijd_min, afbeelding_url, gebruiker_id, categorie_id)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $titel,
                implode("\n", $stappen),                       // één stap per regel
                $tijd,
                $fotoNaam ? 'uploads/' . $fotoNaam : null,
                $gebruikerId,
                $categorieId,
            ]);
            $receptId = (int)$pdo->lastInsertId();

            $zoek    = $pdo->prepare('SELECT id FROM ingredienten WHERE naam = ?');
            $maakIng = $pdo->prepare('INSERT INTO ingredienten (naam, standaard_eenheid) VALUES (?, ?)');
            $koppel  = $pdo->prepare(
                'INSERT INTO recept_ingredienten (recept_id, ingredient_id, hoeveelheid, eenheid)
                 VALUES (?, ?, ?, ?)'
            );

            foreach ($ingredienten as $regel) {
                [$hoeveelheid, $eenheid, $naam] = leesIngredient($regel);
                $naam = mb_substr(mb_strtolower($naam), 0, 100);

                // Ingrediënt hergebruiken als het al bestaat, anders aanmaken
                $zoek->execute([$naam]);
                $ingId = $zoek->fetchColumn();
                if (!$ingId) {
                    $maakIng->execute([$naam, $eenheid]);
                    $ingId = $pdo->lastInsertId();
                }
                $koppel->execute([$receptId, $ingId, $hoeveelheid, $eenheid]);
            }

            $pdo->commit();
            header('Location: ToevoegPagina.php?opgeslagen=1');   // voorkomt dubbel opslaan bij F5
            exit;

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($fotoPad && is_file($fotoPad)) unlink($fotoPad);  // geen losse foto's laten staan
            $fouten[] = 'Opslaan is mislukt. Probeer het opnieuw.';
            // Tijdens ontwikkelen handig: $fouten[] = $ex->getMessage();
        }
    }

    if (!$ingredienten) $ingredienten = ['', '', ''];
    if (!$stappen)      $stappen = ['', ''];
}

$voorbeeldIng = ['Bijv. 200 g bloem', 'Bijv. 1 el honing', 'Bijv. snufje zout'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recept toevoegen – MijnRecepten</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header class="site-header">
    <a href="#" class="logo">Mijn<span>Recepten</span></a>

    <nav class="main-nav" aria-label="Hoofdmenu">
        <a href="#" class="nav-link is-active">Recepten</a>
        <a href="#" class="nav-link">Favorieten</a>
        <a href="#" class="nav-link">Boodschappenlijst</a>
    </nav>

    <div class="header-tools">
        <input type="search" class="search" placeholder="Zoeken…" aria-label="Zoeken">
        <a href="ToevoegPagina.php" class="circle-btn circle-btn--light" aria-label="Nieuw recept">+</a>
        <a href="#" class="circle-btn" aria-label="Mijn account">M</a>
    </div>
</header>

<main class="page">

    <form action="ToevoegPagina.php" method="post" enctype="multipart/form-data">

        <div class="page-head">
            <h1>Recept toevoegen</h1>
            <div class="page-actions">
                <a href="#" class="btn btn--outline">Annuleren</a>
                <button type="submit" class="btn btn--solid">Opslaan</button>
            </div>
        </div>

        <?php if (isset($_GET['opgeslagen'])): ?>
            <p class="melding melding--ok" role="status">Je recept is opgeslagen.</p>
        <?php endif; ?>

        <?php if ($fouten): ?>
            <div class="melding melding--fout" role="alert">
                <ul>
                    <?php foreach ($fouten as $fout): ?>
                        <li><?= e($fout) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="layout">

            <section class="col-left">

                <label class="upload" for="foto" id="uploadVlak">
                    <img id="fotoPreview" class="upload-preview" alt="Voorbeeld van de gekozen foto" hidden>
                    <span class="bowl" aria-hidden="true"></span>
                    <span class="upload-text">Foto uploaden</span>
                    <input type="file" id="foto" name="foto" class="visually-hidden"
                           accept="image/jpeg,image/png,image/webp">
                </label>

                <div class="field">
                    <label for="titel">Titel</label>
                    <input type="text" id="titel" name="titel" maxlength="150" value="<?= e($titel) ?>" required>
                </div>

                <div class="field">
                    <label for="bereidingstijd">Bereidingstijd (min)</label>
                    <input type="number" id="bereidingstijd" name="bereidingstijd" min="1"
                           value="<?= $tijd ? e($tijd) : '' ?>" required>
                </div>

                <div class="field">
                    <label for="categorie">Categorie</label>
                    <select id="categorie" name="categorie" required>
                        <option value="" disabled <?= $categorieId ? '' : 'selected' ?>>Kies een categorie</option>
                        <?php foreach ($categorieen as $cat): ?>
                            <option value="<?= e($cat['id']) ?>" <?= (int)$cat['id'] === $categorieId ? 'selected' : '' ?>>
                                <?= e($cat['naam']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                <ol class="rows steps" data-lijst="stappen">
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

    </form>
</main>

<script src="Script.js" defer></script>
</body>
</html>