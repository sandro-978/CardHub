<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$_SESSION['delete_listing_token'] = bin2hex(random_bytes(32));

$db = getDbConnection();

$result = pg_query_params(
    $db,
    '
    SELECT
        l.id,
        l.price,
        l.condition,
        l.description,
        l.status,
        l.created_at,
        c.name AS card_name,
        c.game,
        c.edition,
        c.language,
        c.image_url
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.user_id = $1
    ORDER BY l.created_at DESC
    ',
    [currentUserId()]
);

if (!$result) {
    die('Errore durante il caricamento degli annunci: ' . htmlspecialchars(pg_last_error($db)));
}

$listings = pg_fetch_all($result) ?: [];

$pageTitle = 'I miei annunci';
require __DIR__ . '/../includes/header.php';
?>

<div class="cardhub-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">I miei annunci</h1>
            <p class="text-muted mb-0">Annunci pubblicati dal tuo account.</p>
        </div>

        <a href="/pages/create-listing.php" class="btn btn-primary">
            Nuovo annuncio
        </a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            Annuncio aggiornato correttamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            Annuncio eliminato correttamente.
        </div>
    <?php endif; ?>

    <?php if (count($listings) === 0): ?>
        <div class="alert alert-info">
            Non hai ancora pubblicato annunci.
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($listings as $listing): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <img
                            src="<?= htmlspecialchars($listing['image_url'] ?: '/assets/img/placeholder-card.png') ?>"
                            class="card-img-top"
                            alt="<?= htmlspecialchars($listing['card_name']) ?>"
                            style="height: 220px; object-fit: cover;"
                        >

                        <div class="card-body d-flex flex-column">
                            <h2 class="h5">
                                <?= htmlspecialchars($listing['card_name']) ?>
                            </h2>

                            <p class="text-muted small">
                                <?= htmlspecialchars($listing['game']) ?> ·
                                <?= htmlspecialchars($listing['edition']) ?> ·
                                <?= htmlspecialchars($listing['language']) ?>
                            </p>

                            <p class="mb-1">
                                <strong>Prezzo:</strong>
                                € <?= htmlspecialchars(number_format((float)$listing['price'], 2, ',', '.')) ?>
                            </p>

                            <p class="mb-1">
                                <strong>Condizione:</strong>
                                <?= htmlspecialchars($listing['condition']) ?>
                            </p>

                            <p class="mb-1">
                                <strong>Stato:</strong>
                                <?= htmlspecialchars($listing['status']) ?>
                            </p>

                            <p class="small text-muted flex-grow-1">
                                <?= htmlspecialchars($listing['description'] ?: 'Nessuna descrizione inserita.') ?>
                            </p>

                            <div class="d-flex gap-2 mt-3">
                                <a
                                    href="/pages/card-detail.php?id=<?= urlencode($listing['id']) ?>"
                                    class="btn btn-outline-secondary btn-sm"
                                >
                                    Dettaglio
                                </a>

                                <a
                                    href="/pages/edit-listing.php?id=<?= urlencode($listing['id']) ?>"
                                    class="btn btn-primary btn-sm"
                                >
                                    Modifica
                                </a>

                                <form
                                    action="/pages/delete-listing-handler.php"
                                    method="POST"
                                    onsubmit="return confirm('Vuoi eliminare definitivamente questo annuncio?');"
                                    class="d-inline"
                                >
                                    <input
                                        type="hidden"
                                        name="listingId"
                                        value="<?= htmlspecialchars($listing['id']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="deleteListingToken"
                                        value="<?= htmlspecialchars($_SESSION['delete_listing_token']) ?>"
                                    >

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Elimina
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-footer text-muted small">
                            Pubblicato il <?= htmlspecialchars($listing['created_at']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>