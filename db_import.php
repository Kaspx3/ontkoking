<?php
// Zet een MySQL-dump (phpMyAdmin, ontkoking.sql) om naar een SQLite-database.
// Wordt alleen gebruikt door db.php, op je eigen computer.

function bouwSqliteUitDump(string $sqlBestand, string $dbBestand): void
{
    $tmp = $dbBestand . '.' . uniqid('tmp', true);
    try {
        $pdo = new PDO('sqlite:' . $tmp, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('PRAGMA foreign_keys = OFF');   // de dump vult tabellen in een volgorde die koppelingen schendt

        $tabellen = $pk = $uniek = $index = $auto = $fk = $inserts = [];

        foreach (dumpStatements((string)file_get_contents($sqlBestand)) as $st) {

            if (preg_match('/^CREATE TABLE `(\w+)`\s*\((.*)\)\s*ENGINE\b.*$/is', $st, $m)) {
                foreach (splitOpKomma($m[2]) as $def) {
                    if (!preg_match('/^`(\w+)`\s+(.*)$/s', $def, $c)) continue;
                    $type = preg_replace('/\benum\s*\([^)]*\)/i', 'TEXT', $c[2]);
                    $type = preg_replace('/\s+ON UPDATE current_timestamp(\(\))?/i', '', $type);
                    $type = preg_replace('/current_timestamp\(\)/i', 'CURRENT_TIMESTAMP', $type);
                    $tabellen[$m[1]][$c[1]] = $type;
                }

            } elseif (preg_match('/^ALTER TABLE `(\w+)`\s+(.*)$/is', $st, $m)) {
                $t = $m[1];
                foreach (splitOpKomma($m[2]) as $deel) {
                    if (preg_match('/^ADD PRIMARY KEY\s*\((.+)\)$/is', $deel, $x))          $pk[$t] = $x[1];
                    elseif (preg_match('/^ADD UNIQUE KEY\s+`\w+`\s*\((.+)\)$/is', $deel, $x)) $uniek[$t][] = $x[1];
                    elseif (preg_match('/^ADD KEY\s+`(\w+)`\s*\((.+)\)$/is', $deel, $x))    $index[$t][$x[1]] = $x[2];
                    elseif (preg_match('/^MODIFY\s+`(\w+)`.*AUTO_INCREMENT/is', $deel, $x)) $auto[$t] = $x[1];
                    elseif (preg_match('/^ADD CONSTRAINT\s+`\w+`\s+(FOREIGN KEY.+)$/is', $deel, $x)) $fk[$t][] = $x[1];
                }

            } elseif (stripos($st, 'INSERT INTO') === 0) {
                $inserts[] = $st;
            }
            // SET, START TRANSACTION, COMMIT enz. worden overgeslagen
        }

        $pdo->beginTransaction();
        foreach ($tabellen as $naam => $kolommen) {
            // Eén AUTO_INCREMENT-sleutel wordt in SQLite: INTEGER PRIMARY KEY AUTOINCREMENT
            $enkelAuto = isset($pk[$naam], $auto[$naam]) && trim($pk[$naam], '` ') === $auto[$naam];

            $regels = [];
            foreach ($kolommen as $k => $type) {
                $regels[] = ($enkelAuto && $k === $auto[$naam]) ? "`$k` INTEGER PRIMARY KEY AUTOINCREMENT" : "`$k` $type";
            }
            if (isset($pk[$naam]) && !$enkelAuto) $regels[] = "PRIMARY KEY ({$pk[$naam]})";
            foreach ($uniek[$naam] ?? [] as $u)  $regels[] = "UNIQUE ($u)";
            foreach ($fk[$naam] ?? [] as $f)     $regels[] = $f;

            $pdo->exec("CREATE TABLE `$naam` (\n  " . implode(",\n  ", $regels) . "\n)");
            foreach ($index[$naam] ?? [] as $ix => $cols) {
                $pdo->exec("CREATE INDEX `idx_{$naam}_$ix` ON `$naam` ($cols)");
            }
        }
        foreach ($inserts as $q) $pdo->exec($q);
        $pdo->commit();
        $pdo = null;                                 // bestand loslaten voor het hernoemen

        rename($tmp, $dbBestand);

    } catch (Throwable $ex) {
        $pdo = null;
        if (is_file($tmp)) unlink($tmp);
        throw $ex;
    }
}

// Knipt de dump in losse statements en zet MySQL-teksten ('\n', '\'') om naar SQLite-teksten.
function dumpStatements(string $sql): array
{
    $statements = [];
    $huidig = '';
    $n = strlen($sql);
    $i = 0;

    while ($i < $n) {
        $c = $sql[$i];

        if ($c === '-' && ($sql[$i + 1] ?? '') === '-') {               // -- commentaar
            $einde = strpos($sql, "\n", $i);
            $i = $einde === false ? $n : $einde + 1;
            continue;
        }
        if ($c === '/' && ($sql[$i + 1] ?? '') === '*') {               // /* commentaar */ (ook /*!40101 ... */)
            $einde = strpos($sql, '*/', $i + 2);
            $i = $einde === false ? $n : $einde + 2;
            continue;
        }
        if ($c === "'") {                                               // tekst tussen aanhalingstekens
            $tekst = '';
            $i++;
            while ($i < $n) {
                $ch = $sql[$i];
                if ($ch === '\\' && $i + 1 < $n) {
                    $volgende = $sql[$i + 1];
                    $tekst .= ['n' => "\n", 'r' => "\r", 't' => "\t"][$volgende] ?? $volgende;
                    $i += 2;
                    continue;
                }
                if ($ch === "'") {
                    if (($sql[$i + 1] ?? '') === "'") { $tekst .= "'"; $i += 2; continue; }
                    $i++;
                    break;
                }
                $tekst .= $ch;
                $i++;
            }
            $huidig .= "'" . str_replace("'", "''", $tekst) . "'";
            continue;
        }
        if ($c === ';') {
            if (trim($huidig) !== '') $statements[] = trim($huidig);
            $huidig = '';
            $i++;
            continue;
        }
        $huidig .= $c;
        $i++;
    }
    if (trim($huidig) !== '') $statements[] = trim($huidig);
    return $statements;
}

// Splitst op komma's, maar niet binnen haakjes of tekst
function splitOpKomma(string $tekst): array
{
    $delen = [];
    $huidig = '';
    $diepte = 0;
    $inTekst = false;

    for ($i = 0, $n = strlen($tekst); $i < $n; $i++) {
        $c = $tekst[$i];
        if ($c === "'") {
            $inTekst = !$inTekst;                  // '' (verdubbeld) klapt twee keer om: klopt
        } elseif (!$inTekst) {
            if ($c === '(') $diepte++;
            elseif ($c === ')') $diepte--;
            elseif ($c === ',' && $diepte === 0) { $delen[] = trim($huidig); $huidig = ''; continue; }
        }
        $huidig .= $c;
    }
    if (trim($huidig) !== '') $delen[] = trim($huidig);
    return $delen;
}
