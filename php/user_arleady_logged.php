<?php
if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Avvio la sessione per controllare se una sessione esiste già.
        if (isset($_SESSION["email"]) && isset($_SESSION["nome"]) && isset($_SESSION["cognome"]) &&
            isset($_SESSION["admin"])) {
            // L'utente è già loggato, dunque gli impedisco di vedere questa pagina.
            header("location: index.php");
            exit();
        }
    }
?>