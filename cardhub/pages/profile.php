<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$db = getDbConnection();

$result = pg_query_params(
    $db,
    'SELECT id, username, email, created_at FROM users WHERE id = $1', 
    [currentUserId()]
);

if (!$result || pg_num_rows($result) !== 1) {
    die('Errore: profilo utente non trovato.');
}

$user = pg_fetch_assoc($result);

$pageTitle = 'Profilo';
require __DIR__ . '/../includes/header.php';
?>

<div class="cardhub-panel">
    <h1 class="h3 mb-3">Profilo utente</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            Profilo aggiornato correttamente.
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <p class="mb-1">
            <strong>Username:</strong>
            <?= htmlspecialchars($user['username']) ?>
        </p>

        <p class="mb-1">
            <strong>Email:</strong>
            <?= htmlspecialchars($user['email']) ?>
        </p>

        <p class="mb-1">
            <strong>Data registrazione:</strong>
            <?= htmlspecialchars($user['created_at']) ?>
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="/pages/edit-profile.php">
            Modifica profilo
        </a>

        <a class="btn btn-primary" href="/pages/dashboard.php">
            Dashboard
        </a>

        <a class="btn btn-primary" href="/pages/my-listings.php">
            I miei annunci
        </a>

        <a class="btn btn-primary" href="/pages/delete-account.php">
            Elimina account
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>