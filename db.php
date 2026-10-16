<?php
// Verbinding met de database "ontkoking" (via PDO)
//
//  - Op je eigen computer: SQLite. De database wordt automatisch gebouwd uit
//    ontkoking_database/ontkoking.sql (je hoeft niets te importeren).
//  - Op de Plesk-server: MySQL met de gegevens hieronder.

$lokaal = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1'], true);

try {
    if ($lokaal) {
        $sqlBestand = __DIR__ . '/ontkoking_database/ontkoking.sql';
        $dbBestand  = __DIR__ . '/ontkoking_database/ontkoking.sqlite';

        if (!is_file($sqlBestand)) {
            exit('Bestand niet gevonden: ontkoking_database/ontkoking.sql');
        }

        // Bouwen als de database nog niet bestaat, of als ontkoking.sql nieuwer is dan de database.
        // (Lokaal toegevoegde testrecepten verdwijnen dan, want ontkoking.sql is de bron.)
        if (!is_file($dbBestand) || filemtime($sqlBestand) > filemtime($dbBestand)) {
            require_once __DIR__ . '/db_import.php';
            bouwSqliteUitDump($sqlBestand, $dbBestand);
        }

        $pdo = new PDO('sqlite:' . $dbBestand, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');   // zonder dit negeert SQLite de koppelingen tussen tabellen

    } else {
        $pdo = new PDO('mysql:host=localhost;dbname=ontkoking;charset=utf8mb4', '103469_ontkoking', 'ontkoking123', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
} catch (Throwable $ex) {
    exit($lokaal
        ? 'Database fout: ' . htmlspecialchars($ex->getMessage())
        : 'Kan geen verbinding maken met de database.');
}
