<?php
$pageTitle = 'Dettaglio carta';
require __DIR__ . '/../includes/header.php';
$listings = json_decode(file_get_contents(__DIR__ . '/../data/sample-listings.json'), true);
$id = (int)($_GET['id'] ?? 1);
$listing = null;
foreach ($listings as $item) {
    if ((int)$item['id'] === $id) {
        $listing = $item;
        break;
    }
}
?>
<?php if (!$listing): ?>
    <div class="alert alert-warning">Annuncio non trovato.</div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-md-5">
            <img class="img-fluid rounded border bg-light" src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['card_name']) ?>">
        </div>
        <div class="col-md-7">
            <div class="cardhub-panel">
                <h1><?= htmlspecialchars($listing['card_name']) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($listing['game']) ?></p>
                <dl class="row">
                    <dt class="col-sm-4">Edizione</dt><dd class="col-sm-8"><?= htmlspecialchars($listing['edition']) ?></dd>
                    <dt class="col-sm-4">Lingua</dt><dd class="col-sm-8"><?= htmlspecialchars($listing['language']) ?></dd>
                    <dt class="col-sm-4">Condizione</dt><dd class="col-sm-8"><?= htmlspecialchars($listing['condition']) ?></dd>
                    <dt class="col-sm-4">Prezzo</dt><dd class="col-sm-8">€ <?= number_format($listing['price'], 2, ',', '.') ?></dd>
                </dl>
                <button class="btn btn-primary">Contatta venditore</button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
