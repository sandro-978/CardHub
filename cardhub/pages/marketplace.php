<?php
$pageTitle = 'Marketplace';
require __DIR__ . '/../includes/header.php';
require_once __DIR__ .'/../includes/db.php';

$dbconn = getDbConnection();
$query = strtolower(trim($_GET['q'] ?? ''));

$filtroGioco = trim($_GET['game'] ??'');
$filtroLingua = trim($_GET['language'] ??'');
$filtroCondizione =trim($_GET['condition'] ??'');
$filtroPrezzoMin = trim($_GET['price_min'] ??'');
$filtroPrezzoMax =trim($_GET['price_max'] ??'');

$sql = "
        SELECT listings.id,listings.price,listings.condition,listings.description,listings.status,listings.created_at,cards.name AS card_name,cards.game,cards.edition,cards.language,cards.image_url,users.username
        FROM listings JOIN cards ON listings.card_id = cards.id JOIN users ON listings.user_id = users.id
        WHERE listings.status = 'active'
        ";

$params = [];
$paramIndex = 1;

if($query !== ''){
    $sql .= "
        AND (LOWER(cards.name) LIKE $" . $paramIndex . "
            OR LOWER(cards.edition) LIKE $" . $paramIndex . "
            OR LOWER(cards.language) LIKE $" . $paramIndex . "
            OR LOWER(cards.game) LIKE $" . $paramIndex . "
        )
    ";
    $params[] = '%'.$query.'%';
    $paramIndex++;
}

if($filtroGioco !== ''){
    $sql .= ' AND cards.game = $' . $paramIndex;
    $params[] = $filtroGioco;
    $paramIndex++;
}
if($filtroLingua !== ''){
    $sql .= ' AND cards.language = $' . $paramIndex;
    $params[] = $filtroLingua;
    $paramIndex++;
}
if($filtroCondizione !== ''){
    $sql .= ' AND listings.condition = $' . $paramIndex;
    $params[] = $filtroCondizione;
    $paramIndex++;
}
if($filtroPrezzoMin !== ''){
    $sql .= ' AND listings.price >= $' . $paramIndex;
    $params[] = $filtroPrezzoMin;
    $paramIndex++;
}
if($filtroPrezzoMax !== ''){
    $sql .= ' AND listings.price <= $' . $paramIndex;
    $params[] = $filtroPrezzoMax;
    $paramIndex++;
}

$sql .= " ORDER BY listings.created_at DESC";

if(count($params) > 0){
    $result = pg_query_params($dbconn, $sql, $params);
}
else{
    $result = pg_query($dbconn, $sql);
}
if(!$result){
    die("errore caricamento annunci");
}

$listings = pg_fetch_all($result);
if($listings === false){
    $listings = [];
}
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h1 class="h2 mb-1 marketplace-title">Marketplace</h1>
        <p class="text-muted mb-0">Annunci disponibili per carte collezionabili.</p>
    </div>
    
    <button id="filters" class="btn btn-warning" type="button">
        ☰ Filtri
    </button>

</div>

<form id="filterPanel" class="filter-panel" method="GET" action="/pages/marketplace.php">
    <div class="filter-grid">

        <div>
            <label class="form-label" for="q">Cerca</label>
            <input class="form-control" type="text" id="q" name ="q" value="<?= htmlspecialchars($_GET['q']??'')?>">
        </div>

        <div>
            <label class="form-label" for="game">Gioco</label>
            <select class="form-select" id="game" name="game">

                <option value="">Tutti i Giochi</option>
                <option value="Yu-Gi-Oh!" <?= ($_GET['game']??'') === "Yu-Gi-Oh!" ? 'selected':'' ?>>Yu-Gi-Oh!</option>
                <option value="Magic" <?= ($_GET['game']??'') === "Magic" ? 'selected':'' ?>>Magic</option>
                <option value="Pokemon" <?= ($_GET['game']??'') === "Pokemon" ? 'selected':'' ?>>Pokemon</option>
                <option value="Battle Deck" <?= ($_GET['game']??'') === "Battle Deck" ? 'selected':'' ?>>Battle Deck</option>
                <option value="Fantasy Cards" <?= ($_GET['game']??'') === "Fantasy Cards" ? 'selected':'' ?>>Fantasy Cards</option>

            </select>
        </div>
        <div>
            <label class="form-label" for="language">Lingua</label>
            <select class="form-select" id="language" name="language">

                <option value="">Tutte le lingue</option>
                <option value="Italiano" <?= ($_GET['language']??'') === "Italiano"?'selected':'' ?>>Italiano</option>
                <option value="Inglese" <?= ($_GET['language']??'') === "Inglese"?'selected':'' ?>>Inglese</option>
                <option value="Giapponese" <?= ($_GET['language']??'') === "Giapponese"?'selected':'' ?>>Giapponese</option>

            </select>
        </div>
        <div>
            <label class="form-label" for="condition">Condizione</label>
            <select class="form-select" id="condition" name="condition">

                <option value="">Tutte le condizioni</option>
                <option value="Near Mint" <?= ($_GET['condition']??'') === "Near Mint"?'selected':'' ?>>Near Mint</option>
                <option value="Excellent" <?= ($_GET['condition']??'') === "Excellent"?'selected':'' ?>>Excellent</option>
                <option value="Good" <?= ($_GET['condition']??'') === "Good"?'selected':'' ?>>Good</option>
                <option value="Played" <?= ($_GET['condition']??'') === "Played"?'selected':'' ?>>Played</option>

            </select>
        </div>
        <div>
            <label class="form-label" for="price_min">Prezzo minimo</label>
            <input class ="form-control" type="number" step="0.01" min = "0" id="price_min" name="price_min"
                value="<?= htmlspecialchars(($_GET['price_min']??''))?>">
        </div>
        <div>
            <label class="form-label" for="price_max">Prezzo massimo</label>
            <input class ="form-control" type="number" step="0.01" min = "0" id="price_max" name="price_max"
                value="<?= htmlspecialchars(($_GET['price_max']??''))?>">
        </div>
    </div>

    <div class="filter-actions">
        <button type="submit" class="btn btn-warning">Applica i filtri</button>
        <a href="/pages/marketplace.php" class="btn btn-outline-light">Reset</a>
    </div>

</form>
<?php if(count($listings) === 0):?>
    <div class="alert alert-light">Nessun annuncio disponibile</div>

<?php else:?>
    <div class="listing-grid">
        <?php foreach ($listings as $listing): ?>
            <?php $imageUrl = $listing["image_url"] ?: '/assets/img/placeholder.png';?>
            <article class="listing-card" data-condition="<?= htmlspecialchars($listing['condition']) ?>">
                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($listing['card_name']) ?>">
                <div class="listing-card-body">
                    <h2 class="h5"><?= htmlspecialchars($listing['card_name']) ?></h2>
                    <p class="listing-meta mb-2">
                        <?= htmlspecialchars($listing['game'])?> ·
                        <?= htmlspecialchars($listing['edition']) ?> ·
                        <?= htmlspecialchars($listing['language']) ?> ·
                        <?= htmlspecialchars($listing['condition']) ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>€ <?= number_format($listing['price'], 2, ',', '.') ?></strong>
                        <a class="btn btn-sm btn-outline-primary" href="/pages/card-detail.php?id=<?= (int)$listing['id'] ?>">Dettagli</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif;?>
<script src="/assets/js/filters.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
