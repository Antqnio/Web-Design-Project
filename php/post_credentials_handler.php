<?php
// Usato per i file iscrizione.php e login.php
$errore = false;
$messaggio;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errore = !gestisciPost();
}
?>