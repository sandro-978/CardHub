<?php
require_once __DIR__. '/../includes/session.php';
require_once __DIR__. '/../includes/db.php';
require_once __DIR__.'/../includes/header.php';
require_once __DIR__.'/../includes/navbar.php';


if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$dbconn = getDbConnection();

$query = pg_query_params(
    $dbconn,
    "SELECT chats.id_chat, chats.id_annuncio, chats.id_acquirente, chats.id_venditore, chats.created_at, cards.name AS nome_carta, listings.price AS prezzo, altro_utente.username AS nome_altro_utente,

        (
            SELECT messaggi.testo
            FROM messaggi
            WHERE messaggi.id_chat = chats.id_chat
            ORDER BY messaggi.created_at DESC
            LIMIT 1
        ) AS ultimo_messaggio,

        (
            SELECT messaggi.created_at
            FROM messaggi
            WHERE messaggi.id_chat = chats.id_chat
            ORDER BY messaggi.created_at DESC
            LIMIT 1
        ) AS data_ultimo_messaggio

    FROM chats JOIN listings ON chats.id_annuncio = listings.id JOIN cards ON listings.card_id = cards.id JOIN users AS altro_utente ON altro_utente.id = CASE
            WHEN chats.id_acquirente = $1 THEN chats.id_venditore
            ELSE chats.id_acquirente
        END

    WHERE chats.id_acquirente = $1 OR chats.id_venditore = $1

    ORDER BY data_ultimo_messaggio DESC NULLS LAST, chats.created_at DESC
    ", [$user_id]
);
if(!$query){
    die("Errore caricamento chat");
}

$chats = pg_fetch_all($query);
if($chats === false){
    $chats = [];
}
?>

<main class= "container">
    <h1 class= "chat-title">Messaggi</h1>
    <?php if(count($chats) === 0): ?> 
        <div class= "empty-chat-box"><p> Non hai ancora nessuna chat disponibile</p></div>
    <?php else: ?>

        <section class = "lista-chat">

            <?php foreach($chats as $chat): ?>

                <article class= "chat-preview">

                    <div class= "chat-preview-info">

                        <h2><?php echo htmlspecialchars($chat['nome_carta']);?></h2>
                        <p>Conversazione con <strong><?php echo htmlspecialchars($chat['nome_altro_utente']);?></strong></p>
                        <p class="chat-preview-ultimo-messaggio"> <?php echo htmlspecialchars($chat['ultimo_messaggio']?? 'Nessun messaggio, inizia la conversazione!');?></p>
                        <?php if (!empty($chat['data_ultimo_messaggio'])): ?><small>Ultimo messaggio: <?php echo htmlspecialchars($chat['data_ultimo_messaggio']); ?></small><?php endif; ?>
                    
                    </div>
                    <a class="btn-chat-open" href="chat.php?id_chat=<?php echo urlencode($chat['id_chat']); ?>">Apri chat</a>
                
                </article>
            
            <?php endforeach ;?>
       
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>