<?php
    
    require_once "user_arleady_logged.php";
    require_once "login_signup_common_checks.php";

    function gestisciPost() {
        // Controllo l'email lato server
        $email = null;
        if (!controlla_email($email))
            return;
        // Controllo la password lato server
        $password = null;
        if (!controlla_password($password))
            return;
        // Controllo il nome lato server
        $nome = null;
        if (isset($_POST["nome"])) {
            $nome = $_POST["nome"];
            $regex_nome = "/^[A-Z][a-z]{2,20}$/";
            if (!preg_match($regex_nome, $nome)) {
                echo 'Nome non valido: ' . $nome;
                return;
            }
        }
        // Controllo il cognome lato server
        $cognome = null;
        if (isset($_POST["cognome"])) {
            $cognome = $_POST["cognome"];
            $regex_cognome = "/^[A-Z][a-z]{2,20}$/";
            if (!preg_match($regex_cognome, $cognome)) {
                echo 'Cognome non valido: ' . $cognome;
                return;
            }
        }
        // Provo a connettermi al db
        require_once "dbconnect.php";
        // Se arrivo qui, controllo se esiste già un'iscritto per quella mail.
        $sql = "SELECT * FROM iscritti WHERE email=?;";
        if ($statement = mysqli_prepare($connessione, $sql)) {
            mysqli_stmt_bind_param($statement, 's', $email);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            if(mysqli_num_rows($result)!==0) {
                echo 'Email già registrata';
                return;
            }
            // Non esiste alcun iscritto con quella mail.
            $sql = "INSERT INTO iscritti (email, password, nome, cognome, dataIscrizione, dataScadenza) VALUES (?, ?, ?, ?, ?, ?)";
            if($statement = mysqli_prepare($connessione, $sql)) {
                // Inserisco l'hash della password e gli altri dati sull'utente.
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $dataCorrente = date("Y-m-d"); // Formato compatibile con sql
                // Ogni volta che c'è un nuovo iscritto, gli si regala 3 giorni di abbonamento (la prova)
                // Quindi, regalo 3 giorni di abbonamento (compreso oggi, quindi altri 2 giorni oltre a oggi).
                $dataScadenza = new DateTime();
                $dataScadenza->modify('+2 days');
                $dataScadenza = $dataScadenza->format('Y-m-d');

                mysqli_stmt_bind_param($statement, 'ssssss', $email, $hash, $nome, $cognome, $dataCorrente, $dataScadenza);
                mysqli_stmt_execute($statement);
            }
            else {
                die(mysqli_connect_error());
            }
        }
        else {
            die(mysqli_connect_error());
        }
        
        echo 'Successo';
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        gestisciPost();
        exit();
    }

    require_once "post_credentials_handler.php";
?>
<!DOCTYPE html>
<html lang='it'>
    <head>
        <title>
            Iscrizione
        </title>
        <meta name="description" content="Registrazione di un nuova iscritto">
        <?php
            require_once "head.php"
        ?>
        <link rel="stylesheet" href="../css/iscrizione.css">
        <script type="module" src="../js/valida_credenziali.js"></script>
        <script type="module" src="../js/iscrizione.js"></script>
        <script type="module" src="../js/messaggio.js"></script>
        <script type="module" src="../js/gestisci_risposta.js"></script>
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
        ?>
        <form id="invio-credenziali">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="mariorossi@gmail.com" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" required>
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" placeholder="Mario" pattern="^[A-Z][a-z]{2,20}$" required>
            <label for="cognome">Cognome</label>
            <input type="text" id="cognome" name="cognome" placeholder="Mario" pattern="^[A-Z][a-z]{2,20}$" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••••" pattern="^[A-Z][A-Za-z0-9'\$+@]{4,16}$" required>
            <button type="submit" id="bottone-invio">Iscriviti</button>
        </form>
        <p id="messaggio"></p>
        <div id="login">
            <p>Sei gi&agrave; iscritto?</p>
            <a href="login.php">Esegui il login</a>
        </div>
        <?php
            require_once "footer.php";
        ?>
    </body>
</html>