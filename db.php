<?php
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

// Verbinding maken
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

// ---------- Kleine hulpfuncties ----------

// Veilig tekst tonen in HTML
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

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

// Alleen simpele interne adressen toestaan (tegen open redirects)
function safe_redirect_target(?string $url, string $fallback = 'index.php'): string
{
    if ($url && preg_match('/^[a-z_]+\.php(\?[\w=&%+.\-]*)?$/i', $url)) {
        return $url;
    }
    return $fallback;
}

// 200.0 -> "200", 0.5 -> "0,5"
function format_amount($amount): string
{
    if ($amount === null || $amount === '') {
        return '';
    }
    return rtrim(rtrim(number_format((float) $amount, 2, ',', ''), '0'), ',');
}
