<?php
$unreadMessagesCount = 0;

if (isLoggedIn()) {
    require_once __DIR__ . '/db.php';

    $navbarDb = getDbConnection();

    $unreadResult = pg_query_params(
        $navbarDb,
        '
        SELECT COUNT(*) AS total
        FROM messaggi m
        INNER JOIN chats c ON c.id_chat = m.id_chat
        WHERE m.user_id <> $1
          AND m.letto = FALSE
          AND (c.id_acquirente = $1 OR c.id_venditore = $1)
        ',
        [currentUserId()]
    );

    if ($unreadResult) {
        $unreadRow = pg_fetch_assoc($unreadResult);
        $unreadMessagesCount = (int)($unreadRow['total'] ?? 0);
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <link rel="stylesheet" href="/assets/css/navbar.css">

    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">CardHub</a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Apri menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/pages/marketplace.php">
                        Marketplace
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/pages/create-listing.php">
                        Nuovo annuncio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/pages/my-listings.php">
                        I miei annunci
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/pages/messaggi.php">
                        <span>Messaggi</span>

                        <?php if ($unreadMessagesCount > 0): ?>
                            <span class="badge rounded-pill bg-warning text-dark">
                                <?= $unreadMessagesCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/pages/dashboard.php">
                        Dashboard
                    </a>
                </li>
            </ul>

            <div class="d-flex gap-2">
                <?php if (isLoggedIn()): ?>
                    <a class="btn btn-outline-light btn-sm" href="/pages/profile.php">
                        <?= htmlspecialchars(currentUserName()) ?>
                    </a>

                    <a class="btn btn-warning btn-sm" href="/auth/logout.php">
                        Logout
                    </a>
                <?php else: ?>
                    <a class="btn btn-warning btn-sm" href="/auth/login.php">
                        Login
                    </a>

                    <a class="btn btn-primary btn-sm" href="/auth/register.php">
                        Registrati
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>