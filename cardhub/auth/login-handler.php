<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ .'/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/login.php');
    exit;
}

$email = $_POST['inputEmail'];
if($email === ''){
    header('Location: /auth/login.php');
    exit;
}

$dbconn = getDbConnection();
$ris = pg_query_params($dbconn,'
    SELECT id,username,email
    FROM users
    WHERE email = $1
',[$email]);

$user = pg_fetch_assoc($ris);
if(!$user){
    header('Location: /auth/login.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

header('Location: /pages/dashboard.php');
exit;
?>
