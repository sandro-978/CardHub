<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/register.php');
    exit;
}

$username = trim($_POST['inputUsername'] ?? '');
$email = trim($_POST['inputEmail'] ?? '');
$password = $_POST['inputPassword'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    die('Errore: tutti i campi sono obbligatori.');
}

if (strlen($username) < 3) {
    die('Errore: lo username deve avere almeno 3 caratteri.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Errore: email non valida.');
}

if (strlen($password) < 8) {
    die('Errore: la password deve avere almeno 8 caratteri.');
}

$db = getDbConnection();

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$query = '
    INSERT INTO users (username, email, password_hash)
    VALUES ($1, $2, $3)
    RETURNING id, username, email
';

$result = pg_query_params($db, $query, [
    $username,
    $email,
    $passwordHash
]);

if (!$result) {
    $error = pg_last_error($db);

    if (str_contains($error, 'duplicate key')) {
        die('Errore: username o email già registrati.');
    }

    die('Errore durante la registrazione: ' . htmlspecialchars($error));
}

$user = pg_fetch_assoc($result);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

header('Location: /pages/dashboard.php');
exit;
?>