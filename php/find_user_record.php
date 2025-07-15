<?php
    // Usato in profilo.php per trovare il profilo di un utente nelle cancella_iscritto() e cambia_password().


    function find_user_record(string $email) : array {
        require_once "dbconnect.php";
        $sql = "SELECT * FROM iscritti WHERE email=?;";
        $statement = mysqli_prepare($connessione, $sql);
        if ($statement == false) {
            die(mysqli_connect_error());
        }
        mysqli_stmt_bind_param($statement, 's', $email);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result)===0) {
            // Se siamo qui, c'è un problema, perché l'utente deve esistere.
            echo "Email non registrata";
            exit();
        }
        // Visto che email è chiave, siamo certi che, arrivati qui, una e una sola riga con tale email esista.
        $riga = mysqli_fetch_assoc($result);
        return $riga;
    }
?>