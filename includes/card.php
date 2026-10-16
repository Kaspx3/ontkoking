<?php
// Eén receptkaart. Verwacht $recipe (rij uit de database) en $backUrl.
?>
<article class="card">
    <a class="card-link" href="recept.php?id=<?= (int) $recipe['id'] ?>">
        <?php if (!empty($recipe['afbeelding_url'])): ?>
            <img class="card-photo" src="<?= e($recipe['afbeelding_url']) ?>" alt="">
        <?php else: ?>
            <div class="card-img"></div>
        <?php endif; ?>
        <div class="card-body">
            <h3><?= e($recipe['titel']) ?></h3>
            <p>
                <?= e($recipe['categorie'] ?? 'Zonder categorie') ?>
                <?php if ($recipe['bereidingstijd_min']): ?> · <?= (int) $recipe['bereidingstijd_min'] ?> min<?php endif; ?>
            </p>
        </div>
    </a>
    <form class="fav-form" action="favoriet.php" method="post">
        <input type="hidden" name="recept_id" value="<?= (int) $recipe['id'] ?>">
        <input type="hidden" name="terug" value="<?= e($backUrl) ?>">
        <button class="fav <?= $recipe['is_fav'] ? 'is-fav' : '' ?>" type="submit"
                aria-label="<?= $recipe['is_fav'] ? 'Verwijder uit favorieten' : 'Voeg toe aan favorieten' ?>">
            <?= $recipe['is_fav'] ? '♥' : '♡' ?>
        </button>
    </form>
</article>
