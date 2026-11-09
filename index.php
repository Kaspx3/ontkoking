<?php
require_once 'db.php';

$user = current_user();
$uid  = $user['id'] ?? 0;

// ---------- Filters uit de URL ----------
$q          = trim($_GET['q'] ?? '');
$category   = $_GET['categorie'] ?? '';
$vegetarian = isset($_GET['vegetarisch']);
$quick      = isset($_GET['snel']);
$glutenFree = isset($_GET['glutenvrij']);
$onlyFavs   = isset($_GET['favorieten']);
$sort       = $_GET['sorteer'] ?? 'nieuw';

// Geen filter, zoekopdracht of sortering? Dan tonen we de startpagina met hero en categorieën.
$isBrowsing = $q === '' && $category === '' && !$vegetarian && !$quick && !$glutenFree
           && !$onlyFavs && !isset($_GET['sorteer']) && !isset($_GET['alle']);

$orderBy = [
    'nieuw'  => 'r.aangemaakt_op DESC',
    'snel'   => 'r.bereidingstijd_min ASC',
    'az'     => 'r.titel ASC',
][$sort] ?? 'r.aangemaakt_op DESC';

$where  = [];
$params = [$uid];   // eerste ? hoort bij de favorieten-join

if ($q !== '') {
    $where[] = '(r.titel LIKE ? OR r.beschrijving LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($category !== '') { $where[] = 'c.naam = ?';                   $params[] = $category; }
if ($vegetarian)      { $where[] = 'r.vegetarisch = 1'; }
if ($quick)           { $where[] = 'r.bereidingstijd_min < 30'; }
if ($glutenFree)      { $where[] = 'r.glutenvrij = 1'; }
if ($onlyFavs)        { $where[] = 'f.recept_id IS NOT NULL'; }

$sql = "SELECT r.*, c.naam AS categorie, (f.recept_id IS NOT NULL) AS is_fav
        FROM recepten r
        LEFT JOIN categorieen c ON c.id = r.categorie_id
        LEFT JOIN favorieten f ON f.recept_id = r.id AND f.gebruiker_id = ?"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

// Categorieën met het aantal recepten per categorie (voor de tegels)
$catStats = $pdo->query(
    'SELECT c.naam, COUNT(r.id) AS aantal
     FROM categorieen c
     LEFT JOIN recepten r ON r.categorie_id = c.id
     GROUP BY c.id, c.naam
     ORDER BY c.naam'
)->fetchAll();
$categories = array_column($catStats, 'naam');

// Zoekt een foto in assets/img/ (jpg, jpeg, webp of png). Geen foto gevonden = null.
if (!function_exists('site_image')) {
    function site_image(string $base): ?string
    {
        foreach (['jpg', 'jpeg', 'webp', 'png'] as $ext) {
            $file = "assets/img/$base.$ext";
            if (is_file(__DIR__ . '/' . $file)) {
                return $file;
            }
        }
        return null;
    }
}

// "Hoofdgerecht" -> "hoofdgerecht" (bestandsnaam van de categoriefoto)
function slugify(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
}

// Achtergrondfoto van de hero: assets/img/hero.jpg (of .jpeg/.webp/.png)
$heroImage = site_image('hero');

// Link voor een categorie-pill: behoud de andere filters
function category_link(string $name, string $selected): string
{
    $params = $_GET;
    if ($name === $selected) {
        unset($params['categorie']);      // nog een keer klikken = filter weg
    } else {
        $params['categorie'] = $name;
    }
    return '?' . http_build_query($params);
}

$backUrl = 'index.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

$total = count($recipes);

$pageTitle  = 'Al je recepten - MijnRecepten';
$activePage = 'recepten';
include 'includes/header.php';
?>

<?php if ($isBrowsing): ?>

    <!-- ===== Hero ===== -->
    <section class="hero"<?php if ($heroImage): ?>
             style="background-image: linear-gradient(90deg, rgba(18, 41, 31, 0.92), rgba(18, 41, 31, 0.72) 50%, rgba(18, 41, 31, 0.3)), url('<?= e($heroImage) ?>')"<?php endif; ?>>
        <div class="hero-text">
            <span class="eyebrow">Snel, simpel en zonder gedoe</span>
            <h1>Wat ga jij <em>vandaag</em> koken?</h1>
            <p>Bewaar je favoriete recepten, ontdek nieuwe gerechten en maak met één klik een boodschappenlijst.</p>

            <form class="hero-search" action="index.php" method="get">
                <input type="search" name="q" placeholder="Zoek op gerecht of ingrediënt…" aria-label="Zoeken">
                <button class="btn btn-accent" type="submit">Zoeken</button>
            </form>

            <div class="hero-chips">
                <a href="?snel=1">Onder 30 minuten</a>
                <a href="?vegetarisch=1">Vegetarisch</a>
                <a href="?glutenvrij=1">Glutenvrij</a>
            </div>

            <div class="hero-stats">
                <div><strong><?= $total ?></strong> recepten</div>
                <div><strong><?= count($categories) ?></strong> categorieën</div>
            </div>
        </div>
    </section>

    <!-- ===== Categorieën ===== -->
    <section class="section" id="categorieen">
        <div class="section-head">
            <div>
                <h2>Waar heb je zin in?</h2>
                <p>Kies een categorie en bekijk de recepten.</p>
            </div>
        </div>

        <div class="cat-grid">
            <?php foreach ($catStats as $i => $cat): ?>
                <?php $photo = site_image('categorieen/' . slugify($cat['naam'])); ?>
                <a class="cat-tile tone-<?= $i % 6 ?><?= $photo ? ' has-photo' : '' ?>"
                   href="?categorie=<?= urlencode($cat['naam']) ?>"
                   <?php if ($photo): ?>style="background-image: url('<?= e($photo) ?>')"<?php endif; ?>>
                    <h3><?= e($cat['naam']) ?></h3>
                    <p><?= (int) $cat['aantal'] ?> <?= (int) $cat['aantal'] === 1 ? 'recept' : 'recepten' ?></p>
                    <span class="cat-arrow" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ===== Nieuwste recepten (pas ná de categorieën) ===== -->
    <section class="section">
        <div class="section-head">
            <div>
                <h2>Nieuwste recepten</h2>
                <p>Vers toegevoegd aan je verzameling.</p>
            </div>
            <a class="link" href="?alle=1">Bekijk alle recepten →</a>
        </div>

        <?php if (!$recipes): ?>
            <div class="empty">
                <h3>Nog geen recepten</h3>
                <p><a class="link" href="recept_form.php">Voeg je eerste recept toe</a>.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach (array_slice($recipes, 0, 6) as $recipe): ?>
                    <?php include 'includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

<?php else: ?>

    <!-- ===== Zoekresultaten / gefilterde lijst ===== -->
    <section class="intro">
        <a class="back-link" href="index.php">← Alle categorieën</a>
        <h1>
            <?php if ($category !== ''): ?><?= e($category) ?>
            <?php elseif ($q !== ''): ?>Zoekresultaten voor “<?= e($q) ?>”
            <?php else: ?>Al je recepten<?php endif; ?>
        </h1>
        <p><?= $total ?> <?= $total === 1 ? 'recept' : 'recepten' ?> gevonden.</p>
    </section>

    <div class="layout">

        <aside class="filters">
            <form id="filter-form" method="get">
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                <?php if ($category !== ''): ?><input type="hidden" name="categorie" value="<?= e($category) ?>"><?php endif; ?>

                <h3>Filter</h3>
                <label class="check"><input type="checkbox" name="vegetarisch" onchange="this.form.submit()" <?= $vegetarian ? 'checked' : '' ?>> Vegetarisch</label>
                <label class="check"><input type="checkbox" name="snel" onchange="this.form.submit()" <?= $quick ? 'checked' : '' ?>> Onder 30 minuten</label>
                <label class="check"><input type="checkbox" name="glutenvrij" onchange="this.form.submit()" <?= $glutenFree ? 'checked' : '' ?>> Glutenvrij</label>
                <label class="check"><input type="checkbox" name="favorieten" onchange="this.form.submit()" <?= $onlyFavs ? 'checked' : '' ?>> Favorieten</label>
            </form>

            <h3>Categorie</h3>
            <div class="pills">
                <?php foreach ($categories as $name): ?>
                    <a class="pill <?= $category === $name ? 'active' : '' ?>"
                       href="<?= e(category_link($name, $category)) ?>"><?= e($name) ?></a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="recipes">
            <div class="recipes-top">
                <h2>Recepten</h2>
                <select class="sort" name="sorteer" form="filter-form" onchange="this.form.submit()" aria-label="Sorteer">
                    <option value="nieuw" <?= $sort === 'nieuw' ? 'selected' : '' ?>>Nieuwste eerst</option>
                    <option value="snel"  <?= $sort === 'snel'  ? 'selected' : '' ?>>Snelste eerst</option>
                    <option value="az"    <?= $sort === 'az'    ? 'selected' : '' ?>>A - Z</option>
                </select>
            </div>

            <?php if (!$recipes): ?>
                <div class="empty">
                    <h3>Geen recepten gevonden</h3>
                    <p>Pas je filters aan of <a class="link" href="recept_form.php">voeg een nieuw recept toe</a>.</p>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <?php include 'includes/card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
