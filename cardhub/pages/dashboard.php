<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$db = getDbConnection();
$userId = currentUserId();

function fetchSingleValue($db, string $query, array $params, string $field = 'total'): int
{
    $result = pg_query_params($db, $query, $params);

    if (!$result) {
        die('Errore durante il caricamento delle statistiche: ' . htmlspecialchars(pg_last_error($db)));
    }

    $row = pg_fetch_assoc($result);

    return (int)($row[$field] ?? 0);
}

function formatDashboardDate(?string $dateValue): string
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

function listingStatusLabel(string $status): string
{
    return match ($status) {
        'active' => 'Attivo',
        'sold' => 'Venduto',
        'inactive' => 'Non attivo',
        default => ucfirst($status),
    };
}

$activeListingsCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM listings
    WHERE user_id = $1
      AND status = $2
    ',
    [$userId, 'active']
);

$soldListingsCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM listings
    WHERE user_id = $1
      AND status = $2
    ',
    [$userId, 'sold']
);

$totalListingsCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM listings
    WHERE user_id = $1
    ',
    [$userId]
);

$openChatsCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM chats
    WHERE id_acquirente = $1
       OR id_venditore = $1
    ',
    [$userId]
);

$sentMessagesCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM messaggi
    WHERE user_id = $1
    ',
    [$userId]
);

$receivedMessagesCount = fetchSingleValue(
    $db,
    '
    SELECT COUNT(*) AS total
    FROM messaggi m
    INNER JOIN chats c ON c.id_chat = m.id_chat
    WHERE m.user_id <> $1
      AND (c.id_acquirente = $1 OR c.id_venditore = $1)
    ',
    [$userId]
);

$totalMessagesCount = $sentMessagesCount + $receivedMessagesCount;

$latestListingResult = pg_query_params(
    $db,
    '
    SELECT
        l.id,
        l.price,
        l.condition,
        l.status,
        l.created_at,
        c.name AS card_name,
        c.game,
        c.edition,
        c.language
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.user_id = $1
    ORDER BY l.created_at DESC
    LIMIT 1
    ',
    [$userId]
);

if (!$latestListingResult) {
    die('Errore durante il caricamento dell’ultimo annuncio: ' . htmlspecialchars(pg_last_error($db)));
}

$latestListing = pg_fetch_assoc($latestListingResult) ?: null;

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';

?>
<link rel="stylesheet" href="/assets/css/dashboard.css">

<section class="dashboard-page">
    <div class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">Area personale</p>
            <h1>Dashboard</h1>
            <p>
                Benvenuto, <strong><?= htmlspecialchars(currentUserName()) ?></strong>.
                Qui trovi una sintesi aggiornata dei tuoi annunci, messaggi e movimenti principali.
            </p>
        </div>

        <div class="dashboard-actions">
            <a href="/pages/create-listing.php" class="btn btn-warning">
                Nuovo annuncio
            </a>

            <a href="/pages/my-listings.php" class="btn btn-light">
                I miei annunci
            </a>

            <a href="/pages/marketplace.php" class="btn btn-outline-light">
                Marketplace
            </a>

            <a href="/pages/profile.php" class="btn btn-outline-light">
                Profilo
            </a>
        </div>
    </div>

    <div class="dashboard-stats-grid">
        <article class="dashboard-stat-card stat-primary">
            <span class="stat-label">Annunci attivi</span>
            <strong><?= $activeListingsCount ?></strong>
            <small>Visibili nel marketplace</small>
        </article>

        <article class="dashboard-stat-card">
            <span class="stat-label">Annunci venduti</span>
            <strong><?= $soldListingsCount ?></strong>
            <small>Conclusi positivamente</small>
        </article>

        <article class="dashboard-stat-card">
            <span class="stat-label">Totale annunci</span>
            <strong><?= $totalListingsCount ?></strong>
            <small>Pubblicati dal tuo account</small>
        </article>

        <article class="dashboard-stat-card">
            <span class="stat-label">Chat aperte</span>
            <strong><?= $openChatsCount ?></strong>
            <small>Conversazioni attive o storiche</small>
        </article>

        <article class="dashboard-stat-card">
            <span class="stat-label">Messaggi inviati</span>
            <strong><?= $sentMessagesCount ?></strong>
            <small>Scritti da te</small>
        </article>

        <article class="dashboard-stat-card">
            <span class="stat-label">Messaggi ricevuti</span>
            <strong><?= $receivedMessagesCount ?></strong>
            <small>Ricevuti nelle tue chat</small>
        </article>
    </div>

    <div class="dashboard-content-grid">
        <article class="dashboard-section-card dashboard-latest-card">
            <div class="dashboard-section-header">
                <div>
                    <p class="dashboard-eyebrow">Ultima attività</p>
                    <h2>Ultimo annuncio pubblicato</h2>
                </div>
            </div>

            <?php if ($latestListing === null): ?>
                <div class="dashboard-empty-state">
                    <h3>Nessun annuncio pubblicato</h3>
                    <p>Quando creerai il primo annuncio, verrà mostrato qui.</p>

                    <a href="/pages/create-listing.php" class="btn btn-warning">
                        Crea il primo annuncio
                    </a>
                </div>
            <?php else: ?>
                <div class="latest-listing-card">
                    <div>
                        <span class="listing-status-badge status-<?= htmlspecialchars($latestListing['status']) ?>">
                            <?= htmlspecialchars(listingStatusLabel($latestListing['status'])) ?>
                        </span>

                        <h3><?= htmlspecialchars($latestListing['card_name']) ?></h3>

                        <p class="latest-listing-meta">
                            <?= htmlspecialchars($latestListing['game']) ?> ·
                            <?= htmlspecialchars($latestListing['edition']) ?> ·
                            <?= htmlspecialchars($latestListing['language']) ?>
                        </p>
                    </div>

                    <div class="latest-listing-details">
                        <div>
                            <span>Prezzo</span>
                            <strong>€ <?= htmlspecialchars(number_format((float)$latestListing['price'], 2, ',', '.')) ?></strong>
                        </div>

                        <div>
                            <span>Condizione</span>
                            <strong><?= htmlspecialchars($latestListing['condition']) ?></strong>
                        </div>

                        <div>
                            <span>Pubblicato il</span>
                            <strong><?= htmlspecialchars(formatDashboardDate($latestListing['created_at'])) ?></strong>
                        </div>
                    </div>

                    <div class="latest-listing-actions">
                        <a
                            href="/pages/card-detail.php?id=<?= (int)$latestListing['id'] ?>"
                            class="btn btn-outline-secondary"
                        >
                            Vedi dettaglio
                        </a>

                        <a
                            href="/pages/edit-listing.php?id=<?= (int)$latestListing['id'] ?>"
                            class="btn btn-primary"
                        >
                            Modifica
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </article>

        <aside class="dashboard-section-card">
            <div class="dashboard-section-header">
                <div>
                    <p class="dashboard-eyebrow">Sintesi</p>
                    <h2>Riepilogo operativo</h2>
                </div>
            </div>

            <div class="dashboard-summary-list">
                <div class="summary-row">
                    <span>Annunci visibili nel marketplace</span>
                    <strong><?= $activeListingsCount ?></strong>
                </div>

                <div class="summary-row">
                    <span>Annunci conclusi come venduti</span>
                    <strong><?= $soldListingsCount ?></strong>
                </div>

                <div class="summary-row">
                    <span>Conversazioni collegate al tuo account</span>
                    <strong><?= $openChatsCount ?></strong>
                </div>

                <div class="summary-row">
                    <span>Messaggi totali gestiti</span>
                    <strong><?= $totalMessagesCount ?></strong>
                </div>
            </div>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>