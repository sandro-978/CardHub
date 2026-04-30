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

if ($metodo === 'GET') {
    $id_chat = $_GET['id_chat'] ?? null;

    if(!$id_chat){
        echo json_encode([
            "successo" => false,
            "errore" => "id_chat mancante"
        ]);
        exit;
    }

    $query = $pdo->prepare("
        SELECT user_id, testo, created_at
        FROM messaggi
        WHERE id_chat = ?
        ORDER BY created_at ASC
    ");

    $query -> execute([$id_chat]);

    echo json_encode([
        "successo" => true,
        "messaggi" => $query->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
}

if ($metodo === "POST"){

    $dati = json_decode(file_get_contents("php://input"), true);
    $id_chat = $dati["id_chat"] ?? null;
    $testo = $dati["testo"] ?? '';

    if(!$id_chat || $testo === ''){
        echo json_encode([
            "successo" => false,
            "messaggio" => "Dati mancanti"
        ]);
        exit;
    }
    $query = $pdo->prepare("
        INSERT INTO messaggi (id_chat, user_id, testo)
        VALUES (?,?,?)
    ");

    $query -> execute([$id_chat,$user_id, $testo]);
    echo json_encode([
        "successo" => true,
    ]);
    exit;
}

echo json_encode([
    "successo"=> false,
    "errore"=> "Metodo non supportato"
]);
