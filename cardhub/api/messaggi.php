<?php
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "successo" => false,
        "errore" => "Utente non loggato"
    ]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$metodo = $_SERVER['REQUEST_METHOD'];
$dbconn = getDbConnection();

if (!$dbconn) {
    echo json_encode([
        "successo" => false,
        "errore" => "Connessione al database fallita"
    ]);
    exit;
}

function chatAppartieneAUtente($dbconn, int $id_chat, int $user_id): bool
{
    $result = pg_query_params(
        $dbconn,
        "
        SELECT id_chat
        FROM chats
        WHERE id_chat = $1
          AND (id_acquirente = $2 OR id_venditore = $2)
        LIMIT 1
        ",
        [$id_chat, $user_id]
    );

    return $result && pg_num_rows($result) === 1;
}

if ($metodo === 'GET') {
    $id_chat = (int)($_GET['id_chat'] ?? 0);

    if ($id_chat <= 0) {
        echo json_encode([
            "successo" => false,
            "errore" => "id_chat mancante o non valido"
        ]);
        exit;
    }

    if (!chatAppartieneAUtente($dbconn, $id_chat, $user_id)) {
        echo json_encode([
            "successo" => false,
            "errore" => "Chat non trovata o accesso non autorizzato"
        ]);
        exit;
    }

    /*
     * Quando l'utente apre la chat, tutti i messaggi ricevuti
     * da altri utenti vengono marcati come letti.
     */
    $readResult = pg_query_params(
        $dbconn,
        "
        UPDATE messaggi
        SET letto = TRUE
        WHERE id_chat = $1
          AND user_id <> $2
          AND letto = FALSE
        ",
        [$id_chat, $user_id]
    );

    if (!$readResult) {
        echo json_encode([
            "successo" => false,
            "errore" => "Errore nell'aggiornamento dello stato di lettura"
        ]);
        exit;
    }

    $result = pg_query_params(
        $dbconn,
        "
        SELECT
            id_messaggio,
            user_id,
            testo,
            letto,
            created_at
        FROM messaggi
        WHERE id_chat = $1
        ORDER BY created_at ASC, id_messaggio ASC
        ",
        [$id_chat]
    );

    if (!$result) {
        echo json_encode([
            "successo" => false,
            "errore" => "Errore nel caricamento dei messaggi"
        ]);
        exit;
    }

    $messaggi = pg_fetch_all($result);

    if ($messaggi === false) {
        $messaggi = [];
    }

    echo json_encode([
        "successo" => true,
        "messaggi" => $messaggi
    ]);
    exit;
}

if ($metodo === "POST") {
    $dati = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dati)) {
        echo json_encode([
            "successo" => false,
            "errore" => "Payload JSON non valido"
        ]);
        exit;
    }

    $id_chat = (int)($dati["id_chat"] ?? 0);
    $testo = trim($dati["testo"] ?? '');

    if ($id_chat <= 0 || $testo === '') {
        echo json_encode([
            "successo" => false,
            "errore" => "Dati mancanti"
        ]);
        exit;
    }

    if (strlen($testo) > 2000) {
        echo json_encode([
            "successo" => false,
            "errore" => "Il messaggio è troppo lungo"
        ]);
        exit;
    }

    if (!chatAppartieneAUtente($dbconn, $id_chat, $user_id)) {
        echo json_encode([
            "successo" => false,
            "errore" => "Chat non trovata o invio non autorizzato"
        ]);
        exit;
    }

    $result = pg_query_params(
        $dbconn,
        "
        INSERT INTO messaggi (id_chat, user_id, testo, letto)
        VALUES ($1, $2, $3, FALSE)
        RETURNING id_messaggio, user_id, testo, letto, created_at
        ",
        [$id_chat, $user_id, $testo]
    );

    if (!$result) {
        echo json_encode([
            "successo" => false,
            "errore" => "Errore nell'invio del messaggio"
        ]);
        exit;
    }

    $messaggio = pg_fetch_assoc($result);

    echo json_encode([
        "successo" => true,
        "messaggio" => $messaggio
    ]);
    exit;
}

echo json_encode([
    "successo" => false,
    "errore" => "Metodo non supportato"
]);