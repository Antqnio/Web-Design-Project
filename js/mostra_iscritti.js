"use strict";
import {gestisciRispostaJSON, gestisciRispostaTestuale} from './gestisci_risposta.js';
import { mostraMessaggio, ROSSO, VERDE} from './messaggio.js';
import {gestisciEventualeMessaggio, controllaEmail} from './mostra_prenotazioni_lib.js';
import {controllaPassword} from './controlla_password.js'

// Inviato al server per decidere l'OFFSET nella query.
let idUltimaRiga = 0;
const TUTTI_CARICATI = "Tutti gli iscritti sono stati caricati";
const NESSUN_ISCRITTO = "Non ci sono iscritti alla palestra"
const RINNOVO = "Rinnova abbonamento";
const ESTENSIONE = "Estendi abbonamento";


function ottieniEmail(e) {
    const bottone = e.target;
    const [id, _] = bottone.id.split("|");
    const td = document.getElementById(`${id}|email`);
    const email = td.innerText;
    return email;
}

function ottieniId(e) {
    return e.target.id.split("|")[0];
}

function controllaNomeCognome(indentificativo) {
    const regex = /^[A-Z][a-z]{2,20}$/;
    if (regex.test(indentificativo))
        return true;
    return false;
}

function stampaStringaDiErrore(stringa) {
    if (risultato != '')
        mostraMessaggio(risultato, ROSSO);
    else
        mostraMessaggio("Errore indefinito", ROSSO);
}

function dataNelPassato(dataScadenzaStringa) {
    const scadenza = new Date(dataScadenzaStringa);
    scadenza.setHours(0, 0, 0, 0);
    const oggi = new Date();
    oggi.setHours(0, 0, 0, 0);
    if (scadenza < oggi)
        return true;
    return false;
}

async function cambiaPassword(e) {
    const email = ottieniEmail(e);
    if (!controllaEmail(email)) {
        console.log(`email non valida: ${email}`);
        return;
    }
    let password = prompt(`Inserisci una nuova password per ${email}`, "");
    if (password == null) {
        // L'amministratore ha premuto "Annulla" nel prompt, quindi non stampo nessun messaggio di errore.
        return;
    }
    if (password == '' || !controllaPassword(password)) {
        mostraMessaggio("Password non valida", ROSSO);
        return;
    }
    try {
        const richiestaCaricamento = "cambia";
        const risposta = await fetch('mostra_iscritti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiestaCaricamento}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });
        const risultato = await gestisciRispostaTestuale(risposta);
        if (risultato === "Successo") {
            mostraMessaggio("Password cambiata con successo", VERDE);
        }
        else if (typeof risultato === "string") {
            stampaStringaDiErrore(risultato)
        }
    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

async function cancellaProfilo(e) {
    const email = ottieniEmail(e);
    if (!controllaEmail(email))
        return;
    let richiesta = confirm(`Sei davvero sicuro di voler cancellare il profilo di ${email}?`);
    if (!richiesta)
        return;
    try {
        const richiestaCaricamento = "cancella";
        const risposta = await fetch('mostra_iscritti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiestaCaricamento}&email=${encodeURIComponent(email)}`
        });
        const risultato = await gestisciRispostaTestuale(risposta);
        if (risultato === "Successo") {
            // Rimuovo la riga corrispondente
            const id = ottieniId(e);
            const tr = document.getElementById(`${id}|riga`);
            tr.remove();
            mostraMessaggio("Profilo eliminato con successo", VERDE);
        }
        else if (typeof risultato === "string") {
            stampaStringaDiErrore(risultato)
        }
    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}


let datiPerRinnovo = {};

async function rinnovaAbbonamento(e) {
    e.preventDefault();
    const form = document.getElementById("invio-rinnovo");
    if (!form.checkValidity()) {
        mostraMessaggio("Non puoi inserire valori negativi per gli abbonamenti");
        return;
    }
    const giorni = document.getElementById("giorni").value;
    const settimane = document.getElementById("settimane").value;
    const mesi = document.getElementById("mesi").value;
    const anni = document.getElementById("anni").value;
    if (giorni == 0 && settimane == 0 && mesi == 0 && anni == 0) {
        mostraMessaggio('Almeno uno tra "giorni", "settimane", "mesi" e "anni" deve essere maggiore di 0', ROSSO);
        return;
    }
    try {
        const richiesta_caricamento = "rinnova";
        const risposta = await fetch('mostra_iscritti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiesta_caricamento}&email=${datiPerRinnovo.emailRinnovo}&giorni=${giorni}&settimane=${settimane}&mesi=${mesi}&anni=${anni}`
        });
        const risultato = await gestisciRispostaJSON(risposta);
        if (risultato && typeof risultato === 'object') {
            if ('dataScadenza' in risultato) {
                // Cambio il testo del bottone rinnova a indicare che l'abbonamento non è più scaduto.
                // Tolgo il colore rosso alla data di scadenza e aggiorno la data di scadenza dell'utente.
                
                // Se è scaduto, faccio partire il rinnovo dal giorno corrente.
                // Se non è scaduto, aumento la durata dell'abbonamento partendo dalla data odierna.
                // In entrambi i casi, la cosa è gestita dal server (per problemi di compatibilità tra MySQL e JavaScript nella gestione delle date).
                datiPerRinnovo.scadenzaAbbonamento.innerText = risultato.dataScadenza;
                if (datiPerRinnovo.scaduto) {
                    datiPerRinnovo.scadenzaAbbonamento.style.color = "";
                    datiPerRinnovo.bottoneRinnovo.innerText = ESTENSIONE;
                    datiPerRinnovo.scaduto = false;
                    mostraMessaggio("Abbonamento rinnovato con successo", VERDE)
                }
                else {
                    mostraMessaggio("Abbonamento esteso con successo", VERDE);
                }
            }
            else if ('messaggio' in risultato) {
                mostraMessaggio(risultato.messaggio)
            }
            else {
                throw new Error("Errore generico.");
            }
            
        }
    }
    catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

function mostraFormRinnovo(e) {
    const form = document.getElementById("rinnova")
    if (form.style.display == "none") {
        const id = ottieniId(e)
        // Salvo l'email, il bottone per il rinnovo e il td dove inserisco la data di scadenza dell'abbonamento
        // in un oggetto variabile globalie, così da poterle modificare in rinnovaAbbonamento.
        const dataScadenza = document.getElementById(`${id}|data-scadenza`).innerText;
        let scaduto = false;
        if (dataNelPassato(dataScadenza)) {
            scaduto = true;
        }
        datiPerRinnovo = {
            emailRinnovo : ottieniEmail(e),
            scadenzaAbbonamento : document.getElementById(`${id}|data-scadenza`),
            bottoneRinnovo : document.getElementById(`${id}|rinnova`),
            scaduto : scaduto
        }
        form.style.display = "block";
    }
    else {
        form.style.display = "none";
    }
}


// Uso un form per potere usare target="_blank"
function mostraPrenotazioni(e) {
    // Controlli messi per evitare che l'utente (anche se amministratore) eviti di fare una richiesta se
    // manualmente ha modificato l'html.
    const email = ottieniEmail(e);
    if (!controllaEmail(email))
        return;
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../php/mostra_prenotazioni_utente.php";
    form.target = "_blank";

    const id = ottieniId(e);
    if (id <= 0) {
        mostraMessaggio(`id = ${id} non valido`);
        return;
    }
    const nome = document.getElementById(`${id}|nome`).innerText;
    if (!controllaNomeCognome(nome)) {
        mostraMessaggio(`Nome = ${nome} non valido`);
        return;
    }
    const cognome = document.getElementById(`${id}|cognome`).innerText;
    if (!controllaNomeCognome(cognome)) {
        mostraMessaggio(`Cognome = ${cognome} non valido`);
        return;
    }

    const campi = {
        nome: nome,
        cognome: cognome,
        email: email
    };

    for (let chiave in campi) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = chiave;
        input.value = campi[chiave];
        form.appendChild(input);
    }

    document.body.appendChild(form); // Per essere inviato, il form deve fare parte del DOM.
    form.submit();
    document.body.removeChild(form); // Pulizia dopo l'invio

}



function aggiungiRecord(record) {
    const tbody = document.getElementsByTagName("tbody")[0];
    const tr = document.createElement("tr");
    tr.id = `${record.id}|riga`;
    tbody.appendChild(tr);

    const tdEmail = document.createElement("td");
    tdEmail.id = `${record.id}|email`;
    tdEmail.innerText = record.email;
    tr.appendChild(tdEmail);

    const tdNome = document.createElement("td");
    tdNome.id = `${record.id}|nome`;
    tdNome.innerText = record.nome;
    tr.appendChild(tdNome);
    
    const tdCognome = document.createElement("td");
    tdCognome.id = `${record.id}|cognome`;
    tdCognome.innerText = record.cognome;
    tr.appendChild(tdCognome);

    const tdDataIscrizione = document.createElement("td");
    tdDataIscrizione.id = `${record.id}|data-iscrizione`;
    tdDataIscrizione.innerText = record.dataIscrizione;
    tr.appendChild(tdDataIscrizione);

    const tdDataScadenza = document.createElement("td");
    tdDataScadenza.id = `${record.id}|data-scadenza`;
    tdDataScadenza.innerText = record.dataScadenza;
    tr.appendChild(tdDataScadenza);
    let scaduto = false
    if (dataNelPassato(record.dataScadenza)) {
        scaduto = true;
        tdDataScadenza.style.color = ROSSO;
    }

    const tdMostraPrenotazioni = document.createElement("td");
    tr.appendChild(tdMostraPrenotazioni);
    const bMostraPrenotazioni = document.createElement("button");
    bMostraPrenotazioni.innerText = "Mostra prenotazioni";
    bMostraPrenotazioni.id = `${record.id}|mostra`;
    bMostraPrenotazioni.classList.add("bottone-mostra");
    bMostraPrenotazioni.addEventListener("click", mostraPrenotazioni);
    tdMostraPrenotazioni.appendChild(bMostraPrenotazioni);

    const tdCambiaPassword = document.createElement("td"); // Lo creo a prescidere per lo stile e l'uniformità della tabella.
    tr.appendChild(tdCambiaPassword);
    const bCambiaPassword = document.createElement("button");
    bCambiaPassword.innerText = "Cambia password";
    bCambiaPassword.id = `${record.id}|cambia`;
    bCambiaPassword.classList.add("bottone-cambia");
    bCambiaPassword.addEventListener("click", cambiaPassword);
    tdCambiaPassword.appendChild(bCambiaPassword);

    
    const tdCancella = document.createElement("td"); // Lo creo a prescidere per lo stile e l'uniformità della tabella.
    tr.appendChild(tdCancella);
    const bCancella = document.createElement("button");
    bCancella.innerText = "Cancella profilo";
    bCancella.id = `${record.id}|cancella`;
    bCancella.classList.add("bottone-cancella");
    bCancella.addEventListener("click", cancellaProfilo);
    tdCancella.appendChild(bCancella);

    const tdRinnova = document.createElement("td"); // Lo creo a prescidere per lo stile e l'uniformità della tabella.
    tr.appendChild(tdRinnova);
    const bRinnova = document.createElement("button");
    if (scaduto)
        bRinnova.innerText = RINNOVO;
    else
        bRinnova.innerText = ESTENSIONE;
    bRinnova.id = `${record.id}|rinnova`;
    bRinnova.classList.add("bottone-rinnova");
    bRinnova.addEventListener("click", mostraFormRinnovo);
    tdRinnova.appendChild(bRinnova); 

    ++idUltimaRiga;


}

async function caricaRighe(righeCaricatePerVolta = 5) {
    const richiesta_caricamento = "carica";
    console.log(`richiesta_caricamento=${richiesta_caricamento} quante=${righeCaricatePerVolta} offset=${idUltimaRiga}`)
    try {
        const risposta = await fetch('mostra_iscritti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `richiesta_caricamento=${richiesta_caricamento}&quante=${righeCaricatePerVolta}&offset=${idUltimaRiga}`
        });
        const risultato = await gestisciRispostaJSON(risposta);
        if (gestisciEventualeMessaggio(risultato, TUTTI_CARICATI, NESSUN_ISCRITTO)) {
                return;
        }
        if (Array.isArray(risultato) && risultato.length > 0 && risultato.every(item => typeof item === 'object' && item !== null)) {
            // La risposta è un array di oggetti (contenente i record della query).
            console.log("Array ricevuto:", risultato);
            risultato.forEach((record) => {
                aggiungiRecord(record);
            })
            if (risultato.length < righeCaricatePerVolta) {
                mostraMessaggio(TUTTI_CARICATI, VERDE);
                const bCarica = document.getElementById("carica");
                bCarica.remove();
            }
        }

    } catch (errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

function nascondiFormRinnovo() {
    const divRinnova = document.getElementById("rinnova");
    divRinnova.style.display = "none";
}

function init() {
    const bCarica = document.getElementById("carica");
    bCarica.addEventListener("click", () => {
        // Uso questa lambda per chiamare la funzione
        caricaRighe();
    });
    // Carico subito degli iscritti
    caricaRighe();
    const form = document.getElementById("invio-rinnovo");
    form.addEventListener("submit", rinnovaAbbonamento)
}

document.addEventListener("DOMContentLoaded", init);