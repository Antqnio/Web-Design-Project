"use strict";
import {ROSSO, DELAY as DELAY_ERRORE} from "./messaggio.js"
import {inizializza, validaInput, gestisciRispostaInvioCredenziali} from './valida_credenziali.js';

// In caso di errore mostra il messaggio, altrimenti reindirizza subito l'utente a index.php.
function mostraMessaggio(contenuto) {
    if (contenuto === "Successo") {
        window.location.href = "../php/index.php";
        return; // Garantisco che il codice successivo non venga esegutio
    }
    const messaggio = document.getElementById("messaggio");
    messaggio.style.display = "block";
    messaggio.style.color = ROSSO;
    messaggio.innerText = contenuto;
    setTimeout(() => {
        messaggio.style.display = "none";
    }, DELAY_ERRORE);
}


async function inviaCredenziali(e) {
    e.preventDefault(); // Evito invio di default
    validaInput(e); // Controllo lato client che le credenziali siano valide
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    try {
        const risposta = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}` // Serve per codificare una stringa in modo che possa essere usata in un URL senza problemi.
        });
        await gestisciRispostaInvioCredenziali(risposta);
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