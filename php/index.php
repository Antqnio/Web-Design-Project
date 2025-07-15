<?php


    function mostraSchermataDefault() : void {
        echo "
            <a id='b-login' href='login.php'>Accedi</a>
            <section>
                <h2>Storia</h2>
                <p>
                    Nata nel 2025, siamo tra le palestre più giovani d'Italia.<br>
                    Proprio per questo, siamo sempre aggiornati riguardo alle pubblicazioni
                    più recenti della letteratura scientifica.<br>
                </p>
            </section>
            <section>
                <h2>Servizi</h2>
                <p>Da noi puoi trovare:</p>
                <ol>
                    <li>Calisthenics</li>
                    <li>Bodybuilding</li>
                    <li>Powerlifting</li>
                </ol>
                <p>
                    Abbiamo tutti attrezzi di ultima generazione e offriamo programmi di allenamento all'avanguardia.<br>
                    Per ogni nuovo iscritto, offriamo 3 giorni di prova (compreso il giorno di iscrizione).<br>
                    Cosa stai aspettando? Iscriviti anche tu!
                </p>
            </section>
            <p>Non sai come funziona?</p>
            <a href=\"guida.php\">Clicca qui per accedere alla guida</a>
            ";
    }

    function mostraSchermataAmministrazione() : void {
        echo "
            <div id='contenitore-menu-amministratore'>
                <img id='admin-logo' src='../img/adminlogo.png' alt='admin logo' onclick='mostraMenu()'>
                <div id='menu-amministratore'></div>
            </div>
            <div id='contenitore-bottoni'>
                <form action='mostra_iscritti.php' method='get'>
                    <button type='submit' id='bottone-mostra-iscritti'>Mostra gli iscritti</button>
                </form>
                <form action='mostra_prenotazioni_amministratore.php' method='get'>
                    <button type='submit' id='bottone-mostra-prenotazioni'>Mostra prenotazioni</button>
                </form>
            </div>
            <p>Ecco la <a href='guida.php'>guida</a> che mostri agli iscritti.</p>
            
        ";
    }
    function mostraSchermataUtente() : void {
        echo "
            <div id='contenitore-menu-utente'>
                <img id='user-logo' src='../img/userlogo.png' alt='user logo' onclick='mostraMenu()'>
                <div id='menu-utente'></div>
            </div>
            <div id='contenitore-bottoni'>
                <form action='prenota.php' method='get'>
                    <button type='submit' id='bottone-prenota'>Prenota un orario</button>
                </form>
                <form action='mostra_prenotazioni_utente.php' method='get'>
                    <button type='submit' id='bottone-mostra-prenotazioni'>Mostra prenotazioni</button>
                </form>
            </div>
            <p>Non ti ricordi qualcosa?</p>
            <a href='guida.php'>Clicca qui per accedere alla guida</a>
        ";
    }
?>
<!DOCTYPE html>
<html lang='it'>
    <head>
        <title>
            Platinium Gym - Indice
        </title>
        <meta name="description" content="Indice della palestra">
        <?php
            require_once "head.php"
        ?>
        <link rel="stylesheet" href="../css/index.css">
        <script src="../js/index.js"></script>
    </head>
    <body>
        <?php
            // Inserisco il logo "interattivo".
            require_once "back_to_index.php";

            // Controllo quale interfaccia mostrare
            session_start();
            if (!isset($_SESSION["admin"]))
                mostraSchermataDefault();
            else if(($_SESSION["admin"]))
                mostraSchermataAmministrazione();
            else
                mostraSchermataUtente();
                
            // Inserisco il footer
            require_once "footer.php";
        ?>
        
        
        
    </body>
        