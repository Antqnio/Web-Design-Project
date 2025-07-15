<?php
    require_once "dbconnect.php";
    $query = "DELETE FROM prenotazioni WHERE email = ? AND `data` = ?";
    if ($statement = mysqli_prepare($connessione, $query)) {
        // A ogni query mostro righe aggiuntive pari a righe_caricate_per_query a partire da $righe_caricate_totali.
        mysqli_stmt_bind_param($statement, 'ss', $email, $data);
        mysqli_stmt_execute($statement);
        if(mysqli_affected_rows($connessione) == 1) {
            $record_da_inviare["messaggio"] = "Successo";
        }
        else {
            $record_da_inviare["messaggio"] = "Errore";
        }
        invia_JSON($record_da_inviare);
    }
    else {
        die(mysqli_connect_error());
    }
?>