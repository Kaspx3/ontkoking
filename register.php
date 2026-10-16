<?php
require_once 'db.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
$naam  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam      = trim($_POST['naam'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['wachtwoord'] ?? '';
    $password2 = $_POST['wachtwoord2'] ?? '';

    if ($naam === '' || $email === '') {
        $error = 'Vul je naam en e-mailadres in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Dit e-mailadres lijkt niet te kloppen.';
    } elseif (strlen($password) < 8) {
        $error = 'Je wachtwoord moet minimaal 8 tekens hebben.';
    } elseif ($password !== $password2) {
        $error = 'De wachtwoorden komen niet overeen.';
    } else {
        $check = $pdo->prepare('SELECT 1 FROM gebruikers WHERE email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Er bestaat al een account met dit e-mailadres.';
        } else {
            $pdo->prepare('INSERT INTO gebruikers (naam, email, wachtwoord_hash) VALUES (?, ?, ?)')
                ->execute([$naam, $email, password_hash($password, PASSWORD_DEFAULT)]);

            // Meteen ingelogd na registreren
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int) $pdo->lastInsertId(),
                'naam'  => $naam,
                'email' => $email,
                'rol'   => 'user',
            ];
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle  = 'Account maken - MijnRecepten';
$activePage = '';
include 'includes/header.php';
?>
    <main class="auth-page">
        <div class="auth-card">
            <h1>Account maken</h1>
            <p class="auth-sub">Bewaar je favoriete recepten en maak boodschappenlijstjes.</p>

            <form class="auth-form" method="post">
                <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>

                <label class="field">
                    <span>Naam</span>
                    <input type="text" name="naam" value="<?= e($naam) ?>" placeholder="Je voornaam" autocomplete="name" maxlength="100" required>
                </label>

                <label class="field">
                    <span>E-mailadres</span>
                    <input type="email" name="email" value="<?= e($email) ?>" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
                </label>

                <label class="field">
                    <span>Wachtwoord</span>
                    <input type="password" name="wachtwoord" placeholder="Minimaal 8 tekens" autocomplete="new-password" minlength="8" required>
                </label>

                <label class="field">
                    <span>Herhaal wachtwoord</span>
                    <input type="password" name="wachtwoord2" placeholder="Typ je wachtwoord nog eens" autocomplete="new-password" required>
                </label>

                <button class="btn" type="submit">Account maken</button>
            </form>

            <p class="auth-foot">Heb je al een account? <a class="link" href="login.php">Inloggen</a></p>
        </div>
    </main>

<?php include 'includes/footer.php'; ?>
