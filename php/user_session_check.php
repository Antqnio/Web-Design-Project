<?php
    // Usato per reindirizzare gli amministratori che cercano di accedere a prenota.php o mostra_prenotazioni_utente.php
    // a mostra_prenotazioni_amministratore.php.
    require_once "session_check.php";
    if ($_SESSION["admin"]) {
        header("location: mostra_prenotazioni_amministratore.php");
        exit();
    }
?>