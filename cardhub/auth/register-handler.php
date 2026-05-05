<?php
require_once __DIR__ . '/../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/register.php');
    exit;
}

$_SESSION['user_id'] = 1;
$_SESSION['username'] = $_POST['inputUsername'] ?? 'nuovo_utente';

header('Location: /pages/dashboard.php');
exit;
?>
