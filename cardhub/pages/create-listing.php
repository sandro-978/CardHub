<?php
require_once __DIR__ . '/../includes/session.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$pageTitle = 'Nuovo annuncio';
require __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/create-listing.css">

<section class="create-listing-page">
    <div class="create-listing-hero">
        <div>
            <p class="create-listing-eyebrow">Marketplace CardHub</p>
            <h1>Pubblica un nuovo annuncio</h1>
            <p>
                Inserisci i dati della carta, definisci prezzo e condizione,
                aggiungi una descrizione e carica un’immagine per rendere
                l’annuncio più chiaro e completo.
            </p>
        </div>

        <div class="create-listing-hero-actions">
            <a href="/pages/my-listings.php" class="btn btn-light">
                I miei annunci
            </a>

            <a href="/pages/marketplace.php" class="btn btn-outline-light">
                Marketplace
            </a>
        </div>
    </div>

    <form
        name="listingForm"
        action="/api/listings.php"
        method="POST"
        enctype="multipart/form-data"
        onsubmit="return validateListingForm()"
        class="create-listing-shell"
    >
        <div class="create-listing-form-card">
            <div class="form-section-heading">
                <span>1</span>
                <div>
                    <h2>Dati della carta</h2>
                    <p>Informazioni principali usate per presentare l’annuncio.</p>
                </div>
            </div>

            <div class="create-form-grid">
                <div class="form-field field-full">
                    <label class="form-label" for="cardName">Nome carta</label>
                    <input
                        class="form-control"
                        id="cardName"
                        name="cardName"
                        type="text"
                        placeholder="Es. Drago Bianco Occhi Blu"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="game">Gioco</label>
                    <select class="form-select" id="game" name="game" required>
                        <option value="">Seleziona un gioco</option>
                        <option value="Yu-Gi-Oh!">Yu-Gi-Oh!</option>
                        <option value="Magic">Magic</option>
                        <option value="Pokemon">Pokemon</option>
                        <option value="Battle Deck">Battle Deck</option>
                        <option value="Fantasy Cards">Fantasy Cards</option>
                    </select>
                </div>

                <div class="form-field">
                    <label class="form-label" for="edition">Edizione</label>
                    <input
                        class="form-control"
                        id="edition"
                        name="edition"
                        type="text"
                        placeholder="Es. Prima edizione"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="language">Lingua</label>
                    <select class="form-select" id="language" name="language" required>
                        <option value="Italiano">Italiano</option>
                        <option value="Inglese">Inglese</option>
                        <option value="Giapponese">Giapponese</option>
                    </select>
                </div>

                <div class="form-field">
                    <label class="form-label" for="condition">Condizione</label>
                    <select class="form-select" id="condition" name="condition" required>
                        <option value="Near Mint">Near Mint</option>
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Played">Played</option>
                    </select>
                </div>
            </div>

            <div class="form-section-divider"></div>

            <div class="form-section-heading">
                <span>2</span>
                <div>
                    <h2>Dettagli annuncio</h2>
                    <p>Prezzo, descrizione e immagine visibile agli altri utenti.</p>
                </div>
            </div>

            <div class="create-form-grid">
                <div class="form-field">
                    <label class="form-label" for="price">Prezzo</label>
                    <div class="price-input-wrap">
                        <span>€</span>
                        <input
                            class="form-control"
                            id="price"
                            name="price"
                            type="number"
                            min="0.01"
                            max="99999999.99"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >
                    </div>

                    <div id="priceError" class="text-danger small mt-1 d-none">
                        Il prezzo massimo consentito è 99.999.999,99 €.
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label" for="cardImage">Immagine carta</label>
                    <input
                        class="form-control"
                        id="cardImage"
                        name="cardImage"
                        type="file"
                        accept="image/png,image/jpeg,image/gif,image/webp"
                    >

                    <div class="form-text">
                        Formati accettati: PNG, JPG, JPEG, GIF, WEBP.
                    </div>

                    <div id="imageError" class="text-danger small mt-1 d-none">
                        Il file selezionato non è un’immagine valida.
                    </div>
                </div>

                <div class="form-field field-full">
                    <label class="form-label" for="description">Descrizione</label>
                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Aggiungi dettagli utili: stato reale, difetti, edizione, eventuali note per l’acquirente..."
                    ></textarea>
                </div>
            </div>

            <div class="create-listing-submit-row">
                <a href="/pages/marketplace.php" class="btn btn-outline-secondary">
                    Annulla
                </a>

                <button class="btn btn-warning" type="submit">
                    Salva annuncio
                </button>
            </div>
        </div>

        <aside class="create-listing-preview-card">
            <div class="preview-card-header">
                <span>Anteprima</span>
                <strong>Immagine carta</strong>
            </div>

            <div id="imagePreviewBox" class="image-preview-box">
                <img
                    id="cardImagePreview"
                    src="/assets/img/placeholder-card.png"
                    alt="Anteprima immagine carta"
                    class="image-preview"
                >

                <button
                    type="button"
                    id="removeImagePreview"
                    class="btn btn-sm btn-outline-danger mt-3"
                >
                    Rimuovi immagine
                </button>
            </div>

            <div class="preview-help-box">
                <h2>Consiglio</h2>
                <p>
                    Usa un’immagine chiara, frontale e ben illuminata.
                    Un annuncio con foto leggibile risulta più affidabile.
                </p>
            </div>
        </aside>
    </form>
</section>

<script src="/assets/js/validation.js"></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>