<?php
// Set $pageTitle and $activePage in the page before including this file
$pageTitle  = $pageTitle  ?? 'MijnRecepten';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="index.css">
  <style>
    /* Login / register popup: self-contained, so it looks right on every page */
    #open-login { padding: 0; border: 0; font-size: 16px; cursor: pointer; }

    .modal {
      --white: #ffffff; --off-white: #f4f6f3; --light-green: #a8d8ae; --green: #4a9d5f;
      --dark-green: #1e3a2d; --text-soft: #5b6f63; --border: #dfe8e1;
      box-sizing: border-box;
      position: fixed; inset: 0; margin: auto;
      width: min(420px, calc(100% - 32px));
      padding: 40px 36px 32px;
      border: 1px solid var(--border);
      border-radius: 28px;
      background: var(--white);
      color: var(--dark-green);
      font-family: "Inter", sans-serif; font-size: 16px; line-height: 1.5;
      box-shadow: 0 24px 60px rgba(30, 58, 45, 0.25);
    }
    .modal *, .modal *::before, .modal *::after { box-sizing: border-box; }
    .modal[open] { animation: modal-in 0.2s ease-out; }
    .modal::backdrop { background: rgba(30, 58, 45, 0.55); }
    @keyframes modal-in { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

    .modal h2 { margin: 0; font-family: "Poppins", sans-serif; font-size: 30px; font-weight: 600; line-height: 1.2; }
    .modal-sub { margin: 8px 0 28px; color: var(--text-soft); }

    .modal-close {
      position: absolute; top: 16px; right: 16px;
      width: 40px; height: 40px; padding: 0;
      border: 0; border-radius: 50%;
      background: var(--off-white); color: var(--dark-green);
      font-size: 24px; line-height: 1; cursor: pointer;
    }
    .modal-close:hover { background: var(--border); }

    .auth-view[hidden], .form-error[hidden] { display: none; }
    .auth-form { display: flex; flex-direction: column; gap: 18px; }

    .field { display: flex; flex-direction: column; gap: 6px; font-weight: 500; }
    .field input {
      width: 100%; padding: 12px 18px;
      border: 1.5px solid var(--border); border-radius: 999px;
      background: var(--off-white); color: var(--dark-green);
      font: inherit; font-weight: 400;
    }
    .field input::placeholder { color: var(--text-soft); }
    .field input:focus { border-color: var(--green); background: var(--white); outline: none; }

    .login-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 14px; }
    .modal .check { display: flex; align-items: center; gap: 10px; margin: 0; cursor: pointer; }
    .modal .check input {
      appearance: none; -webkit-appearance: none;
      width: 20px; height: 20px; margin: 0;
      border: 2px solid var(--green); border-radius: 6px;
      background: var(--white); cursor: pointer;
    }
    .modal .check input:checked { background: var(--green); box-shadow: inset 0 0 0 3px var(--white); }

    .modal .link { color: var(--green); font-weight: 500; text-decoration: none; }
    .modal .link:hover { text-decoration: underline; }

    .btn {
      padding: 13px 24px; border: 0; border-radius: 999px;
      background: var(--green); color: var(--white);
      font-family: "Poppins", sans-serif; font-size: 16px; font-weight: 600; cursor: pointer;
    }
    .btn:hover { background: var(--dark-green); }

    .form-error { margin: 0; padding: 10px 18px; border-radius: 16px; background: #fdecea; color: #9b2c2c; font-size: 14px; }
    .modal-foot { margin: 24px 0 0; text-align: center; color: var(--text-soft); font-size: 15px; }

    .modal :focus-visible, #open-login:focus-visible { outline: 3px solid var(--light-green); outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { .modal[open] { animation: none; } }
  </style>
</head>
<body>

  <header class="site-header">
    <a class="logo" href="../index.php">Mijn<span>Recepten</span></a>

    <nav class="main-nav">
      <a href="../index.php" class="<?= $activePage === 'recepten' ? 'active' : '' ?>">Recepten</a>
      <a href="favorieten.php" class="<?= $activePage === 'favorieten' ? 'active' : '' ?>">Favorieten</a>
      <a href="boodschappenlijst.php" class="<?= $activePage === 'boodschappenlijst' ? 'active' : '' ?>">Boodschappenlijst</a>
    </nav>

    <form class="search" action="../index.php" method="get">
      <input type="search" name="q" placeholder="Zoeken…">
    </form>

    <a class="round-btn round-btn-light" href="bewerken.php" title="Nieuw recept">+</a>
    <button class="round-btn" type="button" id="open-login" title="Inloggen" aria-haspopup="dialog">M</button>
  </header>

  <!-- Login / register popup (front-end only for now) -->
  <dialog class="modal" id="auth-modal" aria-labelledby="login-title">
    <button class="modal-close" type="button" id="close-auth" aria-label="Sluiten">&times;</button>

    <!-- Inloggen -->
    <div class="auth-view" id="view-login">
      <h2 id="login-title">Welkom terug</h2>
      <p class="modal-sub">Log in om je favorieten en boodschappenlijst te bekijken.</p>

      <!-- Later: action="login.php" method="post" -->
      <form class="auth-form" id="login-form" novalidate>
        <label class="field">
          <span>E-mailadres</span>
          <input type="email" name="email" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
        </label>

        <label class="field">
          <span>Wachtwoord</span>
          <input type="password" name="wachtwoord" placeholder="Je wachtwoord" autocomplete="current-password" required>
        </label>

        <div class="login-row">
          <label class="check check-small"><input type="checkbox" name="onthouden"> Onthoud mij</label>
          <a class="link" href="#">Wachtwoord vergeten?</a>
        </div>

        <button class="btn" type="submit">Inloggen</button>
      </form>

      <p class="modal-foot">Nog geen account? <a class="link" href="#" data-switch="register">Account maken</a></p>
    </div>

    <!-- Account maken -->
    <div class="auth-view" id="view-register" hidden>
      <h2 id="register-title">Account maken</h2>
      <p class="modal-sub">Bewaar je favoriete recepten en maak boodschappenlijstjes.</p>

      <!-- Later: action="registreren.php" method="post" -->
      <form class="auth-form" id="register-form" novalidate>
        <label class="field">
          <span>Naam</span>
          <input type="text" name="naam" placeholder="Je voornaam" autocomplete="name" required>
        </label>

        <label class="field">
          <span>E-mailadres</span>
          <input type="email" name="email" placeholder="naam@voorbeeld.nl" autocomplete="email" required>
        </label>

        <label class="field">
          <span>Wachtwoord</span>
          <input type="password" name="wachtwoord" placeholder="Minimaal 8 tekens" autocomplete="new-password" minlength="8" required>
        </label>

        <label class="field">
          <span>Herhaal wachtwoord</span>
          <input type="password" name="wachtwoord2" placeholder="Typ je wachtwoord nog eens" autocomplete="new-password" required>
        </label>

        <p class="form-error" id="register-error" role="alert" hidden></p>

        <button class="btn" type="submit">Account maken</button>
      </form>

      <p class="modal-foot">Heb je al een account? <a class="link" href="#" data-switch="login">Inloggen</a></p>
    </div>
  </dialog>
