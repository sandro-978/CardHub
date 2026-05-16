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

function formatProfileDate(?string $dateValue): string
{
    if (!$dateValue) {
        return 'Data non disponibile';
    }

    $date = date_create($dateValue);

    if (!$date) {
        return $dateValue;
    }

    return date_format($date, 'd/m/Y H:i');
}

$pageTitle = 'Profilo';
require __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/profile.css">

<section class="profile-page">
    <div class="profile-hero">
        <div>
            <p class="profile-eyebrow">Area personale</p>
            <h1>Profilo utente</h1>
            <p>
                Gestisci le informazioni principali del tuo account CardHub,
                accedi rapidamente ai tuoi annunci e controlla le sezioni operative.
            </p>
        </div>

        <div class="profile-avatar">
            <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="profile-alert success">
            Profilo aggiornato correttamente.
        </div>
    <?php endif; ?>

    <div class="profile-layout">
        <article class="profile-card-main">
            <div class="profile-card-header">
                <div>
                    <span>Account</span>
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                </div>
            </div>

            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <span>Username</span>
                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                </div>

                <div class="profile-info-item">
                    <span>Email</span>
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                </div>

                <div class="profile-info-item">
                    <span>Data registrazione</span>
                    <strong><?= htmlspecialchars(formatProfileDate($user['created_at'])) ?></strong>
                </div>
            </div>
        </article>

        <aside class="profile-actions-card">
            <div class="profile-actions-header">
                <span>Azioni rapide</span>
                <h2>Gestione profilo</h2>
            </div>

            <div class="profile-actions-list">
                <a class="btn btn-primary" href="/pages/edit-profile.php">
                    Modifica profilo
                </a>

                <a class="btn btn-outline-primary" href="/pages/dashboard.php">
                    Dashboard
                </a>

                <a class="btn btn-outline-primary" href="/pages/my-listings.php">
                    I miei annunci
                </a>

                <a class="btn btn-outline-secondary" href="/pages/marketplace.php">
                    Marketplace
                </a>
            </div>

            <div class="profile-danger-zone">
                <h3>Zona critica</h3>
                <p>
                    L’eliminazione dell’account rimuove anche gli annunci e le chat collegate.
                </p>

                <a class="btn btn-danger" href="/pages/delete-account.php">
                    Elimina account
                </a>
            </div>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>