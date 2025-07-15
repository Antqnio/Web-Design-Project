"use strict";
import {gestisciRispostaJSON} from './gestisci_risposta.js';
import {gestisciEventualeMessaggio, gestisciRisultatoJSON, controllaData, gestisciMessaggioDiCancellazione} from './mostra_prenotazioni_lib.js';
const NESSUNA_PRENOTAZIONE = "Nessuna prenotazione effettuata";
const TUTTE_CARICATE = "Tutte le prenotazioni sono state caricate";


async function cancellaPrenotazione(e) {
    const bottone = e.target;
    const [id, _] = bottone.id.split("|");
    const td = document.getElementById(`${id}|data`);
    const data = td.innerText;
    console.log("data = " + data);
    if (!controllaData(data))
        return;
    try {
        const richiestaCaricamento = "cancella";
        const risposta = await fetch('mostra_prenotazioni_utente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiestaCaricamento}&data=${encodeURIComponent(data)}`
        });
        const risultato = await gestisciRispostaJSON(risposta);
        gestisciMessaggioDiCancellazione(risultato, id);
    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }

}

// Inviato al server per decidere l'OFFSET nella query.
export let idUltimaRiga = 0;

function incrementaIdUltimaRiga() {
    ++idUltimaRiga;
}




async function caricaRighe(righeCaricatePerVolta = 5) {
    const richiesta_caricamento = "carica";
    try {
        const risposta = await fetch('mostra_prenotazioni_utente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiesta_caricamento}&quante=${righeCaricatePerVolta}&offset=${idUltimaRiga}`
        });

        const risultato = await gestisciRispostaJSON(risposta);

        if (gestisciEventualeMessaggio(risultato, TUTTE_CARICATE, NESSUNA_PRENOTAZIONE)) {
            return;
        }
        gestisciRisultatoJSON(risultato, cancellaPrenotazione, incrementaIdUltimaRiga, TUTTE_CARICATE);
    } catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

function init() {
    const bCarica = document.getElementById("carica");
    bCarica.addEventListener("click", () => {
        // Uso questa lambda per chiamare la funzione
        caricaRighe();
    });
    // Carico subito delle prenotazioni e le mostro all'utente.
    caricaRighe();
}

document.addEventListener("DOMContentLoaded", init);