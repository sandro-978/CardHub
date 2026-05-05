<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Dashboard</h1>
<div class="feature-grid">
    <article class="feature-card"><h2 class="h5">Annunci attivi</h2><p class="display-6">3</p></article>
    <article class="feature-card"><h2 class="h5">Carte gestite</h2><p class="display-6">3</p></article>
    <article class="feature-card"><h2 class="h5">Utente</h2><p><?= htmlspecialchars(currentUserName()) ?></p></article>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
