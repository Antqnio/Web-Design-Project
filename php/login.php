<?php
    require_once "user_arleady_logged.php";
    require_once "login_signup_common_checks.php";
    function gestisciPost() {
        // Imposto il messaggio per eventuale errori.
        global $messaggio;
        //controllo l'email lato server
        $email = null;
        if (!controlla_email($email))
            return;
        //controllo la password lato server
        $password = null;
        if (!controlla_password($password))
            return;
        // Provo a connettermi al db
        require_once "dbconnect.php";
        // Se arrivo qui, recupero le informazioni (mancanti) sugli iscritti.
        $sql = "SELECT * FROM iscritti WHERE email=?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            mysqli_stmt_bind_param($statement, 's', $email);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            if(mysqli_num_rows($result)===0) {
                echo "Email non registrata";
                return;
            }
            // Visto che email è chiave, siamo certi che, arrivati qui, una e una sola riga con tale email esista.
            $riga = mysqli_fetch_assoc($result);
            $hash = $riga["password"];
            if (!password_verify($password, $hash)) {
                echo "Password non corretta";
                return;
            }
            // Se arrivo qui, avvio la sessione e imposto le variabili di tale sessione.
            if(session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["email"] = $riga['email'];
            $_SESSION["nome"] = $riga['nome'];
            $_SESSION["cognome"] = $riga['cognome'];
            if ($riga["admin"]) {
                $_SESSION["admin"] = true; // Imposto la sessione admin.
            }
            else {
                $_SESSION["admin"] = false; // Imposto la sessione utente.
                $_SESSION["dataIscrizione"] = $riga["dataIscrizione"];
                $_SESSION["dataScadenza"] = $riga["dataScadenza"];
            }
        
        }
        else {
            die(mysqli_connect_error());
        }
        echo "Successo";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])&& isset($_POST["password"])) {
        gestisciPost();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Schermata di Login</title>
        <meta name="description" content="Pagina di Login">
        <?php
            require_once "head.php"
        ?>
        <link rel="stylesheet" href="../css/login.css">
        <script type="module" src="../js/valida_credenziali.js"></script>
        <script type="module" src="../js/login.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
    </head>
    <body>
        <?php
            require_once "back_to_index.php"
        ?>
        <form id="invio-credenziali">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="mariorossi@gmail.com" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••••" pattern="^[A-Z][A-Za-z0-9'\$+@]{4,16}$" required>
            <button type="submit" id="bottone-invio">Login</button>
        </form>
        <p id="messaggio"></p>
        <div id="iscrizione">
            <p>Non sei ancora iscritto?</p>
            <a href="iscrizione.php">Iscriviti</a>
        </div>
        <?php
            require_once "footer.php"
        ?>
    </body>
</html>
