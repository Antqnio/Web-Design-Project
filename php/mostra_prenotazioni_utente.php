<?php
    /*
    Faccio in modo che ogni output generato
    venga salvato in un buffer interno anziché essere inviato immediatamente al browser.
    Impedisce di inviare una echo appena viene fatta.
    Utile per evitare che php "sporchi" il json da inviare tramite alla chiamata AJAX.
    */
    ob_start();
    require_once "session_check.php";
    if ($_SESSION["admin"] && $_SERVER["REQUEST_METHOD"] != "POST") { // Se POST, si arriva qui da mostra_iscritti.php
        header("location: mostra_prenotazioni_amministratore.php");
        exit();
    }

    require_once "mostra_prenotazioni_lib.php";


    $nome;
    $cognome;
    $email;
 
    function ottieni_email() : string {
        if ($_SESSION["admin"])
            return $_SESSION["email-utente"];
        return $_SESSION["email"];
    }

    function cancella_prenotazione() : void {
        $data = $_POST["data"];
        // Controllo la data inserita lato server (l'utente non può modificarla se non modificando direttamente l'html).
        $regex_data = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/";
        if (!preg_match($regex_data, $data)) {
            invia_JSON(array("messaggio" => "La data non è in un formato adatto " . $data));
        }
        $email = ottieni_email();
        require_once "cancella_prenotazione.php";
    }


    

    function invia_prenotazioni(int $righe_caricate_per_query = 5, int $offset_di_partenza = 0) : void {
        require_once "dbconnect.php";
        // Mostro le prenotazioni in ordine di data.
        $email = ottieni_email();
        $record_da_inviare = [];
        $nuove_righe_caricate = 0;
        $righe_caricate_totali = $offset_di_partenza;
        $query = "SELECT * FROM prenotazioni WHERE email=? ORDER BY `data` DESC LIMIT ? OFFSET ?;";
        if ($statement = mysqli_prepare($connessione, $query)) {
            // A ogni query mostro righe aggiuntive pari a righe_caricate_per_query a partire da $righe_caricate_totali.
            mysqli_stmt_bind_param($statement, 'sii', $email, $righe_caricate_per_query, $righe_caricate_totali);
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
                    $riga_array = ["id" => $righe_caricate_totali, "data" => $riga["data"], "ora" => $riga["ora"]];
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


    function controllaNomeCognome($identificatore) : bool {
        $regex = "/^[A-Z][a-z]{2,20}$/";
        if (preg_match($regex, $identificatore)) {
            return true;
        }
        return false;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if ($_SESSION["admin"] && isset($_POST["email"]) && controlla_email($_POST["email"])
            && isset($_POST["nome"]) && controllaNomeCognome(($_POST["nome"]))
            && isset($_POST["cognome"]) && controllaNomeCognome(($_POST["cognome"]))) { // Qui arriva un admin dal file mostra_iscritti.php
            
            $nome = $_POST["nome"];
            $cognome = $_POST["cognome"];
            $email = $_POST["email"];
            $_SESSION["email-utente"] = $email;

        }
        elseif (isset($_POST["richiesta_caricamento"])) {
            if ($_POST["richiesta_caricamento"] === "cancella" && isset($_POST["data"])) {
                cancella_prenotazione();
            }
            elseif ($_POST["richiesta_caricamento"] === "carica" &&
                isset($_POST["quante"]) && is_numeric($_POST["quante"]) && $_POST["quante"] > 0 &&
                is_numeric($_POST["offset"]) && $_POST["offset"] >= 0) {
                // Carico (se esistono) ulteriori prenotazioni dell'utente
                // Imposto che gli echo restituiscano in formato JSON alla chiamata AJAX.
                header('Content-Type: application/json');
                invia_prenotazioni($_POST["quante"], $_POST["offset"]);
            }
        }
    }
    elseif ($_SERVER["REQUEST_METHOD"] === "GET") { // Qui arriva un utente normale
        $nome = $_SESSION["nome"];
        $email = $_SESSION["email"];
    }

?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>
            Prenotazioni di
            <?php
                echo $nome;
            ?>
        </title>
        <meta name="description" content="Prenotazione">
        <?php
            require_once "head.php"
        ?>
        <script type="module" src="../js/mostra_prenotazioni_utente.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
        <script type="module" src="../js/mostra_prenotazioni_lib.js"></script>
        <link rel="stylesheet" href="../css/mostra_prenotazioni_utente.css">
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
            if (!$_SESSION["admin"]) {
                echo "
                    <h2>Ecco le sue prenotazioni, $nome:
                    </h2>
                ";
            }
            else {
                echo "
                    <h2>Prenotazioni di $nome $cognome ($email):
                    </h2>
                ";
            }
        ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Ora</th>
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