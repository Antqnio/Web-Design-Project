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

    

    function cancella_profilo() : void {
        // Controllo l'input.
        $email = $_POST["email"];
        controlla_email($email);
        require_once "dbconnect.php";
        $sql = "DELETE FROM iscritti WHERE email=?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            // Cancello il profilo.
            mysqli_stmt_bind_param($statement, 's', $email);
            mysqli_stmt_execute($statement);
            $numero_righe = mysqli_affected_rows($connessione);
            if ($numero_righe === -1) {
                echo "Errore nella query: " . mysqli_error($connessione);
                exit();
            }
            elseif ($numero_righe != 1) {
                echo "C'è stato un errore di interazione col database. Per favore, riprovi.";
                exit();
            }
            else {
                echo "Successo";
                exit();
            }

        }
        else {
            die(mysqli_connect_error());
        }
    }

    require_once "password_check.php";

    function cambia_password() : void {
        // Controllo l'input
        controlla_password("password");
        $email = $_POST["email"];
        controlla_email($email);
        require_once "dbconnect.php";
        $sql = "UPDATE iscritti SET password = ? WHERE email = ?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
            mysqli_stmt_bind_param($statement, 'ss', $hash, $email);
            mysqli_stmt_execute($statement);
            $numero_righe = mysqli_affected_rows($connessione);
            if ($numero_righe != 1) {
                echo "C'è stato un errore di interazione col database. Per favore, riprova";
                exit();
            }
            else {
                echo "Successo";
                exit();
            }

        }
        else {
            echo "C'è stato un errore di interazione col database. Per favore, riprova";
            die(mysqli_connect_error());
        }
    }

    require_once "find_user_record.php";

    function rinnova_abbonamento() : void {
        controlla_email($_POST["email"]);
        if ($_POST["giorni"] == 0 && $_POST["settimane"] == 0 && $_POST["mesi"] == 0 && $_POST["anni"] == 0) {
            echo 'Almeno uno tra "giorni", "settimane", "mesi" e "anni" deve essere maggiore di 0';
            return;
        }
        $riga = find_user_record($_POST["email"]);
        $oggi = new DateTime();
        $oggi->setTime(0, 0, 0); // Imposta l'ora a mezzanotte
        $data_scadenza = new DateTime($riga["dataScadenza"]);
        $data_scadenza->setTime(0, 0, 0); // Resetto l'ora per un confronto preciso
        $query = null;
        $giorni = (int)$_POST["giorni"];
        if ($data_scadenza < $oggi) {
            // Rinnovo: partiamo da CURRENT_DATE()
            $query = "UPDATE iscritti 
                      SET dataScadenza = DATE_ADD(
                                            DATE_ADD(
                                                DATE_ADD(
                                                    DATE_ADD(CURRENT_DATE(), INTERVAL ? DAY),
                                                INTERVAL ? WEEK),
                                            INTERVAL ? MONTH),
                                        INTERVAL ? YEAR)
                      WHERE email = ?";
            // Sottraggo un giorno per permettere l'accesso giornaliero
            // (una persona rinnova solo per un giorno, ovvero il giorno odierno).
            // In più, una persona rinnova a partire da CURRENT_DATE(), quindi bisogna contare
            // il giorno corrente nei giorni di rinnovo (sennò una persona che rinnova di 1 giorno,
            // in realtà, rinnoverebbe per 2 giorni: current_date() e il giorno successivo).
            $giorni -= 1;
        } else {
            // Estendo: partiamo da dataScadenza attuale
            $query = "UPDATE iscritti 
                      SET dataScadenza = DATE_ADD(
                                            DATE_ADD(
                                                DATE_ADD(
                                                    DATE_ADD(dataScadenza, INTERVAL ? DAY),
                                                INTERVAL ? WEEK),
                                            INTERVAL ? MONTH),
                                        INTERVAL ? YEAR)
                      WHERE email = ?;";
        }
        require "dbconnect.php"; // Usato al posto di require_once perché require_once è già stato usato in find_user_record.
        $settimane = (int)$_POST["settimane"];
        $mesi = (int)$_POST["mesi"];
        $anni = (int)$_POST["anni"];
        $email = $_POST["email"]; // Non serve cast perché è una stringa
        $record_da_inviare = []; // Invio direttamente la data da PHP e non la modifico in JavaScript perché MySQL e JavaScript hanno modi troppo diversi di gestire le date.
        if ($statement = mysqli_prepare($connessione, $query)) {
            mysqli_stmt_bind_param($statement, 'iiiis', $giorni, $settimane, $mesi, $anni, $email);
            mysqli_stmt_execute($statement);
            if (mysqli_affected_rows($connessione) == 0) {
                $record_da_inviare["messaggio"] = "Errore di interazione col database";
            }
            else {
                $query = "SELECT dataScadenza FROM iscritti WHERE email = ?";
                if ($statement = mysqli_prepare($connessione, $query)) {
                    mysqli_stmt_bind_param($statement, 's', $email);
                    mysqli_stmt_execute($statement);
                    $result = mysqli_stmt_get_result($statement);
                    $riga = mysqli_fetch_assoc($result);
                    if(mysqli_num_rows($result) == 0) {
                        $record_da_inviare["messaggio"] = "Errore di interazione col database"; // Deve esistere la mail, a questo punto.
                    }
                    else {
                        $record_da_inviare["dataScadenza"] = $riga["dataScadenza"];
                    }
                }
                else {
                    die(mysqli_connect_error());
                }
            }
            invia_JSON($record_da_inviare);
        } else {
            die(mysqli_connect_error());
        }
    }


    function carica_iscritti(int $righe_caricate_per_query = 5, int $offset_di_partenza = 0) : void {
        require_once "dbconnect.php";
        
        $record_da_inviare = [];
        $nuove_righe_caricate = 0;
        $righe_caricate_totali = $offset_di_partenza;
        $query = "SELECT * FROM iscritti WHERE admin = 0 ORDER BY dataIscrizione LIMIT ? OFFSET ?;";
        if ($statement = mysqli_prepare($connessione, $query)) {
            // A ogni query mostro righe aggiuntive pari a righe_caricate_per_query a partire da $righe_caricate_totali.
            mysqli_stmt_bind_param($statement, 'ii', $righe_caricate_per_query, $righe_caricate_totali);
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
                    $riga_array = ["id" => $righe_caricate_totali, "email" => $riga["email"],
                    "nome" => $riga["nome"], "cognome" => $riga["cognome"],
                    "dataIscrizione" => $riga["dataIscrizione"], "dataScadenza" => $riga["dataScadenza"]];
                    ++$nuove_righe_caricate;
                    // Aggiungo in coda il nuovo record.
                    $record_da_inviare[] = $riga_array;
                }
                if ($nuove_righe_caricate === 0) {
                    // Ho mostratato tutti gli utenti non admin
                    $record_da_inviare["messaggio"] = "Nessun record";
                }

            }
            invia_JSON($record_da_inviare);
                
        }
        else {
            die(mysqli_connect_error());
        }
        
    }


    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["richiesta_caricamento"])) {
        if ($_POST["richiesta_caricamento"] === "carica" &&
            isset($_POST["quante"]) && is_numeric($_POST["quante"]) && $_POST["quante"] > 0 &&
            is_numeric($_POST["offset"]) && $_POST["offset"] >= 0) {
            header('Content-Type: application/json');
            carica_iscritti($_POST["quante"], $_POST["offset"]);
        }
        elseif (isset($_POST["email"]) && is_string($_POST["email"])) {
            //echo "giorni = " . $_POST["giorni"] . ", " . "settimane = " . $_POST["settimane"] . ", " . "mesi = " . $_POST["mesi"] . ", " . "anni = " . $_POST["anni"] . ", richiesta_caricamento = " . $_POST["richiesta_caricamento"];
            if ($_POST["richiesta_caricamento"] === "cancella") {
                cancella_profilo();
            }
            elseif ($_POST["richiesta_caricamento"] === "cambia" && isset($_POST["password"])) {
                // Carico (se esistono) ulteriori prenotazioni dell'utente
                // Imposto che gli echo restituiscano in formato JSON alla chiamata AJAX.
                cambia_password();
            }
            // Ci sono dei controlli in più perché is_int() non funzione con le stringhe (non fa il cast implicito)
            elseif ($_POST["richiesta_caricamento"] === "rinnova"
            && isset($_POST["giorni"]) && is_numeric($_POST["giorni"]) && $_POST["giorni"] == (int)$_POST["giorni"] && (int)$_POST["giorni"] >= 0
            && isset($_POST["settimane"]) && is_numeric($_POST["settimane"]) && $_POST["settimane"] == (int)$_POST["settimane"] && (int)$_POST["settimane"] >= 0
            && isset($_POST["mesi"]) && is_numeric($_POST["mesi"]) && $_POST["mesi"] == (int)$_POST["mesi"] && (int)$_POST["mesi"] >= 0
            && isset($_POST["anni"]) && is_numeric($_POST["anni"]) && $_POST["anni"] == (int)$_POST["anni"] && (int)$_POST["anni"] >= 0) {
                rinnova_abbonamento();
                exit();
            }

        }
        exit();
    }
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>
            Iscritti di Platinum Gym
        </title>
        <meta name="description" content="Iscritti di Platinum Gym">
        <?php
            require_once "head.php"
        ?>
        <script type="module" src="../js/mostra_iscritti.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
        <script type="module" src="../js/mostra_prenotazioni_lib.js"></script>
        <script type="module" src="../js/controlla_password.js"></script>
        <link rel="stylesheet" href="../css/mostra_iscritti.css">
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
        ?>
        <div id="rinnova" style="display: none;">
            <p id="messaggio-rinnovo"></p>
            <form id="invio-rinnovo">
                <label for="giorni">Giorni</label>
                <input type="number" value="0" min="0" name="giorni" id="giorni" placeholder="0" required>
                <label for="settimane">Settimane</label>
                <input type="number" value="0" min="0" name="settimane" id="settimane" placeholder="0" required>
                <label for="mesi">Mesi</label>
                <input type="number" value="0" min="0" name="mesi" id="mesi" placeholder="0" required>
                <label for="anni">Anni</label>
                <input type="number" value="0" min="0" name="anni" id="anni" placeholder="0" required>
                <button id="invia" type="submit">Invia</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Membro dal</th>
                    <th>Scadenza abbonamento</th>
                    <!-- Metto dei th vuoti per uniformità della tabella -->
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" id="carica">Carica altri iscritti</button>
        <p id="messaggio"></p>
        <?php
            require_once "footer.php";
        ?>
    </body>
</html>