"use strict";
import {ROSSO, VERDE, DELAY as DELAY_ERRORE} from './messaggio.js';
import {inizializza, validaInput, gestisciRispostaInvioCredenziali} from './valida_credenziali.js';

const SUCCESSO = "Successo";
const DELAY_SUCCESSO = 1000;
const N_CICLI = 5;
function mostraMessaggio(contenuto) {
    const messaggio = document.getElementById("messaggio");
    messaggio.style.display = "block";
    let cicli = N_CICLI;
    if (contenuto === SUCCESSO) {
        messaggio.style.color = VERDE;
        messaggio.innerText = "Registrazione effettuata con successo.\n" +
                "Sarai reindirizzato alla pagina di login tra " + cicli + "s...";
        setInterval(() => {
            --cicli;
            if (cicli == 0)
                window.location.href = "../php/login.php";
            messaggio.innerText = "Registrazione effettuata con successo.\n" +
                "Sarai reindirizzato alla pagina di login tra " + cicli + "s...";
        }, DELAY_SUCCESSO);
    }
    else {
        messaggio.style.color = ROSSO;
        messaggio.innerText = contenuto;
        setTimeout(() => {
            messaggio.style.display = "none";
        }, DELAY_ERRORE);
    }
}

async function inviaCredenziali(e) {
    e.preventDefault(); // Evito invio di default
    validaInput(e); // Controllo lato client che le credenziali siano valide
    const email = document.getElementById("email").value.trim();
    const nome = document.getElementById("nome").value.trim();
    const cognome = document.getElementById("cognome").value.trim();
    const password = document.getElementById("password").value.trim();
    try {
        const risposta = await fetch('iscrizione.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `email=${encodeURIComponent(email)}&nome=${encodeURIComponent(nome)}&` +
            `cognome=${encodeURIComponent(cognome)}&password=${encodeURIComponent(password)}` // Serve per codificare una stringa in modo che possa essere usata in un URL senza problemi.
        });
        gestisciRispostaInvioCredenziali(risposta);
    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

function init() {
    // Chiamo la funzione generica di inizializzazione per login.js e iscrizione.js. Gli passo gli indirizzi delle funzioni
    // da usare nella inizializza.
    inizializza(inviaCredenziali, mostraMessaggio)
}


document.addEventListener("DOMContentLoaded", init);