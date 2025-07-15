<?php
    
    require_once "password_check.php";
    require_once "find_user_record.php";

    function cancella_iscritto() : void {
        controlla_password("password");
        $riga = find_user_record($_SESSION["email"]);
        $hash = $riga["password"];
        $password = $_POST["password"];
        if (!password_verify($password, $hash)) {
            echo "Password non corretta";
            exit();
        }
        require "dbconnect.php"; // Usato al posto di require_once perché require_once è già stato usato in find_user_record.
        $sql = "DELETE FROM iscritti WHERE email=?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            mysqli_stmt_bind_param($statement, 's', $riga["email"]);
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
     

 
    function cambia_password() : void {
        controlla_password("password_nuova");
        controlla_password("password_vecchia");
        $riga = find_user_record($_SESSION["email"]);
        $hash = $riga["password"];
        if (!password_verify($_POST["password_vecchia"], $hash)) {
            echo "Password vecchia non corretta";
            exit();
        }
        if ($_POST["password_vecchia"] === $_POST["password_nuova"]) {
            echo "La nuova password deve essere diversa da quella vecchia";
            exit();
        }
        require "dbconnect.php"; // Usato al posto di require_once perché require_once è già stato usato in find_user_record.
        $sql = "UPDATE iscritti SET password = ? WHERE email = ?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            $hash = password_hash($_POST["password_nuova"], PASSWORD_BCRYPT);
            mysqli_stmt_bind_param($statement, 'ss', $hash, $riga["email"]);
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

    require_once "session_check.php";

    // Cancello l'utente
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["password"])) {
            cancella_iscritto();
        }
        elseif (isset($_POST["password_vecchia"]) && isset($_POST["password_nuova"])) {
            cambia_password();
        }
        exit();
    }
    
    
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>
            Profilo di 
            <?php
                echo $_SESSION["nome"];
            ?>
        </title>
        <meta name="description" content="Profilo Utente">
        <?php
            require_once "head.php"
        ?>
        <link rel="stylesheet" href="../css/profilo.css">
        <script type="module" src="../js/profilo.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
        <script type="module" src="../js/controlla_password.js"></script>
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
            if ($_SESSION["admin"])
                echo '<p id="profilo-amministratore">Profilo Amministratore</p>';
            echo 'Ciao, ' . $_SESSION["nome"] . '! Ecco i tuoi dati:';
        ?>
        <form action="logout.php" method="get">
            <button id="logout" type="submit">Logout</button>
        </form>
        <div id="contenitore-tabella-form">
            <?php
                echo '
                    <table>
                        <tr>
                            <th>Email:</th>
                            <td>' . $_SESSION["email"] . '</td>
                        </tr>
                        <tr>
                            <th>Nome:</th>
                            <td>' . $_SESSION["nome"] . '</td>
                        </tr>
                        <tr>
                            <th>Cognome:</th>
                            <td>' . $_SESSION["cognome"] . '</td>
                        </tr>
                ';
                if (!$_SESSION["admin"]) {
                    echo '
                        <tr>
                            <th>Membro da:</th>
                            <td>' . $_SESSION["dataIscrizione"] . '</td>
                        </tr>
                        <tr>
                            <th>Scadenza abbonamento:</th>
                            <td id="scadenza">' . $_SESSION["dataScadenza"] . '</td>
                        </tr>
                    ';
                }
                echo '</table>';
            ?>
            <div id="cancella">
                <form id="form-cancella" method="POST">
                    <p>Ci dispiace che tu voglia lasciarci. Per procedere, per favore, digiti la sua password:</p>
                    <input type="password" id="password" name="password" placeholder="••••••••••" pattern="^[A-Z][A-Za-z0-9'\$+@]{4,16}$" required>
                    <button type="submit" id="invio-cancellazione">Conferma</button>
                </form>
            </div>
            <div id="contenitore-cambio-password">
                <form id="form-password" method="POST">
                    <p>Per cambiare password, digiti sia la vecchia che la nuova password:</p>
                    <label for="password-vecchia">Password vecchia</label>
                    <input type="password" id="password-vecchia" name="password-vecchia" placeholder="••••••••••" pattern="^[A-Z][A-Za-z0-9'\$+@]{4,16}$" required>
                    <label for="password-nuova">Password nuova</label>
                    <input type="password" id="password-nuova" name="password-nuova" placeholder="••••••••••" pattern="^[A-Z][A-Za-z0-9'\$+@]{4,16}$" required>
                    <button type="submit" id="invia-cambio-password">Conferma</button>
                </form>
            </div>
        </div>
        <div id="contenitore-bottoni">
            <button type="button" id="cambia-password">Cambia Password</button>
            <button type="button" id="cancella-profilo">Cancella profilo</button>
        </div>
        <p id="messaggio"></p>
        
    </body>
</html>