<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$listingId = (int)($_GET['id'] ?? 0);

if ($listingId <= 0) {
    die('Errore: annuncio non valido.');
}

$db = getDbConnection();

$listingResult = pg_query_params(
    $db,
    '
    SELECT
        l.id,
        l.user_id,
        l.card_id,
        l.price,
        l.condition,
        l.description,
        l.status,
        c.name AS card_name,
        c.game,
        c.edition,
        c.language
    FROM listings l
    INNER JOIN cards c ON c.id = l.card_id
    WHERE l.id = $1
      AND l.user_id = $2
    ',
    [$listingId, currentUserId()]
);

if (!$listingResult || pg_num_rows($listingResult) !== 1) {
    die('Errore: annuncio non trovato o non autorizzato.');
}

$listing = pg_fetch_assoc($listingResult);

$pageTitle = 'Modifica annuncio';
require __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h1 class="h3 mb-3">Modifica annuncio</h1>

    <form action="/pages/update-listing-handler.php" method="POST" class="d-grid gap-3">
        <input type="hidden" name="listingId" value="<?= htmlspecialchars($listing['id']) ?>">

        <div>
            <label class="form-label" for="cardName">Nome carta</label>
            <input
                class="form-control"
                id="cardName"
                name="cardName"
                type="text"
                maxlength="120"
                value="<?= htmlspecialchars($listing['card_name']) ?>"
                required
            >
        </div>

        <div>
            <label class="form-label" for="game">Gioco</label>
            <input
                class="form-control"
                id="game"
                name="game"
                type="text"
                maxlength="120"
                value="<?= htmlspecialchars($listing['game']) ?>"
                required
            >
        </div>

        <div>
            <label class="form-label" for="edition">Edizione</label>
            <input
                class="form-control"
                id="edition"
                name="edition"
                type="text"
                maxlength="120"
                value="<?= htmlspecialchars($listing['edition']) ?>"
                required
            >
        </div>

        <div>
            <label class="form-label" for="language">Lingua</label>
            <select class="form-select" id="language" name="language" required>
                <?php
                $languages = ['Italiano', 'Inglese', 'Giapponese'];

                foreach ($languages as $language):
                ?>
                    <option
                        value="<?= htmlspecialchars($language) ?>"
                        <?= $listing['language'] === $language ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($language) ?>
                    </option>
                <?php endforeach; ?>
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
                value="<?= htmlspecialchars($listing['price']) ?>"
                required
            >
        </div>

        <div>
            <label class="form-label" for="condition">Condizione</label>
            <select class="form-select" id="condition" name="condition" required>
                <?php
                $conditions = ['Near Mint', 'Excellent', 'Good', 'Played'];

                foreach ($conditions as $condition):
                ?>
                    <option
                        value="<?= htmlspecialchars($condition) ?>"
                        <?= $listing['condition'] === $condition ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($condition) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="status">Stato annuncio</label>
            <select class="form-select" id="status" name="status" required>
                <?php
                $statuses = [
                    'active' => 'Attivo',
                    'inactive' => 'Non attivo',
                    'sold' => 'Venduto'
                ];

                foreach ($statuses as $value => $label):
                ?>
                    <option
                        value="<?= htmlspecialchars($value) ?>"
                        <?= $listing['status'] === $value ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="description">Descrizione</label>
            <textarea
                class="form-control"
                id="description"
                name="description"
                rows="4"
                maxlength="1000"
            ><?= htmlspecialchars($listing['description'] ?? '') ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                Salva modifiche
            </button>

            <a class="btn btn-outline-secondary" href="/pages/my-listings.php">
                Annulla
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>