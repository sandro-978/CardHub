<?php
require_once __DIR__ . '/config.php';

function getDbConnection()
{
    $connectionString = sprintf(
        'host=%s port=%s dbname=%s user=%s password=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASSWORD
    );

    $dbconn = pg_connect($connectionString);

    if (!$dbconn) {
        die('Errore di connessione al database.');
    }

    return $dbconn;
}
?>
