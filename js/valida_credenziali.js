// Usato in iscrizione.php, iscrizione.js, login.php, login.js (vista la loro similiarità) per il riuso del codice.
"use strict";
import { gestisciRispostaTestuale } from "./gestisci_risposta.js";

let mostraMessaggio;

export async function gestisciRispostaInvioCredenziali(risposta) {
    const risultato = await gestisciRispostaTestuale(risposta);
    if (typeof risultato === "string" && risultato != '')
        mostraMessaggio(risultato);
    else
        mostraMessaggio("Errore indefinito");
}


export function validaInput(e) {
    const form = e.target;
    const bInvio = document.getElementById("bottone-invio");
    if (!form.checkValidity()) {
        bInvio.disabled = true;
        e.preventDefault();
        console.log("Input non valido");
    }
    else {
        bInvio.disabled = false;
        console.log("Input valido");
    }
}

// Visto che i file login.js e iscrizione.js sono simili, uso questa funzione per inizializzare entrambi.
// In entrambi creo una init() a cui passo gli indirizzi delle funzioni inviaCredenziali e mostraMessaggio.
export function inizializza(funzioneInviaCredenziali, funzioneMostraMessaggio) {
    const bInvio = document.getElementById("bottone-invio");
    bInvio.addEventListener("click", funzioneInviaCredenziali);
    bInvio.disabled = true;
    const form = document.getElementById("invio-credenziali");
    form.addEventListener("input", validaInput);
    mostraMessaggio = funzioneMostraMessaggio;
}




