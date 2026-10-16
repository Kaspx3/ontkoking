<?php
// =========================================================
// Hulpfuncties die op meerdere pagina's gebruikt worden.
// Wordt geladen door db.php.
// =========================================================


/* ---------- Algemeen ---------- */

// Veilig tekst tonen in HTML
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// 200.0 -> "200", 0.5 -> "0,5"
function format_amount($amount): string
{
    if ($amount === null || $amount === '') {
        return '';
    }
    return rtrim(rtrim(number_format((float) $amount, 2, ',', ''), '0'), ',');
}

// Alleen simpele interne adressen toestaan (tegen open redirects)
function safe_redirect_target(?string $url, string $fallback = 'index.php'): string
{
    if ($url && preg_match('/^[a-z_]+\.php(\?[\w=&%+.\-]*)?$/i', $url)) {
        return $url;
    }
    return $fallback;
}

// Lijst uit een formulier ("ingredienten[]") opschonen: lege regels weg
function clean_lines($raw): array
{
    if (!is_array($raw)) {
        return [];
    }
    $lines = array_map(fn($line) => trim((string) $line), array_filter($raw, 'is_scalar'));
    return array_values(array_filter($lines, 'strlen'));
}


/* ---------- Inloggen ---------- */

// Ingelogde gebruiker, of null
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

// Stuur bezoekers zonder account naar de loginpagina
function require_login(): void
{
    if (!current_user()) {
        $next = basename($_SERVER['SCRIPT_NAME']);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $next .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: login.php?next=' . urlencode($next));
        exit;
    }
}


/* ---------- Ingrediënten en stappen ---------- */

// "400 g spaghetti" -> [400.0, 'gram', 'spaghetti']
function parse_ingredient(string $line): array
{
    $units = 'kg|gram|gr|g|ml|cl|dl|liter|l|el|tl|kopjes?|teentjes?|tenen|teen|snufjes?|stuks?|blikjes?|blikken?'
           . '|plakjes?|plakken|bosjes?|takjes?|handjes?|pakjes?|zakjes?|scheutjes?|sneetjes?';
    $pattern = '/^\s*(?:(\d+(?:[.,]\d+)?)\s*)?(?:(' . $units . ')(?=\s|$)\s*)?(.+)$/iu';

    if (!preg_match($pattern, $line, $m)) {
        return [null, null, trim($line)];
    }

    // Eén schrijfwijze per eenheid in de database
    $normalize = [
        'g' => 'gram', 'gr' => 'gram', 'teentje' => 'teen', 'teentjes' => 'teen', 'tenen' => 'teen',
        'stuk' => 'stuks', 'snufjes' => 'snufje', 'sneetje' => 'sneetjes',
    ];

    $amount = $m[1] !== '' ? (float) str_replace(',', '.', $m[1]) : null;
    $unit   = $m[2] !== '' ? mb_strtolower($m[2]) : null;
    if ($unit !== null) {
        $unit = $normalize[$unit] ?? $unit;
    }

    return [$amount, $unit, trim($m[3])];
}

// Andersom: een rij uit de database weer als tekstregel ("400 gram spaghetti")
function ingredient_to_line(array $row): string
{
    $line = format_amount($row['hoeveelheid']) . ' ' . $row['eenheid'] . ' ' . $row['naam'];
    return trim(preg_replace('/\s+/', ' ', $line));
}

// Vervangt alle ingrediënten van een recept door de opgegeven regels.
// Roep dit aan binnen een transactie.
function save_recipe_ingredients(PDO $pdo, int $recipeId, array $lines): void
{
    $pdo->prepare('DELETE FROM recept_ingredienten WHERE recept_id = ?')->execute([$recipeId]);

    $find   = $pdo->prepare('SELECT id FROM ingredienten WHERE LOWER(naam) = ?');
    $create = $pdo->prepare('INSERT INTO ingredienten (naam, standaard_eenheid) VALUES (?, ?)');
    $link   = $pdo->prepare(
        'INSERT INTO recept_ingredienten (recept_id, ingredient_id, hoeveelheid, eenheid) VALUES (?, ?, ?, ?)'
    );

    foreach ($lines as $line) {
        [$amount, $unit, $name] = parse_ingredient($line);
        $name = mb_substr(mb_strtolower($name), 0, 100);
        if ($name === '') {
            continue;
        }

        // Ingrediënt hergebruiken als het al bestaat, anders aanmaken
        $find->execute([$name]);
        $ingredientId = $find->fetchColumn();
        if (!$ingredientId) {
            $create->execute([$name, $unit]);
            $ingredientId = $pdo->lastInsertId();
        }
        $link->execute([$recipeId, $ingredientId, $amount, $unit]);
    }
}

// Bereidingswijze (één stap per regel) -> lijst stappen, zonder eventuele "1. " ervoor
function steps_from_text(string $text): array
{
    $steps = [];
    foreach (preg_split('/\R/', $text) as $line) {
        $line = preg_replace('/^\d+[.)]\s*/', '', trim($line));
        if ($line !== '') {
            $steps[] = $line;
        }
    }
    return $steps;
}


/* ---------- Foto's ---------- */
// Foto's staan als bestand in uploads/; de database bewaart alleen het pad.

const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// "2M" uit php.ini omrekenen naar bytes
function ini_bytes(string $value): int
{
    $value  = trim($value);
    $number = (int) $value;
    return match (strtolower(substr($value, -1))) {
        'g'     => $number * 1024 ** 3,
        'm'     => $number * 1024 ** 2,
        'k'     => $number * 1024,
        default => $number,
    };
}

// Maximale fotogrootte: 5 MB, maar nooit meer dan wat de server toestaat
function max_photo_bytes(): int
{
    return min(
        5 * 1024 * 1024,
        ini_bytes((string) ini_get('upload_max_filesize')),
        ini_bytes((string) ini_get('post_max_size'))
    );
}

// Controleert een upload uit $_FILES. Geeft [extensie, null] of [null, foutmelding].
function check_photo(array $file, int $maxBytes): array
{
    $maxMb = max(1, (int) floor($maxBytes / 1048576));

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, "De foto is te groot (maximaal $maxMb MB)."];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'De foto kon niet worden geüpload.'];
    }

    // Het echte bestandstype controleren, niet de naam die de gebruiker opgeeft
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(PHOTO_TYPES[$mime])) {
        return [null, 'Upload een jpg-, png- of webp-foto.'];
    }
    if ($file['size'] > $maxBytes) {
        return [null, "De foto is te groot (maximaal $maxMb MB)."];
    }
    return [PHOTO_TYPES[$mime], null];
}

// Verplaatst de upload naar uploads/ onder een willekeurige naam. Geeft het pad terug.
function store_photo(array $file, string $extension): string
{
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Uploadmap aanmaken mislukt.');
    }

    $name = bin2hex(random_bytes(8)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Foto opslaan mislukt.');
    }
    return 'uploads/' . $name;
}

// Verwijdert een eerder geüpload bestand (alleen uit uploads/)
function delete_upload(?string $path): void
{
    if ($path && preg_match('#^uploads/[a-f0-9]+\.(jpg|png|webp)$#', $path)) {
        $file = dirname(__DIR__) . '/' . $path;
        if (is_file($file)) {
            unlink($file);
        }
    }
}
