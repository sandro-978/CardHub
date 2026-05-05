<?php
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero p-4 p-md-5 rounded-3 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-md-7">
            <h1 class="display-5 fw-bold">Compra, vendi e gestisci carte collezionabili</h1>
            <p class="lead">CardHub è un marketplace in cui ogni utente può pubblicare annunci associati a carte, indicando prezzo, condizione, lingua ed edizione.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="/pages/marketplace.php" class="btn btn-primary btn-lg">Esplora marketplace</a>
                <a href="/pages/create-listing.php" class="btn btn-outline-dark btn-lg">Pubblica annuncio</a>
            </div>
        </div>
        <div class="col-md-5">
            <div class="search-panel shadow-sm">
                <h2 class="h5">Ricerca rapida</h2>
                <form action="/pages/marketplace.php" method="GET" class="d-grid gap-3">
                    <input class="form-control" type="search" name="q" placeholder="Nome carta, edizione, lingua">
                    <button class="btn btn-dark" type="submit">Cerca</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="h4 mb-3">Funzioni previste</h2>
    <div class="feature-grid">
        <article class="feature-card"><h3>Marketplace</h3><p>Lista annunci con prezzo, condizione, lingua ed edizione.</p></article>
        <article class="feature-card"><h3>Gestione utenti</h3><p>Registrazione, login, profilo e sessione utente.</p></article>
        <article class="feature-card"><h3>Gestione annunci</h3><p>Creazione, modifica, pubblicazione e rimozione degli annunci.</p></article>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
