//Usata in mostra_prenotazioni_utente.js, iscrizione.js e login.js
"use strict";
export async function gestisciRispostaJSON(risposta) {
    if (!risposta.ok) {
        throw new Error("HTTP error! Status: " + risposta.status);
    }
    const risultato = await risposta.json(); // Converto la risposta in JSON
    console.log(risultato); // Controllo della risposta
    return risultato;
}

export async function gestisciRispostaTestuale(risposta) {
    if (!risposta.ok) {
        throw new Error("HTTP error! Status: " + risposta.status);
    }
    const risultato = await risposta.text();
    console.log(risultato); // Risposta del server
    return risultato;
}
