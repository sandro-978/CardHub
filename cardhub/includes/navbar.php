<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">CardHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/pages/marketplace.php">Marketplace</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/create-listing.php">Nuovo annuncio</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/my-listings.php">I miei annunci</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/messaggi.php">Messaggi</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/dashboard.php">Dashboard</a></li>
            </ul>
            <div class="d-flex gap-2">
                <?php if (isLoggedIn()): ?>
                    <a class="btn btn-outline-light btn-sm" href="/pages/profile.php"><?= htmlspecialchars(currentUserName()) ?></a>
                    <a class="btn btn-warning btn-sm" href="/auth/logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="/auth/login.php">Login</a>
                    <a class="btn btn-primary btn-sm" href="/auth/register.php">Registrati</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
