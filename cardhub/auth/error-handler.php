<?php
$pageTitle = 'ERROR';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-card">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <h1 class="h3 mb-3">ERROR</h1>
    <form action="/auth/error-handler.php" method="POST" class="d-grid gap-3">
        <div>
            <label class="form-label">ERRORE</label>
        </div>
        <div>
            <a href="/auth/login.php" class="btn btn-primary">Riprova</a>
            <a href="/" class="btn btn-primary">Home</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
