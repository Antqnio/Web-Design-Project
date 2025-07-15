"use strict";

export const ROSSO = "#dc3545";
export const VERDE = "#28a745";
export const DELAY = 5000; // Dopo 5s, faccio sparire il messaggio.
export function mostraMessaggio(testo, colore = ROSSO, timer = true) {
    messaggio.style.color = colore;
    messaggio.innerText = testo;
    messaggio.style.display = "block";
    if (timer) {     
        setTimeout(() => {
            messaggio.style.display = "none";
        }, DELAY);
    }
}

