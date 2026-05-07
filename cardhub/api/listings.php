<?php
require_once __DIR__ ."/../includes/session.php";
require_once __DIR__ ."/../includes/db.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$dbconn = getDbConnection();
if(!$dbconn){
    die('Errore di connessione');
}

if($metodo === 'GET'){
    header('Content-Type: application/json; charset=utf-8');

    $result = pg_query(
        $dbconn,"
        SELECT listings.id,listings.user_id,listings.card_id,listings.price,listings.condition,listings.description,listings.status,listings.created_at,cards.name,cards.game,cards.edition,cards.language,cards.image_url,users.username
        FROM listings JOIN cards ON listings.card_id = cards.id JOIN users ON listings.user_id = users.id
        ORDER BY listings.created_at DESC
        "
    );
    if(!$result){
        echo json_encode([
            'successo'=> false,
            'errore' => 'Errore nel caricamento degli annunci'
        ]);
        exit;
    }

    $listings = pg_fetch_all($result);
    if($listings === false){
        $listings = [];
    }
    echo json_encode([
        'successo' => true,
        'annunci' => $listings
    ]);
    exit;
}

if($metodo === 'POST'){
    if(!isset($_SESSION['user_id'])){
        header('Location: /auth/login.php');
        exit;
    }

    $estensioni = ['png', 'jpg', 'jpeg','gif','webp'];
    $user_id = $_SESSION['user_id'];
    $cardName = trim($_POST['cardName'] ?? '');
    $game = trim($_POST['game'] ??'');
    $edition = trim($_POST['edition'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $condition = trim($_POST['condition'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if($cardName === '' || $edition === '' || $language === '' || $condition === '' || $price === '' || $game === ''){
        die('Dati mancanti');
    }
    if(!is_numeric($price) || $price <= 0){
        die('Prezzo non valido');
    }

    $imageUrl = '/assets/img/placeholder-card.png';
    if(isset($_FILES['cardImage']) && $_FILES['cardImage']['error'] === UPLOAD_ERR_OK){
        $uploadDir = __DIR__.'/../assets/img/uploads/';

        $fileTmpPath = $_FILES['cardImage']['tmp_name'];
        $name = $_FILES['cardImage']['name'];

        $estensione = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $estensioniAccettate = ['png', 'jpg', 'jpeg','gif','webp'];
        if(!in_array($estensione, $estensioniAccettate)){
            die('estensione immagine non valida');
        }

        $nomeFile = uniqid("card_",true).'.'.$estensione;
        $destinazione = $uploadDir.$nomeFile;
        if(!move_uploaded_file($fileTmpPath,$destinazione)){
            die('Errore caricamento immagine');
        }
        $imageUrl = '/assets/img/uploads/'.$nomeFile;
    }

    $cardRis = pg_query_params(
        $dbconn,"
        INSERT INTO cards (name,game,edition,language,image_url)
        VALUES($1,$2,$3,$4,$5)
        RETURNING id",
        [$cardName,$game,$edition,$language,$imageUrl]
    );
    if(!$cardRis){
        die("Errore nella creazione della carta");
    }

    $card = pg_fetch_assoc($cardRis);
    $card_id = $card["id"];
    $listingResult = pg_query_params(
        $dbconn,"
        INSERT INTO listings (user_id,card_id,price,condition,description)
        VALUES($1,$2,$3,$4,$5)",
        [$user_id,$card_id,$price,$condition,$description]
    );
    if(!$listingResult){
        die("Errore nella creazione dell'annuncio");
    }
    header('Location: /pages/marketplace.php');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'successo'=> false,
    'errore'=> 'Metodo non supportato'
]);