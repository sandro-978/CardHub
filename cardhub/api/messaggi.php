<?php
require_once __DIR__ ."/../includes/session.php";
require_once __DIR__ ."/../includes/db.php";

header('Content-Type: application/json');

#definisco get e post per i messaggi

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "successo" => false,
        "errore" => "Utente non loggato"      
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];
$metodo = $_SERVER['REQUEST_METHOD'];
$dbconn = getDbConnection();

if(!$dbconn){
    echo json_encode([
        "successo" => false,
        "errore" => "Connessione al db fallita"
    ]);
    exit;
}

if ($metodo === 'GET') {
    $id_chat = $_GET['id_chat'] ?? null;

    if(!$id_chat){
        echo json_encode([
            "successo" => false,
            "errore" => "id_chat mancante"
        ]);
        exit;
    }
    $result = pg_query_params(
        $dbconn,
        "SELECT user_id,testo,created_at
         FROM messaggi
         WHERE id_chat = $1
         ORDER BY created_at ASC",
         [$id_chat]
    );
    if(!$result){
        echo json_encode([
            "successo"=> false,
            "errore" => "errore nel caricamento dei messaggi"
        ]);
        exit;
    }
    $messaggi = pg_fetch_all($result);
    if($messaggi === false){
        $messaggi = [];
    }
    echo json_encode([
        "successo" => true,
        "messaggi"=> $messaggi
    ]);
    exit;
}

if ($metodo === "POST"){

    $dati = json_decode(file_get_contents("php://input"), true);
    $id_chat = $dati["id_chat"] ?? null;
    $testo = trim($dati["testo"] ?? '');

    if(!$id_chat || $testo === ''){
        echo json_encode([
            "successo" => false,
            "messaggio" => "Dati mancanti"
        ]);
        exit;
    }
    $result = pg_query_params(
        $dbconn,
        "INSERT INTO messaggi (id_chat,user_id,testo)
        VALUES ($1,$2,$3)
        ",
         [$id_chat,$user_id,$testo]
    );
    if(!$result){
        echo json_encode([
            "successo"=> false,
            "errore" => "errore nell'invio dei messaggi"
        ]);
        exit;
    }
    
    echo json_encode([
        "successo" => true,
    ]);
    exit;
}

echo json_encode([
    "successo"=> false,
    "errore"=> "Metodo non supportato"
]);
