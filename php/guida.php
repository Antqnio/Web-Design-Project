<!DOCTYPE html>
<html lang='it'>
    <head>
        <title>
            Guida
        </title>
        <meta name="description" content="Guida al sito">
        <?php
            require_once "head.php"
        ?>
        <link rel="stylesheet" href="../css/guida.css">
    </head>
    <body>
        <?php
            require_once "back_to_index.php";
        ?>
        <h1>Guida al sito</h1>
        <section id="descrizione">
            <h2>Descrizione in breve</h2>
            <p>Il sito rappresenta un servizio di gestione di prenotazione per una palestra.<br>
            Ci sono due tipi di utente: gli utenti normali e l'amministratore.<br>
            Non risulta possibile aggiungere nuovi amministratori al sito sfruttando le funzionalità da esso offerte.
            </p>
        </section>
        <section id="utente">
            <h2>Utente</h2>
            <p>Affinché un utente possa usufruire del servizio, dovrà registrarsi nell'apposita pagina
            (cliccando sul bottone "Accedi", presente in alto a destra nell'indice della pagina).
            Gli utenti possono:</p>
            <ul>
                <li>Prenotare (al massimo) un fascia oraria per giornata (soltanto un allenamento al giorno risulta consentito).<br>
                    Non si può prenotare un orario passato.<br>
                    Si possono prenotare orari solo entro 6 giorni nel futuro (compreso il sesto giorno).<br>
                    Si possono prenotare fasce orarie il cui inizio è nel passato ma che non sono ancora terminati (per esempio, se sono 11:00,
                    posso prenotare, nella data odierna, la fascia oraria 10:00-12:00, perché non risulta ancora terminata).
                </li>
                <li>Cancellare una prenotazione riferita a un orario non ancora iniziato (per esempio, nella data odierna, un utente può, alle 11:00, cancellare la prenotazione
                    per la fascia oraria 12:00-14:00, ma non può cancellare quella per la fascia 10:00-12:00).</li>
                <li>Vedere le proprie prenotazioni (comprese quelle passate).</li>
                <li>Cambiare la propria password.</li>
                <li>Cancellare il proprio account.</li>
            </ul>
            <p>
                Quando un utente si registra, riceve 3 giorni abbonamento (compreso il giorno di iscrizione) come prova.<br>
                Un utente non può prenotare fasce orarie di date in cui il suo abbonamento risulta scaduto.
            </p>
            <p>Ci sono 6 utenti (leggere sotto per le credenziali). Ogni utente ha una prenotazione in data 29/03/2025.</p>
            <p>Per fare dei test sul mostrare prenotazioni passate, bastano quelle del 29/03/2025.</p>
            <p>
                Per testare le prenotazioni future, bisogna aggiungere sul momento delle prenotazioni (oppure, entro il 12/04/2025 alle 08:00 esclusa,
                si possono usare quelle per il 12/04/2025 nella fascia 08:00-10:00 di utente1@example.com e utente3@example.com).
            </p>
            <p>Gli abbonamenti di utente2@example.com, utente5@example.com e utente6@example.com risultano scaduti (quindi si usano per i test sugli abbonamenti scaduti).</p>
        </section>
        <section id="amministratore">
            <h2>Amministratore</h2>
            <p>Gli amministratori possono:</p>
            <ul>
                <li>Visualizzare le prenotazioni in una qualunque data presente o passata e cancellare quelle riferite a un orario non iniziato
                (analogo al caso degli utenti, soltato che lo posso fare per ogni utente, mentre un account utente può vedere e cancellare soltanto le proprie prenotazioni).
                </li>
                <li>Visualizzare gli iscritti alla palestra e, per ogni iscritto:
                    <ul>
                        <li>Visualizzarne le prenotazioni e cancellarne quelle riferite a un orario non iniziato.</li>
                        <li>Cambiarne la password.</li>
                        <li>Cancellarne l'account.</li>
                        <li>Rinnovarne o estendere l'abbonamento</li>
                    </ul>
                </li>
                <li>Cambiare la propria password.</li>
                <li>Cancellare il proprio account.</li>
            </ul>
        </section>
        <section id="dati-presenti">
            <h2>Dati gi&agrave; presenti:</h2>
                <ul>
                    <li>Account amministratore (email: "admin@example.com"; password: "Admin")</li>
                    <li>Sei account utenti:
                        <ol>
                            <li>email: "utente1@example.com"; password: "Utente1".</li>
                            <li>email: "utente2@example.com"; password: "Utente2".</li>
                            <li>email: "utente3@example.com"; password: "Utente3".</li>
                            <li>email: "utente4@example.com"; password: "Utente4".</li>
                            <li>email: "utente5@example.com"; password: "Utente5".</li>
                            <li>email: "utente6@example.com"; password: "Utente6".</li>
                        </ol>
                </ul>
                <p>Questi account permettono di vedere e provare tutte le funzionalità offerte dall'applicazione.</p>
        </section>
        <section id="fonti-immagini">
            <h2>Fonti immagini</h2>
            <ul>
                <li><a href="https://www.shareicon.net/admin-625836">adminlogo.png</a></li>
                <li><a href="https://icon-library.com/icon/username-icon-png-19.html">userlogo.png</a></li>
                <li><a href="https://www.cleanpng.com/png-fitness-centre-computer-icons-dumbbell-weight-trai-903421/">gym.png</a></li>
                <li><a href="https://www.cleanpng.com/png-fitness-centre-computer-icons-dumbbell-weight-trai-903421/">gym.ico</a></li>
                <li><a href="https://www.flaticon.com/free-icon/barbell_4744822">barbell.png</a></li>
            </ul>
        </section>
        <?php
            require_once "footer.php"
        ?>
    </body>