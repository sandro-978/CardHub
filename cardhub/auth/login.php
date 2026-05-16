<?php
require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';

$errorCode = $_GET['error'] ?? $_GET['errore'] ?? '';
$errorMessages = [
    'invalid' => 'Email o password non corretti.',
    'required' => 'Inserisci email e password.',
    'unauthorized' => 'Devi effettuare il login per continuare.'
];

$errorMessage = $errorMessages[$errorCode] ?? '';
?>

<link rel="stylesheet" href="/assets/css/auth-pages.css">

<section class="auth-page">
    <div class="auth-shell">
        <div class="auth-info-panel">
            <p class="auth-eyebrow">CardHub</p>
            <h1>Accedi al tuo marketplace</h1>
            <p>
                Entra nel tuo account per pubblicare annunci, gestire le carte,
                controllare i messaggi e seguire le trattative con altri utenti.
            </p>

            <div class="auth-info-list">
                <div>
                    <strong>Annunci</strong>
                    <span>Gestisci vendite e modifiche</span>
                </div>

                <div>
                    <strong>Messaggi</strong>
                    <span>Contatta acquirenti e venditori</span>
                </div>

                <div>
                    <strong>Profilo</strong>
                    <span>Controlla la tua area personale</span>
                </div>
            </div>
        </div>

        <div class="auth-form-card">
            <div class="auth-form-header">
                <span>Accesso</span>
                <h2>Login</h2>
                <p>Inserisci le tue credenziali per continuare.</p>
            </div>

            <?php if ($errorMessage !== ''): ?>
                <div class="auth-alert error">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form action="/auth/login-handler.php" method="POST" class="auth-form">
                <div class="auth-field">
                    <label class="form-label" for="inputEmail">Email</label>
                    <input
                        class="form-control"
                        id="inputEmail"
                        name="inputEmail"
                        type="email"
                        placeholder="nome@email.it"
                        required
                        autofocus
                    >
                </div>

                <div class="auth-field">
                    <label class="form-label" for="inputPassword">Password</label>
                    <input
                        class="form-control"
                        id="inputPassword"
                        name="inputPassword"
                        type="password"
                        placeholder="La tua password"
                        required
                    >
                </div>

                <button class="btn btn-warning auth-submit" type="submit">
                    Entra
                </button>
            </form>

            <div class="auth-secondary-action">
                <span>Non hai ancora un account?</span>
                <a href="/auth/register.php">Registrati</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>