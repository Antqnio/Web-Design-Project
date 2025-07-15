<?php
    // Funzioni ausiliarie per mostra_prenotazioni_utente.php e mostra_prenotazioni_amministratore.php

    
    // Usata per rendere cancellabili solo le prenotazioni odierne e con ora di inizio non passata.
    function inizio_nel_futuro(string $ora) : bool {
        [$inizio, $fine] = explode("-", $ora);
        $orario_inizio = new DateTime($inizio); // Uso DateTime perché mi fa confrontare direttamente gli orari.
        $ora_minuti_correnti = new DateTime(date("H:i"));
        if ($orario_inizio > $ora_minuti_correnti)
            return true;
        return false;
    }

    
    function invia_JSON($elemento) : void {
        /*
        ob_clean() pulisce il contenuto del buffer di output attivo.
        Usata per eventuali output non desiderati.
        Se non lo faccio, il JSON non viene inviato correttamente perché "sporcato".
        */
        ob_clean();
        // Invio come JSON i l'array dei record o il messaggio
        echo json_encode($elemento);
        die();
    }

    // Crea la parte terminale del body
    function crea_body() : void {
        echo '
            <p id="messaggio"></p>
            <button type="button" id="carica">Carica altre prenotazioni</button>
        ';
    }

    function controlla_email($email) : bool {
        $regex_email = "/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/";
        if (!preg_match($regex_email, $email)) {
            echo "L'email non è in un formato adatto: " . $email; // Usato nella cambia_password() in mostra_iscritti.php.
            exit();
            //  Al posto di fare return false, faccio direttamente exit(), che è un'ottimizzazione in quel caso.
        }
        return true; // Usato in mostra_prenotazioni_utente.php per la POST proveniente da mostra_iscritti.php
    }



