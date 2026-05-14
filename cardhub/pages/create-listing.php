<?php

require_once __DIR__ . '/../includes/session.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$pageTitle = 'Nuovo annuncio';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-card">
    <h1 class="h3 mb-3">Pubblica un annuncio</h1>
    <form name="listingForm" action="../api/listings.php" method="POST" enctype="multipart/form-data" onsubmit="return validateListingForm()" class="d-grid gap-3">
        <div>
            <label class="form-label" for="cardName">Nome carta</label>
            <input class="form-control" id="cardName" name="cardName" type="text" required>
        </div>
        <div>
            <label class="form-label" for="game">Gioco</label>
            <select class="form-select" id="game" name="game"required>

                <option value="">Seleziona un gioco</option>
                <option value="Yu-Gi-Oh!">Yu-Gi-Oh!</option>
                <option value="Magic">Magic</option>
                <option value="Pokemon">Pokemon</option>
                <option value="Battle Deck">Battle Deck</option>
                <option value="Fantasy Cards">Fantasy Cards</option>

            </select>
        </div>
        <div>
            <label class="form-label" for="edition">Edizione</label>
            <input class="form-control" id="edition" name="edition" type="text" required>
        </div>
        <div>
            <label class="form-label" for="language">Lingua</label>
            <select class="form-select" id="language" name="language" required>
                <option value="Italiano">Italiano</option>
                <option value="Inglese">Inglese</option>
                <option value="Giapponese">Giapponese</option>
            </select>
        </div>
        <div>
            <label class="form-label" for="cardImage">Immagine carta</label>
            <input class="form-control" id="cardImage" name="cardImage" type="file" accept="image/*">
        </div>
        <div>
            <label class="form-label" for="condition">Condizione</label>
            <select class="form-select" id="condition" name="condition" required>
                <option value="Near Mint">Near Mint</option>
                <option value="Excellent">Excellent</option>
                <option value="Good">Good</option>
                <option value="Played">Played</option>
            </select>
        </div>
        <div>
            <label class="form-label" for="price">Prezzo</label>
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

            <div id="priceError" class="text-danger small mt-1 d-none">
                Il prezzo massimo consentito è 99.999.999,99 €.
            </div>

        </div>
        <div>
            <label class="form-label" for="description">Descrizione</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Salva annuncio</button>
    </form>
</div>
<script src="/assets/js/validation.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
