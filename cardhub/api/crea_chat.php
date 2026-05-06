<?php
require __DIR__ . '/../includes/session.php';
require_once __DIR__ .'/../includes/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /auth/login.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: /pages/marketplace.php');
    exit;
}

$id_acquirente = $_SESSION['user_id'];
$id_annuncio = $_POST['id_annuncio'];
if(!$id_annuncio){ die('Annuncio Mancante');}

$dbconn = getDbConnection();
if(!$dbconn){die("connessione non riuscita");}

$annucnioRis = pg_query_params($dbconn,"
    SELECT id,user_id
    FROM listings
    WHERE id = $1
",[$id_annuncio]
);

$annuncio = pg_fetch_assoc($annucnioRis);
if(!$annuncio){die("Annucnio non trovato");}

$id_venditore = $annuncio['user_id'];

$chatRis = pg_query_params($dbconn,'
    SELECT id_chat
    FROM chats
    WHERE id_annuncio = $1 AND id_acquirente = $2 AND id_venditore = $3
',[$id_annuncio,$id_acquirente,$id_venditore]
);

$chat = pg_fetch_assoc($chatRis);
if($chat){
    header('Location: /pages/chat.php?id_chat=' . $chat['id_chat']);
    exit;
}

$nuovaChatRis = pg_query_params($dbconn,'
    INSERT INTO chats (id_annuncio,id_acquirente,id_venditore)
    VALUES ($1,$2,$3)
    RETURNING id_chat
',[$id_annuncio,$id_acquirente,$id_venditore]
);

$nuovaChat = pg_fetch_assoc($nuovaChatRis);
if(!$nuovaChat){die("errore creazione nuova chat");}

header('Location: /pages/chat.php?id_chat=' . $nuovaChat['id_chat']);
exit;
