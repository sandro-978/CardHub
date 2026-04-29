<?php
$pageTitle = 'Marketplace';
require __DIR__ . '/../includes/header.php';
$listings = json_decode(file_get_contents(__DIR__ . '/../data/sample-listings.json'), true);
$query = strtolower(trim($_GET['q'] ?? ''));
if ($query !== '') {
    $listings = array_filter($listings, function ($listing) use ($query) {
        return str_contains(strtolower($listing['card_name']), $query)
            || str_contains(strtolower($listing['edition']), $query)
            || str_contains(strtolower($listing['language']), $query);
    });
}
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h1 class="h2 mb-1">Marketplace</h1>
        <p class="text-muted mb-0">Annunci disponibili per carte collezionabili.</p>
    </div>
    <select id="conditionFilter" class="form-select w-auto">
        <option value="">Tutte le condizioni</option>
        <option value="Near Mint">Near Mint</option>
        <option value="Excellent">Excellent</option>
        <option value="Good">Good</option>
    </select>
</div>

<div class="listing-grid">
    <?php foreach ($listings as $listing): ?>
        <article class="listing-card" data-condition="<?= htmlspecialchars($listing['condition']) ?>">
            <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['card_name']) ?>">
            <div class="listing-card-body">
                <h2 class="h5"><?= htmlspecialchars($listing['card_name']) ?></h2>
                <p class="listing-meta mb-2">
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
<script src="/assets/js/filters.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
