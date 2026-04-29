<?php
$pageTitle = 'Nuovo annuncio';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-card">
    <h1 class="h3 mb-3">Pubblica un annuncio</h1>
    <form name="listingForm" action="#" method="POST" onsubmit="return validateListingForm()" class="d-grid gap-3">
        <div>
            <label class="form-label" for="cardName">Nome carta</label>
            <input class="form-control" id="cardName" name="cardName" type="text" required>
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
            <input class="form-control" id="price" name="price" type="number" min="0.01" step="0.01" required>
            <div class="error-message">Il prezzo deve essere maggiore di zero.</div>
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
