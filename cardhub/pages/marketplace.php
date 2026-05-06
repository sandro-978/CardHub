<?php
$pageTitle = 'Marketplace';
require __DIR__ . '/../includes/header.php';
require_once __DIR__ .'/../includes/db.php';

$dbconn = getDbConnection();
$query = strtolower(trim($_GET['q'] ?? ''));
$sql = "
        SELECT listings.id,listings.price,listings.condition,listings.description,listings.status,listings.created_at,cards.name AS card_name,cards.game,cards.edition,cards.language,cards.image_url,users.username
        FROM listings JOIN cards ON listings.card_id = cards.id JOIN users ON listings.user_id = users.id
        WHERE listings.status = 'active'
        ";

$params = [];
if($query !== ''){
    $sql .= '
        AND (LOWER(card_name) LIKE $1 OR LOWER (card.edition) LIKE $1 OR LOWER (card.language) LIKE $1 OR LOWER (card.game) LIKE $1
    ';
    $params[] = '%'.$query.'%';
}
$sql .= "ORDER BY listings.created_at DESC";

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
    <select id="conditionFilter" class="form-select w-auto">
        <option value="">Tutte le condizioni</option>
        <option value="Near Mint">Near Mint</option>
        <option value="Excellent">Excellent</option>
        <option value="Good">Good</option>
        <option value="Played">Played</option>
    </select>
</div>
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
