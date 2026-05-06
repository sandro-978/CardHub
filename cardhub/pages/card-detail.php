<?php
$pageTitle = 'Dettaglio carta';
require __DIR__ . '/../includes/header.php';
require_once __DIR__ .'/../includes/db.php';

$dbconn = getDbConnection();
$id_annuncio = $_GET['id'] ?? null;
if (!$id_annuncio) {
    echo '<main class="container>
    <div class="detail-card"><h1>Annuncio non trovato</div>
    </main>';
    require_once __DIR__ .'/../includes/footer.php';
    exit;
}

$ris = pg_query_params($dbconn,'
    SELECT listings.id AS id_annuncio,listings.user_id AS id_venditore,listings.price,listings.condition,listings.description,listings.status,listings.created_at,cards.name AS nome_carta,cards.game,cards.edition,cards.language,cards.image_url,users.username AS nome_venditore
    FROM listings JOIN cards ON listings.card_id = cards.id JOIN users ON listings.user_id = users.id
    WHERE listings.id = $1
',[$id_annuncio]);

$annuncio = pg_fetch_assoc($ris);
if (!$annuncio) {
    echo '<main class="container"><div class="detail-card"><h1>Annuncio non trovato</h1></div></main>';
    require_once __DIR__ .'/../includes/footer.php';
    exit;
}

$imageUrl = $annuncio['image_url'] ?: '/assets/img/placeholder-card.png';
?>

<main class= "container">
    <section class="card-detail-layout">
        <div class="card-detail-image-box">
            <img src="<?= htmlspecialchars($imageUrl)?>"
                 alt="<?= htmlspecialchars($annuncio['nome_carta'])?>"
                 class = "card-detail-image">
        </div>
        <div class = "card-detail-info">

            <h1 class="card-detail-title">
                <?= htmlspecialchars($annuncio['nome_carta'])?>
            </h1>
            <p class="card-detatil-meta">
            <?= htmlspecialchars($annuncio['game'])?> ·
            <?= htmlspecialchars($annuncio['edition'])?> ·
            <?= htmlspecialchars($annuncio['language'])?> ·
            <?= htmlspecialchars($annuncio['condition'])?> 
            </p>

            <p class="card-detatil-price">
                € <?= number_format((float)$annuncio['price'],2,'.','') ?> 
            </p>

            <p class="card-detail-description">
                <?= htmlspecialchars($annuncio['description'])?>
            </p>

            <div class="card-detail-seller">
                <span>Venditore:</span>
                <strong><?= htmlspecialchars($annuncio['nome_venditore'])?></strong>
            </div>

        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $annuncio['id_venditore']):?>
            <form action="/api/crea_chat.php" method="POST">
                <input type="hidden" name="id_annuncio" value="<?= htmlspecialchars($annuncio['id_annuncio'])?>">
                <button type="submit" class="btn-contact-seller">
                    ✉️ Contatta il venditore 
                </button>
            </form>
        <?php elseif(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $annuncio['id_venditore']):?>
            <p class="seller-note">Questo annuncio è tuo</p>
        <?php else:?>
            <a href="/auth/login.php" class= "btn-contact-seller">
                Accedi per contattare il venditore
            </a>
        <?php endif; ?>

        <a href="/pages/marketplace.php" class="btn-back-marketplace">
            ↩️ Torna al marketplace 
        </a>
    </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
