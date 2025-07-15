"use strict";
import {ROSSO, mostraMessaggio} from './messaggio.js';
import {gestisciRispostaJSON} from './gestisci_risposta.js';
import {gestisciEventualeMessaggio, gestisciRisultatoJSON, rimuoviBottoneCarica, controllaData, controllaEmail, gestisciMessaggioDiCancellazione} from './mostra_prenotazioni_lib.js';



// Inviato al server per decidere l'OFFSET nella query.
export let idUltimaRiga = 0;
let ultimaData;




async function cancellaPrenotazione(e) {
    const bottone = e.target;
    const [id, _] = bottone.id.split("|");
    const tdData = document.getElementById(`${id}|data`);
    const data = tdData.innerText;
    const tdEmail = document.getElementById(`${id}|email`);
    const email = tdEmail.innerText
    console.log("data = " + data + ", email = " + email);
    if (!controllaData(data) || !controllaEmail(email))
        return;
    try {
        const richiestaCaricamento = "cancella";
        const risposta = await fetch('mostra_prenotazioni_amministratore.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiestaCaricamento}&email=${encodeURIComponent(email)}&data=${encodeURIComponent(data)}`
        });
        const risultato = await gestisciRispostaJSON(risposta);
        gestisciMessaggioDiCancellazione(risultato, id);
    }
    catch(errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

function incrementaIdUltimaRiga() {
    ++idUltimaRiga;
}

let bottoneCaricaAbilitato = false;


const GIORNI = ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"];

async function caricaRighe(righeCaricatePerVolta = 5) {
    console.log("idUltimaRiga =", idUltimaRiga)
    const data = document.getElementById("data").value;
    console.log(`data = ${data}`);
    if (!controllaData(data)) {
        return;
    }
    const dataInput = new Date(data);
    if (GIORNI[dataInput.getDay()] === "Domenica") {
        mostraMessaggio("Non ci possono essere prenotazioni di domenica", ROSSO);
        return;
    }
    const bCarica = document.getElementById("carica");
    if (data == ultimaData && bCarica.style.display == "none") // Se il bottone è invisibile, ho già caricato tutte le prenotazioni del giorno
        return;
    if (data != ultimaData) {
        const tbody = document.getElementsByTagName("tbody")[0];
        // Svuoto la tabella (se non già vuota), che mostra le prenotazioni di un altro giorno.
        while (tbody.firstChild) {
            tbody.firstChild.remove();
        }
        idUltimaRiga = 0; // Cambia data, quindi riparto con le righe da 0.
        rimuoviBottoneCarica();
        bottoneCaricaAbilitato = false;
    }
    
    
    console.log("Data valida");
    ultimaData = data;
    const richiestaCaricamento = "carica";
    console.log(`richiestaCaricamento = ${richiestaCaricamento}, data=${data}, quante=${righeCaricatePerVolta}, offset=${idUltimaRiga}`)
    try {
        const risposta = await fetch('mostra_prenotazioni_amministratore.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiestaCaricamento}&quante=${righeCaricatePerVolta}&offset=${idUltimaRiga}` + 
            `&data=${data}`
        });
        const risultato = await gestisciRispostaJSON(risposta);
        // Controllo se il server ha inviato un messaggio (tutte le prenotazioni caricate o l'utente non ha mai prenotato).
        // Se ho un messaggio, non ho un array di record, quindi termino.
        const nessunaPrenotazione = `Non ci sono prenotazioni in data ${data}`;
        const tutteCaricate = `Tutte le prenotazioni del ${data} sono state caricate`;
        if (gestisciEventualeMessaggio(risultato, tutteCaricate, nessunaPrenotazione)) {
            bottoneCaricaAbilitato = false;
            return;
        }
        const mettiEmail = true;
        if (gestisciRisultatoJSON(risultato, cancellaPrenotazione, incrementaIdUltimaRiga, tutteCaricate, mettiEmail)) {
            // Se l'esito è buono, abilito il bottone per caricare ulteriori righe.
            if (!bottoneCaricaAbilitato) {
                const bCarica = document.getElementById("carica");
                bCarica.style.display = "block";
                bCarica.addEventListener("click", () => {
                    // Uso la lambda per sfruttare l'argomento di default della caricaRighe().
                    caricaRighe();
                });
                bottoneCaricaAbilitato = true;
            }
        };

    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}


function init() {
    const bMostra = document.getElementById("mostra-prenotazioni");
    bMostra.addEventListener("click", () => {
        // Uso la lambda per sfruttare l'argomento di default della caricaRighe().
        caricaRighe();
    });
}

document.addEventListener("DOMContentLoaded", init);