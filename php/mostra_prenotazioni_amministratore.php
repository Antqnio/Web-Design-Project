<?php
    require_once "admin_session_check.php";

    /*
    Faccio in modo che ogni output generato
    venga salvato in un buffer interno anziché essere inviato immediatamente al browser.
    Impedisce di inviare una echo appena viene fatta.
    Utile per evitare che php "sporchi" il json da inviare tramite alla chiamata AJAX.
    */
    ob_start();

    require_once "mostra_prenotazioni_lib.php";


    function cancella_prenotazione() : void {
        $data = $_POST["data"];
        $email = $_POST["email"];
        // Controllo la data inserita lato server (l'utente non può modificarla se non modificando direttamente l'html).
        $regex_data = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/";
        if (!preg_match($regex_data, $data)) {
            invia_JSON(array("messaggio" => "La data non è in un formato adatto: " . $data));
        }
        $regex_email = "/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/";
        if (!preg_match($regex_email, $email)) {
            invia_JSON(array("messaggio" => "L'email non è in un formato adatto: " . $email));
        }
        require_once "cancella_prenotazione.php";
    }


    function invia_prenotazioni(int $righe_caricate_per_query = 5, int $offset_di_partenza = 0) : void {
        require_once "dbconnect.php";
        $record_da_inviare = [];
        $data = $_POST["data"];
        $regex = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/";
        if (!preg_match($regex, $data)) {
            // Se la data non è valida, faccio conto che non ci siano record
            $record_da_inviare["messaggio"] = "Nessun record";
            invia_JSON($record_da_inviare);
        }
        // Mostro le prenotazioni in ordine di data.
        
        $nuove_righe_caricate = 0;
        $righe_caricate_totali = $offset_di_partenza;
        $query = "SELECT * FROM prenotazioni WHERE data=? ORDER BY ora ASC, email ASC LIMIT ? OFFSET ?;";
        if ($statement = mysqli_prepare($connessione, $query)) {
            // A ogni query mostro righe aggiuntive pari a righe_caricate_per_query a partire da $righe_caricate_totali.
            mysqli_stmt_bind_param($statement, 'sii', $data, $righe_caricate_per_query, $righe_caricate_totali);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            if(mysqli_num_rows($result) == 0) {
                $record_da_inviare["messaggio"] = "Nessun record";
            }
            else {
                // Array da inviare come JSON alla chiamata AJAX.
                // Metto le parentesi a riga per evitare che le venga assegnato un booleano per via della priorità degli operatori
                while (($riga = mysqli_fetch_assoc($result)) && $nuove_righe_caricate < $righe_caricate_per_query) {
                    ++$righe_caricate_totali;
                    $riga_array = ["id" => $righe_caricate_totali, "data" => $riga["data"], "ora" => $riga["ora"],
                    "email" => $riga["email"]];
                    $data = new DateTime($riga["data"]);
                    $ora = $riga["ora"];
                    $oggi = new DateTime();
                    $oggi->setTime(0, 0, 0);
                    // Rendo cancellabili solo le prenotazioni odierne il cui inizio è ancora in un orario futuro.
                    if ($data > $oggi || ($data == $oggi && inizio_nel_futuro($ora))) {
                       $riga_array["rimuovibile"] = true;
                    }
                    ++$nuove_righe_caricate;
                    // Aggiungo in coda il nuovo record.
                    $record_da_inviare[] = $riga_array;
                }

            }
            invia_JSON($record_da_inviare);
            
            
                
        }
        else {
            die(mysqli_connect_error());
        }
        
        
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["richiesta_caricamento"]) && $_POST["richiesta_caricamento"] === "cancella" && 
            isset($_POST["data"]) && isset($_POST["email"])) {
            cancella_prenotazione();
        }
        elseif (isset($_POST["richiesta_caricamento"]) && $_POST["richiesta_caricamento"] === "carica" &&
            isset($_POST["quante"]) && is_numeric($_POST["quante"]) && $_POST["quante"] > 0 &&
            is_numeric($_POST["offset"]) && $_POST["offset"] >= 0 && isset($_POST["data"])) {
            // Carico (se esistono) ulteriori prenotazioni dell'utente
            // Imposto che gli echo restituiscano in formato JSON alla chiamata AJAX.
            header('Content-Type: application/json');
            invia_prenotazioni($_POST["quante"], $_POST["offset"]);
        }
        exit();
    }


    $dataFutura = new DateTime(); // Data attuale
    $dataFutura->modify('+6 days');
    
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>
            Pagina di Prenotazione - Amministratore
        </title>
        <meta name="description" content="Prenotazione - Amministratore">
        <?php
            require_once "head.php"
        ?>
        <script type="module" src="../js/mostra_prenotazioni_amministratore.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
        <script type="module" src="../js/mostra_prenotazioni_lib.js"></script>
        <link rel="stylesheet" href="../css/mostra_prenotazioni_amministratore.css">
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
        ?>
        <form id="seleziona-data">
            <label for="data">Seleziona una data</label>
            <input type="date" id="data" name="data" max="<?php echo $dataFutura->format('Y-m-d'); ?>">
            <button id="mostra-prenotazioni" type="button">Mostra prenotazioni</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Ora</th>
                    <th>Email</th>
                    <th>Cancella</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <?php
            crea_body();
            require_once "footer.php";
        ?>
    </body>
</html>