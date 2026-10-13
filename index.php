<?php
// Hardcoded for now. Later this array comes from the database.
$recipes = [
    ['id' => 1, 'title' => 'Pasta pomodoro',         'category' => 'Hoofdgerecht', 'minutes' => 25, 'favorite' => true],
    ['id' => 2, 'title' => 'Havermoutpannenkoekjes', 'category' => 'Ontbijt',      'minutes' => 15, 'favorite' => false],
    ['id' => 3, 'title' => 'Appeltaart',             'category' => 'Dessert',      'minutes' => 90, 'favorite' => false],
    ['id' => 4, 'title' => 'Groentecurry',           'category' => 'Hoofdgerecht', 'minutes' => 35, 'favorite' => false],
    ['id' => 5, 'title' => 'Avocadotoast',           'category' => 'Ontbijt',      'minutes' => 10, 'favorite' => false],
    ['id' => 6, 'title' => 'Chocolademousse',        'category' => 'Dessert',      'minutes' => 20, 'favorite' => false],
];

$categories = ['Ontbijt', 'Hoofdgerecht', 'Dessert'];
$selectedCategory = $_GET['categorie'] ?? '';

$pageTitle  = 'Al je recepten - MijnRecepten';
$activePage = 'recepten';
include 'includes/header.php';
?>
    <section class="intro">
        <h1>Al je recepten</h1>
        <p>Snel, simpel en zonder gedoe – <?= count($recipes) ?> recepten voor jou.</p>
    </section>

    <div class="layout">

        <aside class="filters">
            <h3>Filter</h3>
            <label class="check"><input type="checkbox" name="vegetarisch"> Vegetarisch</label>
            <label class="check"><input type="checkbox" name="snel"> Onder 30 minuten</label>
            <label class="check"><input type="checkbox" name="glutenvrij"> Glutenvrij</label>
            <label class="check"><input type="checkbox" name="favorieten"> Favorieten</label>

            <h3>Categorie</h3>
            <div class="pills">
                <?php foreach ($categories as $category): ?>
                    <a class="pill <?= $selectedCategory === $category ? 'active' : '' ?>"
                       href="?categorie=<?= urlencode($category) ?>"><?= htmlspecialchars($category) ?></a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="recipes">
            <div class="recipes-top">
                <h2>Recepten</h2>
                <select class="sort" name="sorteer">
                    <option>Sorteer</option>
                    <option>Nieuwste eerst</option>
                    <option>Snelste eerst</option>
                    <option>A - Z</option>
                </select>
            </div>

            <div class="grid">
                <?php foreach ($recipes as $recipe): ?>
                    <article class="card">
                        <a class="card-link" href="recept.php?id=<?= $recipe['id'] ?>">
                            <div class="card-img"></div>
                            <div class="card-body">
                                <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                                <p><?= htmlspecialchars($recipe['category']) ?> · <?= $recipe['minutes'] ?> min</p>
                            </div>
                        </a>
                        <button class="fav <?= $recipe['favorite'] ? 'is-fav' : '' ?>" type="button" aria-label="Favoriet">
                            <?= $recipe['favorite'] ? '♥' : '♡' ?>
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>
        </main>

    </div>

<?php include 'includes/footer.php'; ?>