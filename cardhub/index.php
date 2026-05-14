<?php
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="home-hero">
    <div class="home-hero-content">
        <div class="home-badge"> La tua community di carte collezionabili </div>
        <h1> Compra, vendi e gestisci <span>carte collezionabili</span></h1>
        <p> CardHub è un marketplace in cui ogni utente può pubblicare annunci di carte</p>
            
        <div class="home_actions">
            <a href="/pages/marketplace.php" class="home-btn-primary"> Esplora marketplace</a>
            <a href="/pages/create-listing.php" class="home-btn-secondary"> Pubblica annuncio</a>
        </div>

        <div class="home-search-box">
            <h2>Ricerca rapida</h2>
            <form action="/pages/marketplace.php" method="GET" class="home-search-form">
                <input type="search" name="q" placeholder="Nome carta, edizione, lingua">
                <button type="submit">Cerca</button>
            </form>
        </div>
    </div>
    <div class="home-hero-visual">
        <div class="hero-card hero-card-back hero-card-left">
            <img src="/assets/img/uploads/DragoCremesiArtwork.png">
        </div>
        <div class="hero-card hero-card-main">
            <img src="/assets/img/uploads/CavaliereAnticoArtwork.png">
        </div>
        <div class="hero-card hero-card-back hero-card-right">
            <img src="/assets/img/uploads/MagoDelleRuneArtwork.png">
        </div>
    </div>
</section>

<section class="home-section-title"><h2>Funzioni attive</h2> </section>
<section class="home-feature-grid">
    <article class="home-feature-card">
        <h3>Marketplace</h3>
        <p>Esplora annunci</p>
        <a href="/pages/marketplace.php"> Vai al marketplace →</a>
    </article>

    <article class="home-feature-card">
        <h3>Gestione utenti</h3>
        <p>Login,registrazione,profilo utente</p>
        <a href="/auth/login.php"> Accedi →</a>
    </article>

    <article class="home-feature-card">
        <h3>Gestione annunci</h3>
        <p>Crea, visualizza o modifica i tuoi annunci</p>
        <a href="/pages/my-listing.php">I miei annunci →</a>
    </article>

    <article class="home-feature-card">
        <h3>Messaggi</h3>
        <p>Contatta i venditori</p>
        <a href="/pages/messaggi.php">Apri messaggu →</a>
    </article>
</section>
<div class="cardhub-panel">
        <h2 class="h3 mb-3">Funzioni da implementare</h2>

        <ul class="list-group">
            <li class="list-group-item">✔️Filtri avanzati nel marketplace✔️ (pulsante filtra)</li>
            <li class="list-group-item">✔️Ordinamento annunci per prezzo, data e condizione✔️ (pulsante ordinamento)</li>
            <li class="list-group-item">Sistema di annunci preferiti</li>
            <li class="list-group-item">Chat completa tra acquirente e venditore</li>
            <li class="list-group-item">Notifiche per nuovi messaggi</li>
            <li class="list-group-item">Dashboard con statistiche reali</li>
            <li class="list-group-item">Storico attività dell’utente</li>
            <li class="list-group-item">Impostazioni privacy del profilo</li>
            <li class="list-group-item">Preferenze lingua dell’interfaccia</li>
            <li class="list-group-item">Anteprima immagine durante la creazione annuncio</li>
            <li class="list-group-item">Gestione avanzata dello stato degli annunci</li>
            <li class="list-group-item">Miglioramento della pagina dettaglio annuncio</li>
            <li class="list-group-item">Miglioramento responsive per dispositivi mobili</li>
        </ul>
    </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
