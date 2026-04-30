<?php
require_once __DIR__. '/../includes/session.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_chat = $_GET['id_chat'] ?? null;
if (!$id_chat){
    echo "Chat non trovata";
    exit;
}

require_once __DIR__."/../includes/header.php";
require_once __DIR__."/../includes/navbar.php";
?>

<main class="container">
    <h1>Chat</h1>

    <section id= "contenitore-messaggi" class="contenitore-messaggi"></section>

    <form id="form messaggio">
        <input
            type="text"
            id="input-messaggio"
            placeholder="Scrivi un messaggio..."
            required
        >
        <button type="submit">Invia</button>
    </form>
</main>

<script>
    const ID_CHAT = <?php echo json_encode($id_chat); ?>;
    const ID_UTENTE_CORRENTE = <?php echo json_encode($_SESSION['user_id']); ?>;
</script>

<script src="../assets/js/chat.js"></script>
<?php require_once __DIR__.'/../includes/footer.php'; ?>