<?php
require_once __DIR__ . '/../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/login.php');
    exit;
}

$_SESSION['user_id'] = 1;
$_SESSION['username'] = $_POST['inputEmail'] ?? 'demo';

header('Location: /pages/dashboard.php');
exit;
?>
