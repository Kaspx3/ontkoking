<?php
// Verbinding met de SQLite-database (via PDO)
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/ontkoking.sqlite', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');   // zonder dit negeert SQLite de koppelingen tussen tabellen
} catch (PDOException $ex) {
    exit('Kan geen verbinding maken met de database.');
}