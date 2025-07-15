"use strict";
import {ROSSO, VERDE, mostraMessaggio} from './messaggio.js';


export function rimuoviBottoneCarica() {
    const bCarica = document.getElementById("carica");
    if (bCarica) // Controllo messo per la modalità amministratore.
        bCarica.style.display = "none";
}



export function aggiungiRecord(record, cancellaPrenotazione, incrementaIdUltimaRiga, mettiEmail = false) {
    const tbody = document.getElementsByTagName("tbody")[0];
    const tr = document.createElement("tr");
    tr.id = `${record.id}|riga`;
    tbody.appendChild(tr);

    const tdData = document.createElement("td");
    tdData.id = `${record.id}|data`;
    tdData.innerText = record.data;
    tr.appendChild(tdData);

    const tdOra = document.createElement("td");
    tdOra.id = `${record.id}|ora`;
    tdOra.innerText = record.ora;
    tr.appendChild(tdOra);

    if (mettiEmail) {
        const tdEmail = document.createElement("td");
        tdEmail.id = `${record.id}|email`;
        tdEmail.innerText = record.email;
        tr.appendChild(tdEmail);
    }

    const tdRimuovi = document.createElement("td"); // Lo creo a prescidere per lo stile e l'uniformità della tabella.
    tr.appendChild(tdRimuovi);
    if (Object.hasOwn(record, "rimuovibile") && record.rimuovibile == true) {
        const bRimuovi = document.createElement("button");
        bRimuovi.innerText = "X";
        bRimuovi.id = `${record.id}|rimuovi`;
        bRimuovi.addEventListener("click", cancellaPrenotazione);
        tdRimuovi.appendChild(bRimuovi);
    }
    else {
        tr.classList.add("non-cancellabile");
    }

    incrementaIdUltimaRiga();

}

export function controllaEmail(email) {
    const regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    if (regex.test(email))
        return true;
    return false;
}


export function gestisciEventualeMessaggio(risultato, tutteCaricate, nessunaPrenotazione) {
    if (typeof risultato === 'object' && risultato !== null && Object.hasOwn(risultato, "messaggio")) {
        // La risposta è un oggetto che contiene una proprietà con identificatore "messaggio".
        if (risultato.messaggio === "Nessun record") {
            const tbody = document.getElementsByTagName("tbody")[0];
            if (tbody.firstChild) {
                //Ho almeno un record
                mostraMessaggio(tutteCaricate, VERDE);
            }
            else {
                mostraMessaggio(nessunaPrenotazione, ROSSO, false);
            }
            rimuoviBottoneCarica();
            return true; // Esisteva un messaggio valido
        }
    }
    return false;
}


// Il ritorno serve solo in mostra_prenotazioni_amministratore.js.
// Se true, creo il bottone carica, altrimenti no.
export function gestisciRisultatoJSON(risultato, cancellaPrenotazione, incrementaIdUltimaRiga, tutteCaricate, mettiEmail = false, righeCaricatePerVolta = 5) {
    if (Array.isArray(risultato) && risultato.length > 0 && risultato.every(item => typeof item === 'object' && item !== null)) {
        // La risposta è un array di oggetti (contenente i record della query).
        console.log("Array ricevuto:", risultato);
        for (let record of risultato)
            aggiungiRecord(record, cancellaPrenotazione, incrementaIdUltimaRiga, mettiEmail);
        if (risultato.length < righeCaricatePerVolta) {
            mostraMessaggio(tutteCaricate, VERDE);
            rimuoviBottoneCarica();
            return false;
        }
    }
    return true;
}



// Controlla se la data è in un formato valido e non è oltre 6 giorni nel futuro.
export function controllaData(data) {
    const regexData = /^\d{4}-\d{2}-\d{2}$/;
    const messaggioDataNonValida = "Data non valida";
    if (!data.match(regexData)) {
        console.log(messaggioDataNonValida);
        return false;
    }
    const dataInput = new Date(data);
    dataInput.setHours(0, 0, 0, 0);
    const limite = new Date();
    limite.setDate(limite.getDate() + 6);
    limite.setHours(0, 0, 0, 0);
    return dataInput <= limite; // Restituisce false se la data è oltre 6 giorni nel futuro.
    
}

export function gestisciMessaggioDiCancellazione(risultato, id) {
    if (typeof risultato === 'object' && risultato !== null && Object.hasOwn(risultato, "messaggio")) {
        if (risultato.messaggio == "Successo") {
            // Rimuovo la riga corrispondente
            const tr = document.getElementById(`${id}|riga`);
            tr.remove();
            const tbody = document.getElementsByTagName("tbody")[0];
            if (!tbody.firstChild && (document.getElementById("carica") == null)) {
                // Non ho più prenotazioni. Se ho rimosso il bottone carica, significa che l'utente non ha più prenotazioni.
                mostraMessaggio(NESSUNA_PRENOTAZIONE, ROSSO, false);
            }
            else {
                mostraMessaggio("Prenotazione cancellata con successo", VERDE);
            }
        }
        else {
            mostraMessaggio("C'è stato un errore durante la cancellazione della sua prenotazione. Per favore, riprovi", ROSSO);
        }
    }
}