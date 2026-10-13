    <?php

    // Gegevens van de database op Plesk
    $host = 'localhost';               // laten staan
    $db = 'ontkoking';               // naam van je database (zoals in phpMyAdmin links staat)
    $user = '103469_ontkoking';            // database-gebruiker uit Plesk
    $pass = 'ontkoking123';      // wachtwoord van die gebruiker

    // Verbinding maken
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // laat fouten zien
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // resultaat als ['titel' => ...]
    ]);