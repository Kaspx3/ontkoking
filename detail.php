<?php
$pageTitle  = 'Recept – MijnRecepten';
$activePage = 'recepten';
include 'includes/header.php';
?>

    <link rel="stylesheet" href="detail.css">

    <main class="recept">
        <img src="https://placehold.co/800x400" alt="Recept foto" class="recept-foto">

        <h1>Pasta pesto</h1>
        <p class="recept-info">20 min · 2 personen · Makkelijk</p>

        <section class="ingredienten">
            <h2>Ingrediënten</h2>
            <ul>
                <li>250 g pasta</li>
                <li>3 el pesto</li>
                <li>Handje cherrytomaten</li>
            </ul>
        </section>

        <section class="bereiding">
            <h2>Bereiding</h2>
            <ol>
                <li>Kook de pasta volgens de verpakking.</li>
                <li>Halveer de tomaten.</li>
                <li>Meng alles met de pesto.</li>
            </ol>
        </section>
    </main>

<?php include 'includes/footer.php'; ?>