<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Dettaglio annuncio';

$dbconn = getDbConnection();

$id_annuncio = (int)($_GET['id'] ?? 0);

if ($id_annuncio <= 0) {
    require __DIR__ . '/../includes/header.php';
    ?>
    <link rel="stylesheet" href="/assets/css/card-detail.css">

    <section class="listing-detail-page">
        <div class="listing-detail-empty">
            <h1>Annuncio non valido</h1>
            <p>L’annuncio richiesto non esiste o il collegamento non è corretto.</p>
            <a href="/pages/marketplace.php" class="btn btn-primary">
                Torna al marketplace
            </a>
        </div>
    </section>

    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$result = pg_query_params(
    $dbconn,
    '
    SELECT
        listings.id AS id_annuncio,
        listings.user_id AS id_venditore,
        listings.price,
        listings.condition,
        listings.description,
        listings.status,
        listings.created_at,
        cards.name AS nome_carta,
        cards.game,
        cards.edition,
        cards.language,
        cards.image_url,
        users.username AS nome_venditore
    FROM listings
    INNER JOIN cards ON listings.card_id = cards.id
    INNER JOIN users ON listings.user_id = users.id
    WHERE listings.id = $1
    LIMIT 1
    ',
    [$id_annuncio]
);

if (!$result) {
    require __DIR__ . '/../includes/header.php';
    ?>
    <link rel="stylesheet" href="/assets/css/card-detail.css">

    <section class="listing-detail-page">
        <div class="listing-detail-empty">
            <h1>Errore di caricamento</h1>
            <p>Non è stato possibile caricare il dettaglio dell’annuncio.</p>
            <a href="/pages/marketplace.php" class="btn btn-primary">
                Torna al marketplace
            </a>
        </div>
    </section>

    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$annuncio = pg_fetch_assoc($result);

if (!$annuncio) {
    require __DIR__ . '/../includes/header.php';
    ?>
    <link rel="stylesheet" href="/assets/css/card-detail.css">

    <section class="listing-detail-page">
        <div class="listing-detail-empty">
            <h1>Annuncio non trovato</h1>
            <p>L’annuncio potrebbe essere stato eliminato o non essere più disponibile.</p>
            <a href="/pages/marketplace.php" class="btn btn-primary">
                Torna al marketplace
            </a>
        </div>
    </section>

    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$imageUrl = $annuncio['image_url'] ?: '/assets/img/placeholder-card.png';
$isOwner = isLoggedIn() && currentUserId() === (int)$annuncio['id_venditore'];

$statusLabels = [
    'active' => 'Attivo',
    'inactive' => 'Non attivo',
    'sold' => 'Venduto'
];

$statusLabel = $statusLabels[$annuncio['status']] ?? ucfirst($annuncio['status']);

require __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/card-detail.css">

<section class="listing-detail-page">
    <div class="listing-detail-shell">
        <div class="listing-detail-image-panel">
            <img
                src="<?= htmlspecialchars($imageUrl) ?>"
                alt="<?= htmlspecialchars($annuncio['nome_carta']) ?>"
                class="listing-detail-image"
            >
        </div>

        <div class="listing-detail-info-panel">
            <div class="listing-detail-header">
                <span class="listing-detail-status status-<?= htmlspecialchars($annuncio['status']) ?>">
                    <?= htmlspecialchars($statusLabel) ?>
                </span>

                <h1><?= htmlspecialchars($annuncio['nome_carta']) ?></h1>

                <p class="listing-detail-subtitle">
                    <?= htmlspecialchars($annuncio['game']) ?> ·
                    <?= htmlspecialchars($annuncio['edition']) ?> ·
                    <?= htmlspecialchars($annuncio['language']) ?>
                </p>
            </div>

            <div class="listing-detail-price-box">
                <span>Prezzo richiesto</span>
                <strong>
                    € <?= htmlspecialchars(number_format((float)$annuncio['price'], 2, ',', '.')) ?>
                </strong>
            </div>

            <div class="listing-detail-spec-grid">
                <div>
                    <span>Gioco</span>
                    <strong><?= htmlspecialchars($annuncio['game']) ?></strong>
                </div>

                <div>
                    <span>Edizione</span>
                    <strong><?= htmlspecialchars($annuncio['edition']) ?></strong>
                </div>

                <div>
                    <span>Lingua</span>
                    <strong><?= htmlspecialchars($annuncio['language']) ?></strong>
                </div>

                <div>
                    <span>Condizione</span>
                    <strong><?= htmlspecialchars($annuncio['condition']) ?></strong>
                </div>
            </div>

            <div class="listing-detail-description">
                <h2>Descrizione</h2>

                <?php if (trim($annuncio['description'] ?? '') === ''): ?>
                    <p class="text-muted">
                        Il venditore non ha inserito una descrizione per questo annuncio.
                    </p>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($annuncio['description'])) ?></p>
                <?php endif; ?>
            </div>

            <div class="listing-detail-seller-card">
                <div>
                    <span>Venditore</span>
                    <strong><?= htmlspecialchars($annuncio['nome_venditore']) ?></strong>
                </div>

                <small>
                    Pubblicato il <?= htmlspecialchars($annuncio['created_at']) ?>
                </small>
            </div>

            <div class="listing-detail-actions">
                <?php if (isLoggedIn() && !$isOwner && $annuncio['status'] === 'active'): ?>
                    <form action="/api/crea_chat.php" method="POST">
                        <input
                            type="hidden"
                            name="id_annuncio"
                            value="<?= htmlspecialchars($annuncio['id_annuncio']) ?>"
                        >

                        <button type="submit" class="btn btn-warning">
                            Contatta il venditore
                        </button>
                    </form>
                <?php elseif ($isOwner): ?>
                    <a
                        href="/pages/edit-listing.php?id=<?= (int)$annuncio['id_annuncio'] ?>"
                        class="btn btn-primary"
                    >
                        Modifica il tuo annuncio
                    </a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="/auth/login.php" class="btn btn-warning">
                        Accedi per contattare il venditore
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        Annuncio non contattabile
                    </button>
                <?php endif; ?>

                <a href="/pages/marketplace.php" class="btn btn-outline-light">
                    Torna al marketplace
                </a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>