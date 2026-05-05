<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserName(): string
{
    return $_SESSION['username'] ?? 'Ospite';
}
?>
