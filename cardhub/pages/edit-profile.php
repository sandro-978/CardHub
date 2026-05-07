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
    'SELECT id, username, email FROM users WHERE id = $1',
    [currentUserId()]
);

if (!$result || pg_num_rows($result) !== 1) {
    die('Errore: profilo utente non trovato.');
}

$user = pg_fetch_assoc($result);

$pageTitle = 'Modifica profilo';
require __DIR__ . '/../includes/header.php';
?>

<div class="cardhub-panel">
    <h1 class="h3 mb-3">Modifica profilo</h1>

    <form action="/auth/update-profile-handler.php" method="POST" class="cardhub-form">
        <div class="mb-3">
            <label for="inputUsername" class="form-label">Username</label>
            <input
                type="text"
                id="inputUsername"
                name="inputUsername"
                class="form-control"
                value="<?= htmlspecialchars($user['username']) ?>"
                minlength="3"
                maxlength="60"
                required
            >
        </div>

        <div class="mb-3">
            <label for="inputEmail" class="form-label">Email</label>
            <input
                type="email"
                id="inputEmail"
                name="inputEmail"
                class="form-control"
                value="<?= htmlspecialchars($user['email']) ?>"
                maxlength="120"
                required
            >
        </div>

        <div class="mb-3">
            <label for="inputPassword" class="form-label">password</label>
            <input
                type="password"
                id="inputPassword"
                name="inputPassword"
                class="form-control"
                value="<?= htmlspecialchars($user['email']) ?>"
                maxlength="120"
                required
            >
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Salva modifiche
            </button>

            <a href="/pages/profile.php" class="btn btn-outline-secondary">
                Annulla
            </a>

            <a href="/auth/delete-account.php" class="btn btn-outline-secondary">
                elimina account
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>