"use strict";
import {ROSSO, VERDE, mostraMessaggio} from './messaggio.js';
import {gestisciRispostaTestuale} from './gestisci_risposta.js'


const FASCE_ORARIE = ["08:00-10:00", "10:00-12:00", "12:00-14:00", "14:00-16:00", "16:00-18:00", "18:00-20:00", "20:00-22:00"];



// data deve essere di tipo date.
function resettaOra(data) {
    data.setHours(0);
    data.setMinutes(0);
    data.setSeconds(0);
    data.setMilliseconds(0);
}


async function prenota(e) {
    const messaggio = document.getElementById("messaggio");
    const cellaCliccata = e.target;
    if (cellaCliccata.classList.contains("prenotato")) {
        mostraMessaggio("Hai già una prenotazione in questa data a quest'orario", ROSSO);
        return;
    }
    if (!cellaCliccata.classList.contains("disponibile")) { // Controllo lato client aggiuntivo.
        mostraMessaggio("Non puoi prenotare un orario senza posti disponbili", ROSSO);
    }
    

    const [ora, data] = cellaCliccata.id.split("|");
    console.log("ora = " + ora + ", data = " + data);
    // Controllo input lato client.
    const regexData = /^\d{4}-\d{2}-\d{2}$/;
    const messaggioDataNonValida = "Data non valida";
    if (!data.match(regexData)) {
        console.log(messaggioDataNonValida);
        console.log(data.match(regexData));
        return;
    }

    
    if (!FASCE_ORARIE.includes(ora)) {
        console.log("Ora non valida");
        return;
    }
    const dataInserita = new Date(data);
    resettaOra(dataInserita);
    const oggi = new Date();
    resettaOra(oggi);
    const limite = new Date(); // Data limite dell'inserimento;
    limite.setDate(oggi.getDate() + 6);
    if (dataInserita < oggi || dataInserita > limite) {
        console.log(messaggioDataNonValida);
        return;
    }
    try {
        const response = await fetch('prenota.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `data=${encodeURIComponent(data)}&ora=${encodeURIComponent(ora)}` // Serve per codificare una stringa in modo che possa essere usata in un URL senza problemi.
        });
        const result = await gestisciRispostaTestuale(response);
        if (result === "Prenotazione avvenuta con successo") {
            cellaCliccata.innerText = parseInt(cellaCliccata.innerText) - 1;
            cellaCliccata.classList.add("prenotato");
            if (cellaCliccata.innerText == 0) {
                // Aggiorno localmente la tabella
                cellaCliccata.classList.remove("disponibile");
                cellaCliccata.classList.add("non-disponibile");
            }
            mostraMessaggio(result, VERDE); // Verde per il successo.
        }
        else {
            mostraMessaggio(result, ROSSO); //Rosso per gli errori.
        } 
    } catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }

}

function init() {
    document.querySelectorAll('td.disponibile').forEach(td => {
        td.addEventListener('click', prenota);
    });
}

document.addEventListener("DOMContentLoaded", init);
