<?php
$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-card">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <h1 class="h3 mb-3">Login</h1>
    <form action="/auth/login-handler.php" method="POST" class="d-grid gap-3">
        <div>
            <label class="form-label" for="inputEmail">Email</label>
            <input class="form-control" id="inputEmail" name="inputEmail" type="email" required autofocus>
        </div>
        <div>
            <label class="form-label" for="inputPassword">Password</label>
            <input class="form-control" id="inputPassword" name="inputPassword" type="password" required>
        </div>
        <button class="btn btn-primary" type="submit">Entra</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
