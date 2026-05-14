<?php
$pageTitle = 'Marketplace';

require_once __DIR__ . '/../includes/db.php';

$dbconn = getDbConnection();

const MAX_LISTING_PRICE = 99999999.99;

function getQueryParam(string $name): string
{
    return trim($_GET[$name] ?? '');
}

function fetchDistinctOptions($dbconn, string $sql): array
{
    $result = pg_query($dbconn, $sql);

    if (!$result) {
        return [];
    }

    $rows = pg_fetch_all($result);

    if ($rows === false) {
        return [];
    }

    return array_map(
        static fn($row) => $row['value'],
        $rows
    );
}

function normalizePriceFilter(string $rawValue, array &$errors, string $label): ?string
{
    if ($rawValue === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $rawValue);

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
        $errors[] = "$label non valido. Usa massimo due cifre decimali.";
        return null;
    }

    $price = (float)$normalized;

    if ($price < 0) {
        $errors[] = "$label non può essere negativo.";
        return null;
    }

    if ($price > MAX_LISTING_PRICE) {
        $errors[] = "$label troppo alto. Il massimo consentito è 99.999.999,99 €.";
        return null;
    }

    return number_format($price, 2, '.', '');
}

$q = strtolower(getQueryParam('q'));
$filtroGioco = getQueryParam('game');
$filtroEdizione = getQueryParam('edition');
$filtroLingua = getQueryParam('language');
$filtroCondizione = getQueryParam('condition');
$filtroVenditore = getQueryParam('seller');
$filtroPrezzoMinRaw = getQueryParam('price_min');
$filtroPrezzoMaxRaw = getQueryParam('price_max');
$sort = getQueryParam('sort');

$filterErrors = [];

$filtroPrezzoMin = normalizePriceFilter($filtroPrezzoMinRaw, $filterErrors, 'Prezzo minimo');
$filtroPrezzoMax = normalizePriceFilter($filtroPrezzoMaxRaw, $filterErrors, 'Prezzo massimo');

if ($filtroPrezzoMin !== null && $filtroPrezzoMax !== null) {
    if ((float)$filtroPrezzoMin > (float)$filtroPrezzoMax) {
        $filterErrors[] = 'Il prezzo minimo non può essere maggiore del prezzo massimo.';
        $filtroPrezzoMin = null;
        $filtroPrezzoMax = null;
    }
}

/*
 * Ordinamento condizione:
 * valore minore = condizione migliore.
 */
$conditionRankSql = "
    CASE LOWER(l.condition)
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
    'newest' => 'l.created_at DESC',
    'oldest' => 'l.created_at ASC',
    'price_asc' => 'l.price ASC, l.created_at DESC',
    'price_desc' => 'l.price DESC, l.created_at DESC',
    'condition_best' => $conditionRankSql . ' ASC, l.price ASC, l.created_at DESC',
    'condition_worst' => $conditionRankSql . ' DESC, l.price ASC, l.created_at DESC',
    'name_asc' => 'c.name ASC, l.created_at DESC',
    'name_desc' => 'c.name DESC, l.created_at DESC'
];

$sortLabels = [
    'newest' => 'Data: più recenti',
    'oldest' => 'Data: meno recenti',
    'price_asc' => 'Prezzo: crescente',
    'price_desc' => 'Prezzo: decrescente',
    'condition_best' => 'Condizione: migliore',
    'condition_worst' => 'Condizione: peggiore',
    'name_asc' => 'Nome: A-Z',
    'name_desc' => 'Nome: Z-A'
];

if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'newest';
}

$gameOptions = fetchDistinctOptions(
    $dbconn,
    "
    SELECT DISTINCT c.game AS value
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.status = 'active'
      AND c.game <> ''
    ORDER BY c.game ASC
    "
);

$editionOptions = fetchDistinctOptions(
    $dbconn,
    "
    SELECT DISTINCT c.edition AS value
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.status = 'active'
      AND c.edition <> ''
    ORDER BY c.edition ASC
    "
);

$languageOptions = fetchDistinctOptions(
    $dbconn,
    "
    SELECT DISTINCT c.language AS value
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.status = 'active'
      AND c.language <> ''
    ORDER BY c.language ASC
    "
);

$conditionOptions = fetchDistinctOptions(
    $dbconn,
    "
    SELECT value
    FROM (
        SELECT DISTINCT
            l.condition AS value,
            CASE LOWER(l.condition)
                WHEN 'mint' THEN 1
                WHEN 'near mint' THEN 2
                WHEN 'excellent' THEN 3
                WHEN 'good' THEN 4
                WHEN 'played' THEN 5
                WHEN 'poor' THEN 6
                ELSE 99
            END AS condition_rank
        FROM listings l
        WHERE l.status = 'active'
          AND l.condition <> ''
    ) AS condition_values
    ORDER BY condition_rank ASC, value ASC
    "
);

$sellerOptions = fetchDistinctOptions(
    $dbconn,
    "
    SELECT DISTINCT u.username AS value
    FROM listings l
    INNER JOIN users u ON u.id = l.user_id
    WHERE l.status = 'active'
      AND u.username <> ''
    ORDER BY u.username ASC
    "
);

$sql = "
    SELECT
        l.id,
        l.user_id,
        l.card_id,
        l.price,
        l.condition,
        l.description,
        l.status,
        l.created_at,
        c.name AS card_name,
        c.game,
        c.edition,
        c.language,
        c.image_url,
        u.username
    FROM listings l
    INNER JOIN cards c ON l.card_id = c.id
    INNER JOIN users u ON l.user_id = u.id
    WHERE l.status = 'active'
";

$params = [];
$paramIndex = 1;

if ($q !== '') {
    $sql .= "
        AND (
            LOWER(c.name) LIKE $" . $paramIndex . "
            OR LOWER(c.game) LIKE $" . $paramIndex . "
            OR LOWER(c.edition) LIKE $" . $paramIndex . "
            OR LOWER(c.language) LIKE $" . $paramIndex . "
            OR LOWER(l.condition) LIKE $" . $paramIndex . "
            OR LOWER(u.username) LIKE $" . $paramIndex . "
            OR LOWER(COALESCE(l.description, '')) LIKE $" . $paramIndex . "
        )
    ";

    $params[] = '%' . $q . '%';
    $paramIndex++;
}

if ($filtroGioco !== '') {
    $sql .= ' AND c.game = $' . $paramIndex;
    $params[] = $filtroGioco;
    $paramIndex++;
}

if ($filtroEdizione !== '') {
    $sql .= ' AND c.edition = $' . $paramIndex;
    $params[] = $filtroEdizione;
    $paramIndex++;
}

if ($filtroLingua !== '') {
    $sql .= ' AND c.language = $' . $paramIndex;
    $params[] = $filtroLingua;
    $paramIndex++;
}

if ($filtroCondizione !== '') {
    $sql .= ' AND l.condition = $' . $paramIndex;
    $params[] = $filtroCondizione;
    $paramIndex++;
}

if ($filtroVenditore !== '') {
    $sql .= ' AND u.username = $' . $paramIndex;
    $params[] = $filtroVenditore;
    $paramIndex++;
}

if ($filtroPrezzoMin !== null) {
    $sql .= ' AND l.price >= $' . $paramIndex;
    $params[] = $filtroPrezzoMin;
    $paramIndex++;
}

if ($filtroPrezzoMax !== null) {
    $sql .= ' AND l.price <= $' . $paramIndex;
    $params[] = $filtroPrezzoMax;
    $paramIndex++;
}

$sql .= ' ORDER BY ' . $allowedSorts[$sort];

$result = count($params) > 0
    ? pg_query_params($dbconn, $sql, $params)
    : pg_query($dbconn, $sql);

if (!$result) {
    die('Errore durante il caricamento degli annunci.');
}

$listings = pg_fetch_all($result) ?: [];

$hasFilterParams =
    $q !== '' ||
    $filtroGioco !== '' ||
    $filtroEdizione !== '' ||
    $filtroLingua !== '' ||
    $filtroCondizione !== '' ||
    $filtroVenditore !== '' ||
    $filtroPrezzoMinRaw !== '' ||
    $filtroPrezzoMaxRaw !== '';

$hasCustomSort = $sort !== 'newest';
$hasActiveControls = $hasFilterParams || $hasCustomSort;

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h1 class="h2 mb-1 marketplace-title">Marketplace</h1>
        <p class="text-muted mb-0">
            Annunci disponibili per carte collezionabili.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button id="filters" class="btn btn-warning" type="button">
            ☰ Filtri avanzati
        </button>

        <button id="sortToggle" class="btn btn-outline-dark" type="button">
            ↕ Ordina
        </button>
    </div>
</div>

<?php foreach ($filterErrors as $error): ?>
    <div class="alert alert-warning">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endforeach; ?>

<form id="filterPanel" class="filter-panel" method="GET" action="/pages/marketplace.php">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

    <div class="filter-grid">
        <div>
            <label class="form-label" for="q">Ricerca libera</label>
            <input
                class="form-control"
                type="text"
                id="q"
                name="q"
                placeholder="Nome, gioco, edizione, lingua, venditore..."
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
            >
        </div>

        <div>
            <label class="form-label" for="game">Gioco</label>
            <select class="form-select" id="game" name="game">
                <option value="">Tutti i giochi</option>
                <?php foreach ($gameOptions as $game): ?>
                    <option value="<?= htmlspecialchars($game) ?>" <?= $filtroGioco === $game ? 'selected' : '' ?>>
                        <?= htmlspecialchars($game) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="edition">Edizione</label>
            <select class="form-select" id="edition" name="edition">
                <option value="">Tutte le edizioni</option>
                <?php foreach ($editionOptions as $edition): ?>
                    <option value="<?= htmlspecialchars($edition) ?>" <?= $filtroEdizione === $edition ? 'selected' : '' ?>>
                        <?= htmlspecialchars($edition) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="language">Lingua</label>
            <select class="form-select" id="language" name="language">
                <option value="">Tutte le lingue</option>
                <?php foreach ($languageOptions as $language): ?>
                    <option value="<?= htmlspecialchars($language) ?>" <?= $filtroLingua === $language ? 'selected' : '' ?>>
                        <?= htmlspecialchars($language) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="condition">Condizione</label>
            <select class="form-select" id="condition" name="condition">
                <option value="">Tutte le condizioni</option>
                <?php foreach ($conditionOptions as $condition): ?>
                    <option value="<?= htmlspecialchars($condition) ?>" <?= $filtroCondizione === $condition ? 'selected' : '' ?>>
                        <?= htmlspecialchars($condition) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="seller">Venditore</label>
            <select class="form-select" id="seller" name="seller">
                <option value="">Tutti i venditori</option>
                <?php foreach ($sellerOptions as $seller): ?>
                    <option value="<?= htmlspecialchars($seller) ?>" <?= $filtroVenditore === $seller ? 'selected' : '' ?>>
                        <?= htmlspecialchars($seller) ?>
                    </option>
                <?php endforeach; ?>
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

<form id="sortPanel" class="filter-panel sort-panel" method="GET" action="/pages/marketplace.php">
    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <input type="hidden" name="game" value="<?= htmlspecialchars($filtroGioco) ?>">
    <input type="hidden" name="edition" value="<?= htmlspecialchars($filtroEdizione) ?>">
    <input type="hidden" name="language" value="<?= htmlspecialchars($filtroLingua) ?>">
    <input type="hidden" name="condition" value="<?= htmlspecialchars($filtroCondizione) ?>">
    <input type="hidden" name="seller" value="<?= htmlspecialchars($filtroVenditore) ?>">
    <input type="hidden" name="price_min" value="<?= htmlspecialchars($filtroPrezzoMinRaw) ?>">
    <input type="hidden" name="price_max" value="<?= htmlspecialchars($filtroPrezzoMaxRaw) ?>">

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
        <button type="submit" class="btn btn-dark">
            Applica ordinamento
        </button>

        <a
            href="/pages/marketplace.php?<?= http_build_query([
                'q' => $_GET['q'] ?? '',
                'game' => $filtroGioco,
                'edition' => $filtroEdizione,
                'language' => $filtroLingua,
                'condition' => $filtroCondizione,
                'seller' => $filtroVenditore,
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

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <p class="text-muted mb-0">
            Risultati trovati: <strong><?= count($listings) ?></strong>
        </p>

        <p class="text-muted small mb-0">
            Ordinamento corrente:
            <strong><?= htmlspecialchars($sortLabels[$sort]) ?></strong>
        </p>
    </div>

    <?php if ($hasActiveControls): ?>
        <a href="/pages/marketplace.php" class="btn btn-sm btn-outline-secondary">
            Reset completo
        </a>
    <?php endif; ?>
</div>

<?php if (count($listings) === 0): ?>
    <div class="alert alert-light">
        Nessun annuncio disponibile con i filtri selezionati.
    </div>
<?php else: ?>
    <div class="listing-grid">
        <?php foreach ($listings as $listing): ?>
            <?php $imageUrl = $listing['image_url'] ?: '/assets/img/placeholder-card.png'; ?>

            <article
                class="listing-card"
                data-condition="<?= htmlspecialchars($listing['condition']) ?>"
                data-game="<?= htmlspecialchars($listing['game']) ?>"
                data-language="<?= htmlspecialchars($listing['language']) ?>"
            >
                <img
                    src="<?= htmlspecialchars($imageUrl) ?>"
                    alt="<?= htmlspecialchars($listing['card_name']) ?>"
                >

                <div class="listing-card-body">
                    <h2 class="h5">
                        <?= htmlspecialchars($listing['card_name']) ?>
                    </h2>

                    <p class="listing-meta mb-2">
                        <?= htmlspecialchars($listing['game']) ?> ·
                        <?= htmlspecialchars($listing['edition']) ?> ·
                        <?= htmlspecialchars($listing['language']) ?> ·
                        <?= htmlspecialchars($listing['condition']) ?>
                    </p>

                    <p class="small text-muted mb-2">
                        Venditore:
                        <strong><?= htmlspecialchars($listing['username']) ?></strong>
                    </p>

                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <strong>
                            € <?= number_format((float)$listing['price'], 2, ',', '.') ?>
                        </strong>

                        <a
                            class="btn btn-sm btn-outline-primary"
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

<script src="/assets/js/filters.js"></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>