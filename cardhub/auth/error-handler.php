<?php
$pageTitle = 'Errore';

$errorCode = $_GET['error'] ?? $_GET['code'] ?? 'generic';

$errorMessages = [
    'login' => [
        'title' => 'Accesso non riuscito',
        'message' => 'Email o password non corretti. Controlla le credenziali e riprova.',
    ],
    'register' => [
        'title' => 'Registrazione non riuscita',
        'message' => 'Non è stato possibile completare la registrazione. Verifica i dati inseriti.',
    ],
    'db' => [
        'title' => 'Errore database',
        'message' => 'Si è verificato un problema di connessione o interrogazione del database.',
    ],
    'forbidden' => [
        'title' => 'Operazione non autorizzata',
        'message' => 'Non hai i permessi necessari per eseguire questa operazione.',
    ],
    'not-found' => [
        'title' => 'Risorsa non trovata',
        'message' => 'La pagina o l’elemento richiesto non esiste o non è più disponibile.',
    ],
    'generic' => [
        'title' => 'Si è verificato un errore',
        'message' => 'Qualcosa non è andato a buon fine. Puoi tornare alla home oppure riprovare.',
    ],
];

$error = $errorMessages[$errorCode] ?? $errorMessages['generic'];

require __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/error-page.css">

<section class="error-page">
    <div class="error-card">
        <div class="error-icon">
            !
        </div>

        <p class="error-eyebrow">CardHub</p>

        <h1><?= htmlspecialchars($error['title']) ?></h1>

        <p class="error-message">
            <?= htmlspecialchars($error['message']) ?>
        </p>

        <div class="error-code-box">
            <span>Codice errore</span>
            <strong><?= htmlspecialchars(strtoupper($errorCode)) ?></strong>
        </div>

        <div class="error-actions">
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                Torna indietro
            </button>

            <a href="/auth/login.php" class="btn btn-warning">
                Vai al login
            </a>

            <a href="/index.php" class="btn btn-primary">
                Vai alla home
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>