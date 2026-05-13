<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/my-listings.php');
    exit;
}

$listingId = (int)($_POST['listingId'] ?? 0);

$cardName = trim($_POST['cardName'] ?? '');
$game = trim($_POST['game'] ?? '');
$edition = trim($_POST['edition'] ?? '');
$language = trim($_POST['language'] ?? '');

$priceRaw = trim($_POST['price'] ?? '');
$condition = trim($_POST['condition'] ?? '');
$status = trim($_POST['status'] ?? '');
$description = trim($_POST['description'] ?? '');

$priceRaw = str_replace(',', '.', $priceRaw);

$allowedConditions = ['Near Mint', 'Excellent', 'Good', 'Played'];
$allowedStatuses = ['active', 'inactive', 'sold'];
$allowedLanguages = ['Italiano', 'Inglese', 'Giapponese'];

if ($listingId <= 0) {
    die('Errore: annuncio non valido.');
}

if ($cardName === '' || $game === '' || $edition === '' || $language === '') {
    die('Errore: dati carta obbligatori.');
}

if (strlen($cardName) > 120 || strlen($game) > 120 || strlen($edition) > 120) {
    die('Errore: nome carta, gioco o edizione troppo lunghi.');
}

if (!in_array($language, $allowedLanguages, true)) {
    die('Errore: lingua non valida.');
}

if (!preg_match('/^\d+(\.\d{1,2})?$/', $priceRaw)) {
    die('Errore: prezzo non valido.');
}

$priceFloat = (float)$priceRaw;

if ($priceFloat <= 0 || $priceFloat > 99999999.99) {
    die('Errore: il prezzo deve essere compreso tra 0,01 e 99.999.999,99.');
}

if (!in_array($condition, $allowedConditions, true)) {
    die('Errore: condizione non valida.');
}

if (!in_array($status, $allowedStatuses, true)) {
    die('Errore: stato annuncio non valido.');
}

if (strlen($description) > 1000) {
    die('Errore: descrizione troppo lunga.');
}

$price = number_format($priceFloat, 2, '.', '');

$db = getDbConnection();

/*
 * Prima recuperiamo l'annuncio verificando che appartenga all'utente.
 * Questo evita modifiche non autorizzate cambiando manualmente l'id nell'URL/form.
 */
$listingCheck = pg_query_params(
    $db,
    '
    SELECT id, card_id
    FROM listings
    WHERE id = $1
      AND user_id = $2
    ',
    [$listingId, currentUserId()]
);

if (!$listingCheck || pg_num_rows($listingCheck) !== 1) {
    die('Errore: annuncio non trovato o modifica non autorizzata.');
}

$listing = pg_fetch_assoc($listingCheck);
$cardId = (int)$listing['card_id'];

pg_query($db, 'BEGIN');

$cardUpdate = pg_query_params(
    $db,
    '
    UPDATE cards
    SET
        name = $1,
        game = $2,
        edition = $3,
        language = $4
    WHERE id = $5
    ',
    [
        $cardName,
        $game,
        $edition,
        $language,
        $cardId
    ]
);

if (!$cardUpdate) {
    pg_query($db, 'ROLLBACK');
    die('Errore durante l’aggiornamento della carta: ' . htmlspecialchars(pg_last_error($db)));
}

$listingUpdate = pg_query_params(
    $db,
    '
    UPDATE listings
    SET
        price = $1,
        condition = $2,
        status = $3,
        description = $4
    WHERE id = $5
      AND user_id = $6
    RETURNING id
    ',
    [
        $price,
        $condition,
        $status,
        $description,
        $listingId,
        currentUserId()
    ]
);

if (!$listingUpdate) {
    pg_query($db, 'ROLLBACK');
    die('Errore durante l’aggiornamento dell’annuncio: ' . htmlspecialchars(pg_last_error($db)));
}

if (pg_num_rows($listingUpdate) !== 1) {
    pg_query($db, 'ROLLBACK');
    die('Errore: annuncio non trovato o modifica non autorizzata.');
}

pg_query($db, 'COMMIT');

header('Location: /pages/my-listings.php?updated=1');
exit;