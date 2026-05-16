<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$pageTitle = 'Messaggi';

$user_id = currentUserId();
$dbconn = getDbConnection();

function formatMessageDate(?string $dateValue): string
{
    if (!$dateValue) {
        return '';
    }

    $date = date_create($dateValue);

    if (!$date) {
        return $dateValue;
    }

    return date_format($date, 'd/m/Y H:i');
}

$query = pg_query_params(
    $dbconn,
    "
    SELECT
        chats.id_chat,
        chats.id_annuncio,
        chats.id_acquirente,
        chats.id_venditore,
        chats.created_at,
        cards.name AS nome_carta,
        listings.price AS prezzo,
        altro_utente.username AS nome_altro_utente,

        (
            SELECT messaggi.testo
            FROM messaggi
            WHERE messaggi.id_chat = chats.id_chat
            ORDER BY messaggi.created_at DESC, messaggi.id_messaggio DESC
            LIMIT 1
        ) AS ultimo_messaggio,

        (
            SELECT messaggi.created_at
            FROM messaggi
            WHERE messaggi.id_chat = chats.id_chat
            ORDER BY messaggi.created_at DESC, messaggi.id_messaggio DESC
            LIMIT 1
        ) AS data_ultimo_messaggio,

        (
            SELECT COUNT(*)
            FROM messaggi
            WHERE messaggi.id_chat = chats.id_chat
              AND messaggi.user_id <> $1
              AND messaggi.letto = FALSE
        ) AS messaggi_non_letti

    FROM chats
    INNER JOIN listings ON chats.id_annuncio = listings.id
    INNER JOIN cards ON listings.card_id = cards.id
    INNER JOIN users AS altro_utente
        ON altro_utente.id = CASE
            WHEN chats.id_acquirente = $1 THEN chats.id_venditore
            ELSE chats.id_acquirente
        END

    WHERE chats.id_acquirente = $1
       OR chats.id_venditore = $1

    ORDER BY
        messaggi_non_letti DESC,
        data_ultimo_messaggio DESC NULLS LAST,
        chats.created_at DESC
    ",
    [$user_id]
);

if (!$query) {
    die('Errore caricamento chat: ' . htmlspecialchars(pg_last_error($dbconn)));
}

$chats = pg_fetch_all($query);

if ($chats === false) {
    $chats = [];
}

$totalUnread = 0;

foreach ($chats as $chat) {
    $totalUnread += (int)$chat['messaggi_non_letti'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="chat-title mb-1">Messaggi</h1>

            <p class="text-muted mb-0">
                Gestisci le conversazioni con acquirenti e venditori.
            </p>
        </div>

        <?php if ($totalUnread > 0): ?>
            <span class="badge rounded-pill bg-warning text-dark fs-6">
                <?= $totalUnread ?> nuovi messaggi
            </span>
        <?php endif; ?>
    </div>

    <?php if (count($chats) === 0): ?>
        <div class="empty-chat-box">
            <p class="mb-0">Non hai ancora nessuna chat disponibile.</p>
        </div>
    <?php else: ?>
        <section class="lista-chat">
            <?php foreach ($chats as $chat): ?>
                <?php
                $unreadCount = (int)$chat['messaggi_non_letti'];
                $hasUnread = $unreadCount > 0;
                ?>

                <article class="chat-preview <?= $hasUnread ? 'border border-warning border-2' : '' ?>">
                    <div class="chat-preview-info">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h2 class="mb-0">
                                <?= htmlspecialchars($chat['nome_carta']) ?>
                            </h2>

                            <?php if ($hasUnread): ?>
                                <span class="badge rounded-pill bg-warning text-dark">
                                    <?= $unreadCount ?> nuovi
                                </span>
                            <?php endif; ?>
                        </div>

                        <p>
                            Conversazione con
                            <strong><?= htmlspecialchars($chat['nome_altro_utente']) ?></strong>
                        </p>

                        <p class="chat-preview-ultimo-messaggio">
                            <?= htmlspecialchars($chat['ultimo_messaggio'] ?? 'Nessun messaggio, inizia la conversazione!') ?>
                        </p>

                        <?php if (!empty($chat['data_ultimo_messaggio'])): ?>
                            <small>
                                Ultimo messaggio:
                                <?= htmlspecialchars(formatMessageDate($chat['data_ultimo_messaggio'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <a
                        class="btn-chat-open"
                        href="/pages/chat.php?id_chat=<?= urlencode($chat['id_chat']) ?>"
                    >
                        <?= $hasUnread ? 'Apri e leggi' : 'Apri chat' ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>