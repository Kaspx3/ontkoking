<?php
require_once 'db.php';
session_start();

// TIJDELIJK: tot login.php klaar is wordt gebruiker 1 (Admin) gebruikt
$gebruikerId = $_SESSION['gebruiker_id'] ?? 1;

function e($tekst): string {
    return htmlspecialchars((string)$tekst, ENT_QUOTES, 'UTF-8');
}

// "2M" uit php.ini omrekenen naar bytes
function iniBytes(string $waarde): int {
    $waarde = trim($waarde);
    $getal  = (int)$waarde;
    return match (strtolower(substr($waarde, -1))) {
        'g'     => $getal * 1024 ** 3,
        'm'     => $getal * 1024 ** 2,
        'k'     => $getal * 1024,
        default => $getal,
    };
}

// "400 g spaghetti" -> [400.0, 'gram', 'spaghetti']
function leesIngredient(string $regel): array {
    $eenheden = 'kg|gram|gr|g|ml|cl|dl|liter|l|el|tl|kopjes?|teentjes?|snufjes?|stuks?|blikjes?|blikken?'
        . '|plakjes?|plakken|bosjes?|takjes?|handjes?|pakjes?|zakjes?|scheutjes?|sneetjes?';
    $patroon = '/^\s*(?:(\d+(?:[.,]\d+)?)\s*)?(?:(' . $eenheden . ')(?=\s|$)\s*)?(.+)$/iu';

    if (!preg_match($patroon, $regel, $m)) {
        return [null, null, $regel];
    }

    // Zelfde schrijfwijze als de bestaande data in de database (gram, teen, stuks, ...)
    $zelfde = ['g' => 'gram', 'gr' => 'gram', 'teentje' => 'teen', 'teentjes' => 'teen',
               'stuk' => 'stuks', 'snufjes' => 'snufje', 'sneetje' => 'sneetjes'];

    $hoeveelheid = $m[1] !== '' ? (float)str_replace(',', '.', $m[1]) : null;
    $eenheid     = $m[2] !== '' ? mb_strtolower($m[2]) : null;
    if ($eenheid !== null) $eenheid = $zelfde[$eenheid] ?? $eenheid;

    return [$hoeveelheid, $eenheid, trim($m[3])];
}

$categorieen = $pdo->query('SELECT id, naam FROM categorieen ORDER BY naam')->fetchAll();

// Foto's worden als bestanden bewaard; de database bevat alleen het pad.
$kolommen = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
    ? $pdo->query('PRAGMA table_info(recepten)')->fetchAll(PDO::FETCH_COLUMN, 1)
    : $pdo->query('SHOW COLUMNS FROM recepten')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('afbeelding_url', $kolommen, true)) {
    $pdo->exec('ALTER TABLE recepten ADD COLUMN afbeelding_url VARCHAR(255) DEFAULT NULL');
}

// Maximale fotogrootte: 5 MB, maar nooit meer dan wat de server toestaat
$maxFoto = min(5 * 1024 * 1024, iniBytes((string)ini_get('upload_max_filesize')), iniBytes((string)ini_get('post_max_size')));
$maxFotoMb = max(1, (int)floor($maxFoto / 1048576));

$fouten = [];
$titel = '';
$tijd = '';
$categorieId = 0;
$ingredienten = ['', '', ''];
$stappen = ['', ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Is het formulier groter dan post_max_size, dan is $_POST helemaal leeg
    if (!$_POST && !$_FILES) {
        $fouten[] = "Het formulier is te groot. Kies een kleinere foto (maximaal $maxFotoMb MB).";
    } else {
        $titel       = trim($_POST['titel'] ?? '');
        $tijd        = (int)($_POST['bereidingstijd'] ?? 0);
        $categorieId = (int)($_POST['categorie'] ?? 0);

        $ruwIng  = is_array($_POST['ingredienten'] ?? null) ? $_POST['ingredienten'] : [];
        $ruwStap = is_array($_POST['stappen'] ?? null) ? $_POST['stappen'] : [];
        $ingredienten = array_values(array_filter(array_map('trim', array_map('strval', $ruwIng)), 'strlen'));
        $stappen      = array_values(array_filter(array_map('trim', array_map('strval', $ruwStap)), 'strlen'));

        // ---- Validatie ----
        if ($titel === '' || mb_strlen($titel) > 150) $fouten[] = 'Vul een titel in (maximaal 150 tekens).';
        if ($tijd < 1 || $tijd > 1440) $fouten[] = 'Vul de bereidingstijd in minuten in (1 tot 1440).';
        if (!$categorieen) {
            $fouten[] = 'Er staan nog geen categorieën in de database.';
        } elseif (!in_array($categorieId, array_column($categorieen, 'id'))) {
            $fouten[] = 'Kies een categorie.';
        }
        $gebruikerCheck = $pdo->prepare('SELECT COUNT(*) FROM gebruikers WHERE id = ?');
        $gebruikerCheck->execute([$gebruikerId]);
        if (!$gebruikerCheck->fetchColumn()) $fouten[] = 'Gebruiker niet gevonden. Log eerst in.';
        if (!$ingredienten) $fouten[] = 'Voeg minstens één ingrediënt toe.';
        if (!$stappen)      $fouten[] = 'Voeg minstens één stap toe.';

        // ---- Foto controleren (optioneel) ----
        $fotoExt = null;   // extensie op basis van het gecontroleerde bestandstype

        $fotoFout = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($fotoFout !== UPLOAD_ERR_NO_FILE) {
            if ($fotoFout === UPLOAD_ERR_INI_SIZE || $fotoFout === UPLOAD_ERR_FORM_SIZE) {
                $fouten[] = "De foto is te groot (maximaal $maxFotoMb MB).";
            } elseif ($fotoFout !== UPLOAD_ERR_OK) {
                $fouten[] = 'De foto kon niet worden geüpload.';
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['foto']['tmp_name']);
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    $fouten[] = 'Upload een jpg-, png- of webp-foto.';
                } elseif ($_FILES['foto']['size'] > $maxFoto) {
                    $fouten[] = "De foto is te groot (maximaal $maxFotoMb MB).";
                } else {
                    $fotoExt  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
                }
            }
        }

        // ---- Opslaan ----
        if (!$fouten) {
            $fotoPad = null;   // opruimen als opslaan mislukt
            try {
                $pdo->beginTransaction();

                // Stappen genummerd opslaan, zoals de bestaande recepten ("1. ...\n2. ...")
                $genummerd = [];
                foreach ($stappen as $i => $stap) {
                    $genummerd[] = ($i + 1) . '. ' . $stap;
                }

                // kolom => [waarde, type]
                $velden = [
                    'titel'              => [$titel, PDO::PARAM_STR],
                    'bereidingswijze'    => [implode("\n", $genummerd), PDO::PARAM_STR],
                    'bereidingstijd_min' => [$tijd, PDO::PARAM_INT],
                    'gebruiker_id'       => [$gebruikerId, PDO::PARAM_INT],
                    'categorie_id'       => [$categorieId, PDO::PARAM_INT],
                ];

                if ($fotoExt !== null) {
                    $map = __DIR__ . '/uploads';
                    if (!is_dir($map) && !mkdir($map, 0755, true) && !is_dir($map)) {
                        throw new RuntimeException('Uploadmap aanmaken mislukt.');
                    }
                    $bestandsnaam = bin2hex(random_bytes(8)) . '.' . $fotoExt;
                    $fotoPad = $map . '/' . $bestandsnaam;
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPad)) {
                        throw new RuntimeException('Foto opslaan mislukt.');
                    }
                    $velden['afbeelding_url'] = ['uploads/' . $bestandsnaam, PDO::PARAM_STR];
                }

                $stmt = $pdo->prepare(
                    'INSERT INTO recepten (' . implode(', ', array_keys($velden)) . ')
                     VALUES (:' . implode(', :', array_keys($velden)) . ')'
                );
                foreach ($velden as $kolom => [$waarde, $type]) {
                    $stmt->bindValue(':' . $kolom, $waarde, $type);
                }
                $stmt->execute();
                $receptId = (int)$pdo->lastInsertId();

                $zoek    = $pdo->prepare('SELECT id FROM ingredienten WHERE LOWER(naam) = ?');
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
                        $mooi = mb_strtoupper(mb_substr($naam, 0, 1)) . mb_substr($naam, 1);   // "spaghetti" -> "Spaghetti"
                        $maakIng->execute([$mooi, $eenheid]);
                        $ingId = $pdo->lastInsertId();
                    }
                    $koppel->execute([$receptId, $ingId, $hoeveelheid, $eenheid]);
                }

                $pdo->commit();
                header('Location: ToevoegPagina.php?opgeslagen=1');   // voorkomt dubbel opslaan bij F5
                exit;

            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                if ($fotoPad && is_file($fotoPad)) unlink($fotoPad);   // geen losse foto's laten staan
                $fouten[] = 'Opslaan is mislukt. Probeer het opnieuw.';
                // Tijdens ontwikkelen handig: $fouten[] = $ex->getMessage();
            }
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
                <a href="ToevoegPagina.php" class="btn btn--outline">Annuleren</a>
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
                           accept="image/jpeg,image/png,image/webp" data-max-bytes="<?= $maxFoto ?>">
                </label>

                <div class="field">
                    <label for="titel">Titel</label>
                    <input type="text" id="titel" name="titel" maxlength="150" value="<?= e($titel) ?>" required>
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
