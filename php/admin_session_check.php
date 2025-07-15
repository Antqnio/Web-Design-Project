<?php
    // Usato per impedire agli utenti di vedere certe mostra_iscritti.php e mostra_prenotazioni_amministratore.php
    require_once "session_check.php";
    if (!$_SESSION["admin"]) {
        echo 'Errore: non hai il permesso di accedere a questa pagina';
        exit();
    }
?>