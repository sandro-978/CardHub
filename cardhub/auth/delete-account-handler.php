<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/delete-account.php');
    exit;
}

$token = $_POST['deleteAccountToken'] ?? '';
$sessionToken = $_SESSION['delete_account_token'] ?? '';

if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    die('Errore: richiesta non valida.');
}

$password = $_POST['inputPassword'] ?? '';

if ($password === '') {
    die('Errore: password obbligatoria.');
}

$userId = currentUserId();
$db = getDbConnection();

$userResult = pg_query_params(
    $db,
    'SELECT id, password_hash FROM users WHERE id = $1',
    [$userId]
);

if (!$userResult || pg_num_rows($userResult) !== 1) {
    die('Errore: utente non trovato.');
}

$user = pg_fetch_assoc($userResult);

if (!password_verify($password, $user['password_hash'])) {
    die('Errore: password non corretta.');
}

pg_query($db, 'BEGIN');

$deleteResult = pg_query_params(
    $db,
    'DELETE FROM users WHERE id = $1',
    [$userId]
);

if (!$deleteResult) {
    pg_query($db, 'ROLLBACK');
    die('Errore durante l’eliminazione account: ' . htmlspecialchars(pg_last_error($db)));
}

pg_query($db, 'COMMIT');

unset($_SESSION['delete_account_token']);

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: /index.php?accountDeleted=1');
exit;
?>