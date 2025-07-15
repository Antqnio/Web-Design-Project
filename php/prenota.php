<?php
    
    require_once "user_session_check.php";

    define("FASCE_ORARIE", ["08:00-10:00", "10:00-12:00", "12:00-14:00", "14:00-16:00", "16:00-18:00", "18:00-20:00", "20:00-22:00"]);

    define("POSTI_TOTALI", 5); // Suppongo ci siano 5 posti disponibili per fascia oraria.


    // Usata per controllare se l'utente sta prenotando, nel giorno odierno, un orario non passato.
    function orarioNelFuturo(string $ora) : bool {
        [$inizio, $fine] = explode("-", $ora);
        $orario_fine = new DateTime($fine); // Uso DateTime perché mi fa confrontare direttamente gli orari.
        $ora_minuti_correnti = new DateTime(date("H:i"));
        if ($ora_minuti_correnti > $orario_fine)
            return false;
        return true;
    }


    require_once "find_user_record.php";

    function effettua_prenotazione() : void {
        
        // Segno la prenotazione dell'utente.

        // Validazione input lato server.
        $data = $_POST["data"];
        $ora = $_POST["ora"];
        
        // Faccio i controlli sull'input passato dall'utente.

        // Controllo la data con una ragex.
        $regex_data = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/";
        if (!preg_match($regex_data, $data)) {
            echo "La data non è in un formato adatto";
            return;
        }
        $data_input = new DateTime($data);
        $oggi = new DateTime();
        $oggi->setTime(0, 0, 0); // Metto l'ora di oggi a 00:00:00, come quella che ho in $data_input.
        $limite = (new DateTime())->modify('6 days'); // 7 giorni in futuro rispetto a oggi. Al massimo si può prenotare 6 giorni nel futuro.
        if ($data_input < $oggi) {
            echo "La data è nel passato";
            return;
        }
        if ($data_input > $limite) {
            echo "La data è oltre una settimana nel futuro";
        }
        if (!in_array($ora, FASCE_ORARIE)) {
            echo "L'ora non è in un formato valido";
            return;
        }
        // Controllo su giorno corretto ma ora passata.
        if ($data_input == $oggi && !orarioNelFuturo($ora)) {
            echo "Stai cercando di prenotare un'ora di oggi nel passato";
            return;
        }
        //Controllo se l'abbonamento dell'utente sarà scaduto in quella data (dataScadenza è l'ultimo giorno
        //di abbonamento valido).
        $riga = find_user_record($_SESSION["email"]);
        $data_scadenza = new DateTime($riga["dataScadenza"]);
        if ($data_input > $data_scadenza) {
            echo 'Il tuo abbonamento sarà scaduto per quella data';
            return;
        }
        //Controllo se l'utente ha già una prenotazione in questo giorno. Se sì, impedisco di prenotare.
        require "dbconnect.php"; // Non uso require_once perché già incluso tramite "require_once "find_user_record.php";"
        $email = $_SESSION["email"];
        $sql = "SELECT * FROM prenotazioni WHERE email=? AND data=?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            mysqli_stmt_bind_param($statement, 'ss', $email, $data);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            if(mysqli_num_rows($result) > 0) {
                echo "Hai già una prenotazione in questa data";
                return;
            }
            // Segno la prenotazione.
            $sql = "INSERT INTO prenotazioni(email, `data`, ora) VALUES (?, ?, ?)";
            if ($statement = mysqli_prepare($connessione, $sql)) {
                mysqli_stmt_bind_param($statement, 'sss', $email, $data, $ora);
                mysqli_stmt_execute($statement);

            }
            else {
                die(mysqli_connect_error());
                echo mysqli_connect_error();
            }
        }
        else {
            die(mysqli_connect_error());
        }
        echo "Prenotazione avvenuta con successo";
    }


    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["data"]) && isset($_POST["ora"])) {
        effettua_prenotazione();
        exit(); // Messo per non inviare come risposta alla chiamata AJAX tutto il resto del file php.
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <title>
            Pagina di Prenotazione
        </title>
        <meta name="description" content="Prenotazione">
        <?php
            require_once "head.php"
        ?>
        <script type="module" src="../js/prenota.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <link rel="stylesheet" href="../css/prenota.css">
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
        ?>
        <!-- Cambiare in questo modo:
         - Un utente può prenotare solo le date entro una settimana
         - Fare sottoforma di tabella per impedire di prenotare certe date
         - Usare AJAX -->
        <ul id="legenda">
            <li class="li-prenotato">Posto prenotato</li>
            <li class="li-disponibile">Posto disponibile</li>
            <li class="li-non-disponibile">Posto non disponbile</li>
            <li class="li-non-valido">Orario non valido</li>
            <li class="li-domenica">Domenica</li>
        </ul>
        <table id="calendario">
            <?php
                require_once "dbconnect.php";
                // Per 7 giorni a partire dal giorno corrente, guardo quante prenotazioni ci sono per orario.
                $dataCorrente = date("Y-m-d"); // Formato compatibile con sql
                // Prima controllo che esistano le fasce orarie per quelle date.
                //$arrayPrenotazioni = [];
                define("NUM_FASCE_ORARIE", count(FASCE_ORARIE));
                define("GIORNI", ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"]);
                define("NUMERO_GIORNI", count(GIORNI)); //7 giorni alla settimana.
                
                // date('w') dà il giorno odierno come indice (0 --> Domenica, 1 --> Lunedì...)
                // Creo l'header della tabella
                echo '<thead>';
                echo '<tr>';
                echo '<th>\\</th>';
                    for ($i = 0, $giorno_numerico = date('w'), $data = new DateTime(); $i < NUMERO_GIORNI; ++$i, $giorno_numerico = ($giorno_numerico + 1) % NUMERO_GIORNI, $data->modify('1 day')) {
                        echo "<th>" . GIORNI[$giorno_numerico] . "<br>" . $data->format('Y-m-d') . "</th>";
                    }
                echo '</tr>';
                echo '
                    </thead>
                    <tbody>';
                // Faccio 7 iterazioni e indicizzo a partire dal giorno corrente 
                //for ($i = 0; $i < 7; ++$i, $giorno_numerico = ($giorno_numerico + 1) % 7) {
                // Aggiungere il colore blu per indicare dove l'utente ha prenotato.
                for ($i = 0; $i < NUM_FASCE_ORARIE; ++$i) {
                    echo '<tr>';
                    echo '<th>' . FASCE_ORARIE[$i] . '</th>';
                    for ($j = 0, $giorno_numerico = date('w'), $data = new DateTime(); $j < NUMERO_GIORNI; ++$j, $giorno_numerico = ($giorno_numerico + 1) % NUMERO_GIORNI, $data->modify('1 day')) {
                        // mysqli mi dà problemi con + INTERVAL
                        // $query = "SELECT COUNT(*) AS prenotazioniPerFasciaOraria FROM prenotazioni WHERE `data` = current_date() + INTERVAL ? DAY AND ora = ?;";
                        $data_query = $data->format('Y-m-d');
                        $fascia_oraria = FASCE_ORARIE[$i];
                        $query =  "SELECT COUNT(*) AS prenotazioniPerFasciaOraria FROM prenotazioni WHERE `data` = ? AND ora = ?;";
                        
                        
                        if ($statement = mysqli_prepare($connessione, $query)) {
                            mysqli_stmt_bind_param($statement, 'ss', $data_query, $fascia_oraria);
                            mysqli_stmt_execute($statement);
                            $result = mysqli_stmt_get_result($statement);
                            if(mysqli_num_rows($result)===0) {
                                echo '<script>console.log("Errore di MySQL: se non fosse stata trovata alcuna corrispondenza per \"data\" e ora, la query avrebbe dovruto restituire 0")</script>';
                            }
                            else {
                                $riga = mysqli_fetch_assoc($result);
                                $prenotazioni = $riga["prenotazioniPerFasciaOraria"];
                                echo "<td id='" . $fascia_oraria . "|" . $data->format("Y-m-d") . "' ";
                                if (GIORNI[$giorno_numerico] == "Domenica") {
                                    echo "class='domenica'>";
                                }
                                else {
                                    // Se l'iscritto ha prenotato in quella data a quell'ora, coloro di blu
                                    $query_prenotazione = "SELECT * FROM prenotazioni WHERE `data` = ? AND ora = ? AND email = ?";
                                    if(session_status() === PHP_SESSION_NONE){
                                        session_start();
                                    }
                                    $email = $_SESSION["email"];
                                    $prenotato = false;
                                    if ($statement = mysqli_prepare($connessione, $query_prenotazione)) {
                                        mysqli_stmt_bind_param($statement, 'sss', $data_query, $fascia_oraria, $email);
                                        mysqli_stmt_execute($statement);
                                        $result = mysqli_stmt_get_result($statement);
                                        if (mysqli_num_rows($result) > 0) { // Al massimo è uguale a 1, visto che la coppia (email, data) è chiave della relazione iscritti.
                                            // L'utente ha una prenotazione in quella data a quell'ora.
                                            echo "class='prenotato ";
                                            $prenotato = true;
                                        }
                                    }
                                    else {
                                        die(mysqli_connect_error());
                                    }
                                    $posti_liberi = POSTI_TOTALI - $prenotazioni; // Serve per l'elseif
                                    // Controllo $j == 0 per vedere se sono al giorno di oggi
                                    // Solo se sono al giorno odierno, non rendo disponibili gli orari passati
                                    // Anche se un utente ha prenotato, gli permetto di vedere i posti nella stessa ora e nelle altre fasce
                                    // orarie (casomai cambiasse idea).
                                    if ($posti_liberi > 0 && ($j != 0 || ($j == 0 && orarioNelFuturo($fascia_oraria)))) {
                                        if ($prenotato) {
                                            echo "disponibile";
                                        }
                                        else {
                                            echo "class='disponibile"; // Stampo i posti liberi solo per gli orari validi per privacy.
                                        }
                                        echo "'> $posti_liberi";
                                    } 
                                    else {
                                        if ($prenotato) {
                                            echo "non-disponibile' ";
                                        }
                                        else {
                                            echo "class='non-disponibile' ";
                                        }
                                        echo '>';
                                    }
                                }
                                echo '</td>';
                            }
                            
                           
                        }
                        else {
                            die(mysqli_connect_error());
                        }
                    }
                    echo '</tr>';
                }
                echo '</tbody>';
            ?>
        </table>
        <p id="istruzioni">Il numero contenuto in una cella indica il numero di posti liberi.</p>
        <p id="messaggio"></p>
        <?php
            require_once "footer.php"
        ?>
    </body>
</html>