<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$db = getDbConnection();
$userId = currentUserId();

/*
 * Conta gli annunci attivi dell'utente corrente.
 * Se vuoi contare TUTTI gli annunci attivi del marketplace,
 * rimuovi "AND user_id = $1" dalla query.
 */
$activeListingsResult = pg_query_params(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM listings
    WHERE status = $1
      AND user_id = $2
    ',
    ['active', $userId]
);

if (!$activeListingsResult) {
    die('Errore durante il conteggio degli annunci: ' . htmlspecialchars(pg_last_error($db)));
}

$activeListingsRow = pg_fetch_assoc($activeListingsResult);
$activeListingsCount = (int)($activeListingsRow['total'] ?? 0);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="cardhub-panel">
    <h1 class="h3 mb-4">Dashboard</h1>

    <p class="text-muted">
        Benvenuto, <strong><?= htmlspecialchars(currentUserName()) ?></strong>.
    </p>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-muted">Annunci attivi</h2>

                    <p class="display-6 mb-0">
                        <?= $activeListingsCount ?>
                    </p>

                    <small class="text-muted">
                        Annunci attualmente pubblicati da te
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6">Azioni rapide</h2>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="/pages/create-listing.php" class="btn btn-primary">
                            Crea nuovo annuncio
                        </a>

                        <a href="/pages/my-listings.php" class="btn btn-outline-primary">
                            I miei annunci
                        </a>

                        <a href="/pages/marketplace.php" class="btn btn-outline-secondary">
                            Vai al marketplace
                        </a>

                        <a href="/pages/profile.php" class="btn btn-outline-dark">
                            Profilo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>