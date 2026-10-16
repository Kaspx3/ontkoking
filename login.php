<?php
require_once 'db.php';

$next = safe_redirect_target($_GET['next'] ?? $_POST['next'] ?? '');

if (current_user()) {
    header('Location: ' . $next);
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['wachtwoord'] ?? '';

    $stmt = $pdo->prepare('SELECT id, naam, email, wachtwoord_hash, rol FROM gebruikers WHERE email = ?');
    $stmt->execute([$email]);
    $found = $stmt->fetch();

    if ($found && password_verify($password, $found['wachtwoord_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int) $found['id'],
            'naam'  => $found['naam'],
            'email' => $found['email'],
            'rol'   => $found['rol'],
        ];
        header('Location: ' . $next);
        exit;
    }
    $error = 'E-mailadres of wachtwoord klopt niet.';
}

$pageTitle  = 'Inloggen - MijnRecepten';
$activePage = '';
include 'includes/header.php';
?>
    <main class="auth-page">
        <div class="auth-card">
            <h1>Welkom terug</h1>
            <p class="auth-sub">Log in om je favorieten en boodschappenlijst te bekijken.</p>

            <form class="auth-form" method="post">
                <input type="hidden" name="next" value="<?= e($next) ?>">

                <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>

                <label class="field">
                    <span>E-mailadres</span>
                    <input type="email" name="email" value="<?= e($email) ?>" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
                </label>

                <label class="field">
                    <span>Wachtwoord</span>
                    <input type="password" name="wachtwoord" placeholder="Je wachtwoord" autocomplete="current-password" required>
                </label>

                <button class="btn" type="submit">Inloggen</button>
            </form>

            <p class="auth-foot">Nog geen account? <a class="link" href="register.php">Account maken</a></p>
        </div>
    </main>

<?php include 'includes/footer.php'; ?>
