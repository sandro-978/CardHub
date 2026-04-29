<?php
$pageTitle = 'Profilo';
require __DIR__ . '/../includes/header.php';
?>
<div class="cardhub-panel">
    <h1 class="h3">Profilo utente</h1>
    <p>Utente corrente: <strong><?= htmlspecialchars(currentUserName()) ?></strong></p>
    <p class="text-muted">Pagina predisposta per dati personali, email e statistiche annunci.</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
