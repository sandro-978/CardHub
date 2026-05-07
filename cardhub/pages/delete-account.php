<?php
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$_SESSION['delete_account_token'] = bin2hex(random_bytes(32));

$pageTitle = 'Elimina account';
require __DIR__ . '/../includes/header.php';
?>

<div class="cardhub-panel">
    <h1 class="h3 mb-3 text-danger">Elimina account</h1>

    <div class="alert alert-danger">
        <strong>Attenzione:</strong> l’eliminazione è definitiva.
        Verranno rimossi account, annunci, chat e messaggi collegati.
    </div>

    <form action="/auth/delete-account-handler.php" method="POST" class="cardhub-form">
        <input
            type="hidden"
            name="deleteAccountToken"
            value="<?= htmlspecialchars($_SESSION['delete_account_token']) ?>"
        >

        <div class="mb-3">
            <label for="inputPassword" class="form-label">Conferma password</label>
            <input
                type="password"
                id="inputPassword"
                name="inputPassword"
                class="form-control"
                required
            >
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger">
                Elimina definitivamente
            </button>

            <a href="/pages/profile.php" class="btn btn-outline-secondary">
                Annulla
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>