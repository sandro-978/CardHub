<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserId(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function currentUserName(): string
{
    return $_SESSION['username'] ?? 'Ospite';
}

function currentUserEmail(): string
{
    return $_SESSION['email'] ?? '';
}
?>