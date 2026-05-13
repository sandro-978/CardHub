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

$token = $_POST['deleteListingToken'] ?? '';
$sessionToken = $_SESSION['delete_listing_token'] ?? '';

if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    die('Errore: richiesta non valida.');
}

$listingId = (int)($_POST['listingId'] ?? 0);

if ($listingId <= 0) {
    die('Errore: annuncio non valido.');
}

$db = getDbConnection();

/*
 * La condizione user_id = currentUserId() è essenziale:
 * impedisce a un utente di eliminare annunci di altri modificando l'id nel form.
 */
$result = pg_query_params(
    $db,
    '
    DELETE FROM listings
    WHERE id = $1
      AND user_id = $2
    RETURNING id
    ',
    [
        $listingId,
        currentUserId()
    ]
);

if (!$result) {
    die('Errore durante l’eliminazione dell’annuncio: ' . htmlspecialchars(pg_last_error($db)));
}

if (pg_num_rows($result) !== 1) {
    die('Errore: annuncio non trovato o eliminazione non autorizzata.');
}

header('Location: /pages/my-listings.php?deleted=1');
exit;