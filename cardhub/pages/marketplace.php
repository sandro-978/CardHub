<?php
$pageTitle = 'Marketplace';

require_once __DIR__ . '/../includes/db.php';

$dbconn = getDbConnection();

const MAX_LISTING_PRICE = 99999999.99;

function getMarketplaceParam(string $name): string
{
    return trim($_GET[$name] ?? '');
}

function normalizeMarketplacePrice(string $rawValue): ?string
{
    if ($rawValue === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $rawValue);

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
        return null;
    }

    $price = (float)$normalized;

    if ($price < 0 || $price > MAX_LISTING_PRICE) {
        return null;
    }

    return number_format($price, 2, '.', '');
}

$query = strtolower(getMarketplaceParam('q'));
$filtroGioco = getMarketplaceParam('game');
$filtroLingua = getMarketplaceParam('language');
$filtroCondizione = getMarketplaceParam('condition');
$filtroPrezzoMinRaw = getMarketplaceParam('price_min');
$filtroPrezzoMaxRaw = getMarketplaceParam('price_max');
$sort = getMarketplaceParam('sort');

$filtroPrezzoMin = normalizeMarketplacePrice($filtroPrezzoMinRaw);
$filtroPrezzoMax = normalizeMarketplacePrice($filtroPrezzoMaxRaw);

if ($filtroPrezzoMin !== null && $filtroPrezzoMax !== null && (float)$filtroPrezzoMin > (float)$filtroPrezzoMax) {
    $filtroPrezzoMin = null;
    $filtroPrezzoMax = null;
}

$conditionRankSql = "
    CASE LOWER(listings.condition)
        WHEN 'mint' THEN 1
        WHEN 'near mint' THEN 2
        WHEN 'excellent' THEN 3
        WHEN 'good' THEN 4
        WHEN 'played' THEN 5
        WHEN 'poor' THEN 6
        ELSE 99
    END
";

$allowedSorts = [
    'newest' => 'listings.created_at DESC',
    'oldest' => 'listings.created_at ASC',
    'price_asc' => 'listings.price ASC, listings.created_at DESC',
    'price_desc' => 'listings.price DESC, listings.created_at DESC',
    'condition_best' => $conditionRankSql . ' ASC, listings.price ASC',
    'condition_worst' => $conditionRankSql . ' DESC, listings.price ASC',
    'name_asc' => 'cards.name ASC, listings.created_at DESC',
    'name_desc' => 'cards.name DESC, listings.created_at DESC'
];

$sortLabels = [
    'newest' => 'Più recenti',
    'oldest' => 'Meno recenti',
    'price_asc' => 'Prezzo crescente',
    'price_desc' => 'Prezzo decrescente',
    'condition_best' => 'Condizione migliore',
    'condition_worst' => 'Condizione peggiore',
    'name_asc' => 'Nome A-Z',
    'name_desc' => 'Nome Z-A'
];

if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'newest';
}

$sql = "
    SELECT
        listings.id,
        listings.price,
        listings.condition,
        listings.description,
        listings.status,
        listings.created_at,
        cards.name AS card_name,
        cards.game,
        cards.edition,
        cards.language,
        cards.image_url,
        users.username
    FROM listings
    INNER JOIN cards ON listings.card_id = cards.id
    INNER JOIN users ON listings.user_id = users.id
    WHERE listings.status = 'active'
";

$params = [];
$paramIndex = 1;

if ($query !== '') {
    $sql .= "
        AND (
            LOWER(cards.name) LIKE $" . $paramIndex . "
            OR LOWER(cards.edition) LIKE $" . $paramIndex . "
            OR LOWER(cards.language) LIKE $" . $paramIndex . "
            OR LOWER(cards.game) LIKE $" . $paramIndex . "
            OR LOWER(users.username) LIKE $" . $paramIndex . "
            OR LOWER(COALESCE(listings.description, '')) LIKE $" . $paramIndex . "
        )
    ";

    $params[] = '%' . $query . '%';
    $paramIndex++;
}

if ($filtroGioco !== '') {
    $sql .= ' AND cards.game = $' . $paramIndex;
    $params[] = $filtroGioco;
    $paramIndex++;
}

if ($filtroLingua !== '') {
    $sql .= ' AND cards.language = $' . $paramIndex;
    $params[] = $filtroLingua;
    $paramIndex++;
}

if ($filtroCondizione !== '') {
    $sql .= ' AND listings.condition = $' . $paramIndex;
    $params[] = $filtroCondizione;
    $paramIndex++;
}

if ($filtroPrezzoMin !== null) {
    $sql .= ' AND listings.price >= $' . $paramIndex;
    $params[] = $filtroPrezzoMin;
    $paramIndex++;
}

if ($filtroPrezzoMax !== null) {
    $sql .= ' AND listings.price <= $' . $paramIndex;
    $params[] = $filtroPrezzoMax;
    $paramIndex++;
}

$sql .= ' ORDER BY ' . $allowedSorts[$sort];

$result = count($params) > 0
    ? pg_query_params($dbconn, $sql, $params)
    : pg_query($dbconn, $sql);

if (!$result) {
    die('Errore caricamento annunci: ' . htmlspecialchars(pg_last_error($dbconn)));
}

$listings = pg_fetch_all($result) ?: [];

$hasActiveControls =
    $query !== '' ||
    $filtroGioco !== '' ||
    $filtroLingua !== '' ||
    $filtroCondizione !== '' ||
    $filtroPrezzoMinRaw !== '' ||
    $filtroPrezzoMaxRaw !== '' ||
    $sort !== 'newest';

require __DIR__ . '/../includes/header.php';
?>

<section class="marketplace-page">
    <div class="marketplace-hero">
        <div>
            <p class="marketplace-eyebrow">CardHub marketplace</p>
            <h1>Marketplace</h1>
            <p>
                Esplora annunci di carte collezionabili, filtra per gioco,
                lingua, condizione e prezzo, oppure ordina i risultati in base
                alle tue preferenze.
            </p>
        </div>

        <div class="marketplace-hero-actions">
            <button id="filters" class="btn btn-warning" type="button">
                Filtri avanzati
            </button>

            <button id="sortToggle" class="btn btn-outline-light" type="button">
                Ordina
            </button>

            <a href="/pages/create-listing.php" class="btn btn-light">
                Nuovo annuncio
            </a>
        </div>
    </div>

    <div class="marketplace-toolbar">
        <div>
            <span>Risultati trovati</span>
            <strong><?= count($listings) ?></strong>
        </div>

        <div>
            <span>Ordinamento</span>
            <strong><?= htmlspecialchars($sortLabels[$sort]) ?></strong>
        </div>

        <?php if ($hasActiveControls): ?>
            <a href="/pages/marketplace.php" class="btn btn-sm btn-outline-secondary">
                Reset completo
            </a>
        <?php endif; ?>
    </div>

    <form id="filterPanel" class="filter-panel marketplace-panel" method="GET" action="/pages/marketplace.php">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

        <div class="marketplace-panel-header">
            <div>
                <span>Ricerca</span>
                <h2>Filtri avanzati</h2>
            </div>
        </div>

        <div class="filter-grid">
            <div>
                <label class="form-label" for="q">Cerca</label>
                <input
                    class="form-control"
                    type="text"
                    id="q"
                    name="q"
                    placeholder="Nome carta, edizione, venditore..."
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                >
            </div>

            <div>
                <label class="form-label" for="game">Gioco</label>
                <select class="form-select" id="game" name="game">
                    <option value="">Tutti i giochi</option>
                    <option value="Yu-Gi-Oh!" <?= $filtroGioco === "Yu-Gi-Oh!" ? 'selected' : '' ?>>Yu-Gi-Oh!</option>
                    <option value="Magic" <?= $filtroGioco === "Magic" ? 'selected' : '' ?>>Magic</option>
                    <option value="Pokemon" <?= $filtroGioco === "Pokemon" ? 'selected' : '' ?>>Pokemon</option>
                    <option value="Battle Deck" <?= $filtroGioco === "Battle Deck" ? 'selected' : '' ?>>Battle Deck</option>
                    <option value="Fantasy Cards" <?= $filtroGioco === "Fantasy Cards" ? 'selected' : '' ?>>Fantasy Cards</option>
                </select>
            </div>

            <div>
                <label class="form-label" for="language">Lingua</label>
                <select class="form-select" id="language" name="language">
                    <option value="">Tutte le lingue</option>
                    <option value="Italiano" <?= $filtroLingua === "Italiano" ? 'selected' : '' ?>>Italiano</option>
                    <option value="Inglese" <?= $filtroLingua === "Inglese" ? 'selected' : '' ?>>Inglese</option>
                    <option value="Giapponese" <?= $filtroLingua === "Giapponese" ? 'selected' : '' ?>>Giapponese</option>
                </select>
            </div>

            <div>
                <label class="form-label" for="condition">Condizione</label>
                <select class="form-select" id="condition" name="condition">
                    <option value="">Tutte le condizioni</option>
                    <option value="Near Mint" <?= $filtroCondizione === "Near Mint" ? 'selected' : '' ?>>Near Mint</option>
                    <option value="Excellent" <?= $filtroCondizione === "Excellent" ? 'selected' : '' ?>>Excellent</option>
                    <option value="Good" <?= $filtroCondizione === "Good" ? 'selected' : '' ?>>Good</option>
                    <option value="Played" <?= $filtroCondizione === "Played" ? 'selected' : '' ?>>Played</option>
                </select>
            </div>

            <div>
                <label class="form-label" for="price_min">Prezzo minimo</label>
                <input
                    class="form-control"
                    type="number"
                    step="0.01"
                    min="0"
                    max="99999999.99"
                    id="price_min"
                    name="price_min"
                    value="<?= htmlspecialchars($filtroPrezzoMinRaw) ?>"
                >
            </div>

            <div>
                <label class="form-label" for="price_max">Prezzo massimo</label>
                <input
                    class="form-control"
                    type="number"
                    step="0.01"
                    min="0"
                    max="99999999.99"
                    id="price_max"
                    name="price_max"
                    value="<?= htmlspecialchars($filtroPrezzoMaxRaw) ?>"
                >
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-warning">
                Applica filtri
            </button>

            <a href="/pages/marketplace.php?sort=<?= urlencode($sort) ?>" class="btn btn-outline-light">
                Reset filtri
            </a>
        </div>
    </form>

    <form id="sortPanel" class="filter-panel marketplace-panel sort-panel" method="GET" action="/pages/marketplace.php">
        <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <input type="hidden" name="game" value="<?= htmlspecialchars($filtroGioco) ?>">
        <input type="hidden" name="language" value="<?= htmlspecialchars($filtroLingua) ?>">
        <input type="hidden" name="condition" value="<?= htmlspecialchars($filtroCondizione) ?>">
        <input type="hidden" name="price_min" value="<?= htmlspecialchars($filtroPrezzoMinRaw) ?>">
        <input type="hidden" name="price_max" value="<?= htmlspecialchars($filtroPrezzoMaxRaw) ?>">

        <div class="marketplace-panel-header">
            <div>
                <span>Ordinamento</span>
                <h2>Scegli come ordinare gli annunci</h2>
            </div>
        </div>

        <div class="sort-grid">
            <?php foreach ($sortLabels as $sortValue => $sortLabel): ?>
                <label class="sort-option <?= $sort === $sortValue ? 'active' : '' ?>">
                    <input
                        type="radio"
                        name="sort"
                        value="<?= htmlspecialchars($sortValue) ?>"
                        <?= $sort === $sortValue ? 'checked' : '' ?>
                    >
                    <span><?= htmlspecialchars($sortLabel) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-warning">
                Applica ordinamento
            </button>

            <a
                href="/pages/marketplace.php?<?= http_build_query([
                    'q' => $_GET['q'] ?? '',
                    'game' => $filtroGioco,
                    'language' => $filtroLingua,
                    'condition' => $filtroCondizione,
                    'price_min' => $filtroPrezzoMinRaw,
                    'price_max' => $filtroPrezzoMaxRaw,
                    'sort' => 'newest'
                ]) ?>"
                class="btn btn-outline-light"
            >
                Reset ordinamento
            </a>
        </div>
    </form>

    <?php if (count($listings) === 0): ?>
        <div class="marketplace-empty">
            <h2>Nessun annuncio disponibile</h2>
            <p>Non sono presenti annunci compatibili con i filtri selezionati.</p>
            <a href="/pages/marketplace.php" class="btn btn-warning">
                Rimuovi filtri
            </a>
        </div>
    <?php else: ?>
        <div class="listing-grid">
            <?php foreach ($listings as $listing): ?>
                <?php
                $imageUrl = $listing["image_url"] ?: '/assets/img/placeholder-card.png';
                $description = trim($listing['description'] ?? '');

                if ($description === '') {
                    $description = 'Nessuna descrizione inserita.';
                }

                if (strlen($description) > 120) {
                    $description = substr($description, 0, 120) . '...';
                }
                ?>

                <article class="listing-card" data-condition="<?= htmlspecialchars($listing['condition']) ?>">
                    <div class="listing-image-wrap">
                        <img
                            src="<?= htmlspecialchars($imageUrl) ?>"
                            alt="<?= htmlspecialchars($listing['card_name']) ?>"
                        >

                        <span class="listing-condition-badge">
                            <?= htmlspecialchars($listing['condition']) ?>
                        </span>
                    </div>

                    <div class="listing-card-body">
                        <div>
                            <h2><?= htmlspecialchars($listing['card_name']) ?></h2>

                            <p class="listing-meta">
                                <?= htmlspecialchars($listing['game']) ?> ·
                                <?= htmlspecialchars($listing['edition']) ?> ·
                                <?= htmlspecialchars($listing['language']) ?>
                            </p>
                        </div>

                        <p class="listing-description">
                            <?= htmlspecialchars($description) ?>
                        </p>

                        <div class="listing-seller-row">
                            <span>Venditore</span>
                            <strong><?= htmlspecialchars($listing['username']) ?></strong>
                        </div>

                        <div class="listing-card-footer">
                            <strong>
                                € <?= number_format((float)$listing['price'], 2, ',', '.') ?>
                            </strong>

                            <a
                                class="btn btn-sm btn-primary"
                                href="/pages/card-detail.php?id=<?= (int)$listing['id'] ?>"
                            >
                                Dettagli
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script src="/assets/js/filters.js"></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>