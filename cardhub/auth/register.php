<?php
$pageTitle = 'Registrazione';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-card">
    <h1 class="h3 mb-3">Registrazione</h1>
    <form name="registrationForm" action="/auth/register-handler.php" method="POST" onsubmit="return validateRegistrationForm()" class="d-grid gap-3">
        <div>
            <label class="form-label" for="inputUsername">Username</label>
            <input class="form-control" id="inputUsername" name="inputUsername" type="text" required>
            <div class="error-message">Lo username deve avere almeno 3 caratteri.</div>
        </div>
        <div>
            <label class="form-label" for="inputEmail">Email</label>
            <input class="form-control" id="inputEmail" name="inputEmail" type="email" required>
            <div class="error-message">Inserisci una email valida.</div>
        </div>
        <div>
            <label class="form-label" for="inputPassword">Password</label>
            <input class="form-control" id="inputPassword" name="inputPassword" type="password" required>
            <div class="error-message">La password deve avere almeno 8 caratteri.</div>
        </div>
        <button class="btn btn-primary" type="submit">Crea account</button>
    </form>
</div>
<script src="/assets/js/validation.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
