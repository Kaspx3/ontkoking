<?php
// =========================================================
// Databaseverbinding + sessie. Elke pagina begint hiermee:
//     require_once 'db.php';
// De hulpfuncties (e(), require_login(), ...) staan in includes/functions.php
// =========================================================

// TIJDELIJK: toon de echte foutmelding in plaats van een lege 500-pagina.
// Zet dit op false zodra alles werkt.
$debug = true;
if ($debug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Gegevens van de database op Plesk
$host = 'localhost';
$db   = 'ontkoking';
$user = '103469_ontkoking';
$pass = 'ontkoking123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    echo '<p style="font-family:sans-serif;padding:24px">Geen verbinding met de database.';
    if ($debug) {
        echo '<br><br><strong>' . htmlspecialchars($ex->getMessage()) . '</strong>';
    }
    echo '</p>';
    exit;
}

// Sessie starten (nodig voor inloggen)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/functions.php';
