<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/edit-profile.php');
    exit;
}

$username = trim($_POST['inputUsername'] ?? '');
$email = trim($_POST['inputEmail'] ?? '');

if ($username === '' || $email === '') {
    die('Errore: username ed email sono obbligatori.');
}

if (strlen($username) < 3 || strlen($username) > 60) {
    die('Errore: lo username deve contenere tra 3 e 60 caratteri.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Errore: email non valida.');
}

$db = getDbConnection();

$query = '
    UPDATE users
    SET username = $1,
        email = $2
    WHERE id = $3
    RETURNING id, username, email
';

$result = pg_query_params($db, $query, [
    $username,
    $email,
    currentUserId()
]);

if (!$result) {
    $error = pg_last_error($db);

    if (str_contains($error, 'duplicate key')) {
        die('Errore: username o email già utilizzati da un altro account.');
    }

    die('Errore durante l’aggiornamento del profilo: ' . htmlspecialchars($error));
}

if (pg_num_rows($result) !== 1) {
    die('Errore: aggiornamento profilo non riuscito.');
}

$user = pg_fetch_assoc($result);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

header('Location: /pages/profile.php?updated=1');
exit;
?>