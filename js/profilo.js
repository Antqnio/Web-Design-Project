"use strict";
import { gestisciRispostaTestuale } from "./gestisci_risposta.js";
import {ROSSO, VERDE, mostraMessaggio} from "./messaggio.js";
import {controllaPassword} from './controlla_password.js'



async function inviaCambio(e) {
    e.preventDefault();
    const passwordVecchia = document.getElementById("password-vecchia").value;
    if (!controllaPassword(passwordVecchia)) {
        mostraMessaggio("Password vecchia non valida", ROSSO);
        return;
    }
    const passwordNuova = document.getElementById("password-nuova").value;
    if (!controllaPassword(passwordNuova)) {
        mostraMessaggio("Password nuova non valida", ROSSO);
        return;
    }
    try {
        const risposta = await fetch('profilo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `password_vecchia=${passwordVecchia}&password_nuova=${passwordNuova}`
        });
        const risultato = await gestisciRispostaTestuale(risposta);
        if (typeof(risultato) === "string") {
            if (risultato === "Successo") {
                mostraMessaggio("La sua password è stata cambiata con successo", VERDE);
                const contenitoreCambioPassword = document.getElementById("contenitore-cambio-password");
                contenitoreCambioPassword.style.display = "none";
                document.getElementById("password-vecchia").value = "";
                document.getElementById("password-nuova").value = "";
            }
            else if (risultato != '') {
                mostraMessaggio(risultato, ROSSO);
            }
        }
        else {
            mostraMessaggio("Errore indefinito", ROSSO);
        }  
    }
    catch(errore) {
        console.log(`Errore: ${errore.message}`);
    }
}

async function inviaCancellazione(e) {
    e.preventDefault();
    const password = document.getElementById("password").value;
    if (!controllaPassword(password)) {
        mostraMessaggio("Password non valida", ROSSO);
        return;
    }
    try {
        const risposta = await fetch('profilo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `password=${password}`
        });
        const risultato = await gestisciRispostaTestuale(risposta);
        if (risultato === "Successo") {
            // Termino la sessione del profilo cancellato.
            window.location.href = "../php/logout.php"
        }
        else if (typeof risultato === "string") {
            if (risultato != '')
                mostraMessaggio(risultato, ROSSO);
            else
                mostraMessaggio("Errore indefinito", ROSSO);
        }
    }
    catch(errore) {
        console.log(`Errore: ${errore.message}`);
    }

}





let primaChiamataCancellaProfilo = true;
function mostraSchermataCancellaProfilo() {
    const divCambio = document.getElementById("contenitore-cambio-password");
    const divCancella = document.getElementById("cancella");
    if (primaChiamataCancellaProfilo || divCancella.style.display === "none") {
        divCancella.style.display = "block";
        primaChiamataCancellaProfilo = false;
        divCambio.style.display = "none";
    }
    else {
        divCancella.style.display = "none";
    }
}

let primaChiamataCambiaPassword = true;
function mostraSchermataCambiaPassword() {
    const divCambio = document.getElementById("contenitore-cambio-password");
    const divCancella = document.getElementById("cancella");
    if (primaChiamataCambiaPassword || divCambio.style.display === "none") {
        divCambio.style.display = "block";
        primaChiamataCambiaPassword = false;
        divCancella.style.display = "none";
    }
    else {
        divCambio.style.display = "none";
    }
}


function init() {
    const bCambia = document.getElementById("cambia-password");
    bCambia.addEventListener("click", mostraSchermataCambiaPassword);
    const bCancella = document.getElementById("cancella-profilo");
    bCancella.addEventListener("click", mostraSchermataCancellaProfilo);
    const formPassword = document.getElementById("form-password");
    formPassword.addEventListener("submit", (e) => {
        if (!formPassword.checkValidity())
            e.preventDefault();
    });
    const formCancella = document.getElementById("form-cancella");
    formCancella.addEventListener("submit", (e) => {
        if (!formCancella.checkValidity())
            e.preventDefault();
    });
    const bInviaCancellazione = document.getElementById("invio-cancellazione");
    bInviaCancellazione.addEventListener("click", inviaCancellazione);
    const bInviaCambio = document.getElementById("invia-cambio-password");
    bInviaCambio.addEventListener("click", inviaCambio);

    const dataScadenza = document.getElementById("scadenza");
    if (dataScadenza) {
        const scadenza = new Date(dataScadenza.innerText);
        scadenza.setHours(0, 0, 0, 0);
        const oggi = new Date();
        oggi.setHours(0, 0, 0, 0);
        if (scadenza < oggi)
            dataScadenza.style.color = "red";
    }
}

document.addEventListener("DOMContentLoaded", init);