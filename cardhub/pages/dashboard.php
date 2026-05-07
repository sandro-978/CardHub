<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Dashboard</h1>
<div class="alert alert-light">
    Benvenuto nella tua dashboard, <?= htmlspecialchars(currentUserName()) ?>! Qui puoi gestire i tuoi annunci, visualizzare le tue carte collezionabili e accedere alle funzionalità del tuo account.
</div>

<div class="feature-grid">
    <article class="feature-card" href="/pages/my-listings.php"><h2 class="h5">Annunci attivi</h2><p class="display-6">3</p></article>
    <article class="feature-card"><h2 class="h5">Carte gestite</h2><p class="display-6">3</p></article>
    <article class="feature-card"><h2 class="h5">Utente</h2><p><?= htmlspecialchars(currentUserName()) ?></p></article>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
