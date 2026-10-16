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

$categories = $pdo->query('SELECT naam FROM categorieen ORDER BY naam')->fetchAll(PDO::FETCH_COLUMN);

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

$pageTitle  = 'Al je recepten - MijnRecepten';
$activePage = 'recepten';
include 'includes/header.php';
?>
    <section class="intro">
        <h1>Al je recepten</h1>
        <p>Snel, simpel en zonder gedoe – <?= count($recipes) ?> <?= count($recipes) === 1 ? 'recept' : 'recepten' ?> voor jou.</p>
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
                    <p>Pas je filters aan of <a class="link" href="ToevoegPagina.html">voeg een nieuw recept toe</a>.</p>
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

<?php include 'includes/footer.php'; ?>
