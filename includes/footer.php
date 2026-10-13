<script>
  // Login / register popup (front-end only)
  (function () {
    var modal = document.getElementById('auth-modal');
    var openBtn = document.getElementById('open-login');
    var closeBtn = document.getElementById('close-auth');
    if (!modal || !openBtn) return;

    var views = {
      login: document.getElementById('view-login'),
      register: document.getElementById('view-register')
    };
    var titles = { login: 'login-title', register: 'register-title' };

    function showView(name) {
      Object.keys(views).forEach(function (key) {
        views[key].hidden = key !== name;
      });
      modal.setAttribute('aria-labelledby', titles[name]);
      var first = views[name].querySelector('input');
      if (first) first.focus();
    }

    openBtn.addEventListener('click', function () {
      showView('login');
      modal.showModal();
    });
    closeBtn.addEventListener('click', function () { modal.close(); });

    // Click on the dark backdrop closes the popup
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.close();
    });

    // "Account maken" / "Inloggen" links switch between the two views
    modal.querySelectorAll('[data-switch]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        showView(link.getAttribute('data-switch'));
      });
    });

    // Backend is not connected yet, so don't actually submit
    document.getElementById('login-form').addEventListener('submit', function (e) {
      e.preventDefault();
      // TODO: remove this and post the form to login.php
    });

    var registerForm = document.getElementById('register-form');
    var registerError = document.getElementById('register-error');

    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var f = registerForm.elements;
      var message = '';

      if (!f.naam.value.trim() || !f.email.value.trim()) {
        message = 'Vul je naam en e-mailadres in.';
      } else if (f.wachtwoord.value.length < 8) {
        message = 'Je wachtwoord moet minimaal 8 tekens hebben.';
      } else if (f.wachtwoord.value !== f.wachtwoord2.value) {
        message = 'De wachtwoorden komen niet overeen.';
      }

      registerError.textContent = message;
      registerError.hidden = message === '';
      // TODO: if there is no message, post the form to registreren.php
    });
  })();
</script>
</body>
</html>
