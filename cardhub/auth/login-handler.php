<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/login.php');
    exit;
}

$email = trim($_POST['inputEmail'] ?? '');
$password = $_POST['inputPassword'] ?? '';

if ($email === '' || $password === '') {
    die('Errore: email e password sono obbligatorie.');
}

$db = getDbConnection();

$query = '
    SELECT id, username, email, password_hash
    FROM users
    WHERE email = $1
    LIMIT 1
';

$result = pg_query_params($db, $query, [$email]);

if (!$result || pg_num_rows($result) !== 1) {
    die('Errore: credenziali non valide.');
}

$user = pg_fetch_assoc($result);

if (!password_verify($password, $user['password_hash'])) {
    die('Errore: credenziali non valide.');
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

header('Location: /pages/dashboard.php');
exit;
?>