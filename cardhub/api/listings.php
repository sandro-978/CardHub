<?php
header('Content-Type: application/json; charset=utf-8');
echo file_get_contents(__DIR__ . '/../data/sample-listings.json');
?>
