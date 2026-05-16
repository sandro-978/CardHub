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

function formatListingDate(?string $dateValue): string
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

$activeCount = 0;
$soldCount = 0;
$inactiveCount = 0;

foreach ($listings as $listing) {
    if ($listing['status'] === 'active') {
        $activeCount++;
    } elseif ($listing['status'] === 'sold') {
        $soldCount++;
    } else {
        $inactiveCount++;
    }
}

$pageTitle = 'I miei annunci';
require __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/my-listings.css">

<section class="my-listings-page">
    <div class="my-listings-hero">
        <div>
            <p class="my-listings-eyebrow">Area venditore</p>
            <h1>I miei annunci</h1>
            <p>
                Gestisci gli annunci pubblicati dal tuo account, modifica i dettagli,
                controlla lo stato delle inserzioni o rimuovi gli annunci non più necessari.
            </p>
        </div>

        <div class="my-listings-hero-actions">
            <a href="/pages/create-listing.php" class="btn btn-warning">
                Nuovo annuncio
            </a>

            <a href="/pages/marketplace.php" class="btn btn-outline-light">
                Marketplace
            </a>
        </div>
    </div>

    <div class="my-listings-stats">
        <article>
            <span>Totale annunci</span>
            <strong><?= count($listings) ?></strong>
        </article>

        <article>
            <span>Attivi</span>
            <strong><?= $activeCount ?></strong>
        </article>

        <article>
            <span>Venduti</span>
            <strong><?= $soldCount ?></strong>
        </article>

        <article>
            <span>Non attivi</span>
            <strong><?= $inactiveCount ?></strong>
        </article>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="my-listings-alert success">
            Annuncio aggiornato correttamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="my-listings-alert success">
            Annuncio eliminato correttamente.
        </div>
    <?php endif; ?>

    <?php if (count($listings) === 0): ?>
        <div class="my-listings-empty">
            <h2>Nessun annuncio pubblicato</h2>
            <p>
                Non hai ancora creato annunci. Pubblica la tua prima carta
                per renderla visibile nel marketplace.
            </p>

            <a href="/pages/create-listing.php" class="btn btn-warning">
                Crea il primo annuncio
            </a>
        </div>
    <?php else: ?>
        <div class="my-listings-grid">
            <?php foreach ($listings as $listing): ?>
                <?php
                $imageUrl = $listing['image_url'] ?: '/assets/img/placeholder-card.png';
                $status = $listing['status'];
                $description = trim($listing['description'] ?? '');

                if ($description === '') {
                    $description = 'Nessuna descrizione inserita.';
                }

                if (strlen($description) > 150) {
                    $description = substr($description, 0, 150) . '...';
                }
                ?>

                <article class="my-listing-card">
                    <div class="my-listing-image-wrap">
                        <img
                            src="<?= htmlspecialchars($imageUrl) ?>"
                            alt="<?= htmlspecialchars($listing['card_name']) ?>"
                        >

                        <span class="my-listing-status status-<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars(listingStatusLabel($status)) ?>
                        </span>
                    </div>

                    <div class="my-listing-body">
                        <div class="my-listing-main">
                            <h2><?= htmlspecialchars($listing['card_name']) ?></h2>

                            <p class="my-listing-meta">
                                <?= htmlspecialchars($listing['game']) ?> ·
                                <?= htmlspecialchars($listing['edition']) ?> ·
                                <?= htmlspecialchars($listing['language']) ?>
                            </p>
                        </div>

                        <div class="my-listing-price-row">
                            <div>
                                <span>Prezzo</span>
                                <strong>
                                    € <?= htmlspecialchars(number_format((float)$listing['price'], 2, ',', '.')) ?>
                                </strong>
                            </div>

                            <div>
                                <span>Condizione</span>
                                <strong><?= htmlspecialchars($listing['condition']) ?></strong>
                            </div>
                        </div>

                        <p class="my-listing-description">
                            <?= htmlspecialchars($description) ?>
                        </p>

                        <div class="my-listing-actions">
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

                    <footer class="my-listing-footer">
                        Pubblicato il <?= htmlspecialchars(formatListingDate($listing['created_at'])) ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>