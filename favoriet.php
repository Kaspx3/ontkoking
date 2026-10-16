<?php
// Zet een recept aan/uit bij de favorieten van de ingelogde gebruiker
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$back = safe_redirect_target($_POST['terug'] ?? '');

if (!current_user()) {
    header('Location: login.php?next=' . urlencode($back));
    exit;
}

$uid      = current_user()['id'];
$receptId = (int) ($_POST['recept_id'] ?? 0);

$check = $pdo->prepare('SELECT 1 FROM favorieten WHERE gebruiker_id = ? AND recept_id = ?');
$check->execute([$uid, $receptId]);

if ($check->fetch()) {
    $pdo->prepare('DELETE FROM favorieten WHERE gebruiker_id = ? AND recept_id = ?')->execute([$uid, $receptId]);
} else {
    // Alleen toevoegen als het recept echt bestaat
    $pdo->prepare('INSERT INTO favorieten (gebruiker_id, recept_id) SELECT ?, id FROM recepten WHERE id = ?')
        ->execute([$uid, $receptId]);
}

header('Location: ' . $back);
exit;
