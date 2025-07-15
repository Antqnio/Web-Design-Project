"use strict";


let mostrato = false;

function rimuoviMenu() {
    const bProfilo = document.getElementById("vai-a-profilo");
    if (!bProfilo)
        return;
    bProfilo.remove();
    const bLogout = document.getElementById("logout");
    bLogout.remove();
    mostrato = false;
}


function init() {
    document.body.addEventListener("click", (event) => {
        if (event.target === document.body) {
            // Ho cliccato nella parte vuota del body. Lo uso per far sparire il menù a tendina.
            // Nascondo il menù quando perde il focus.
            console.log("Click sulla parte vuota del body");
            rimuoviMenu();
        }
        

    });
}




function mostraMenu() {
    if (mostrato) {
        rimuoviMenu();
        return;
    }
    console.log("In mostraMenu()");
    const table = document.createElement("table");
    // Creo vettoreStringhe per fare un ciclo, così da scrivere meno codice.
    const vettoreStringhe = [["vai-a-profilo", "logout"], ["Vai al tuo profilo", "Logout"], ["profilo.php", "logout.php"]];
    table.id = "tabella-menu";
    let div = document.getElementById("menu-utente");
    if (div == null) {
        // C'è il menu-amministratore
        div = document.getElementById("menu-amministratore");
    }
        

    div.appendChild(table);
    for (let i = 0; i < 2; ++i) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        table.appendChild(tr);
        tr.appendChild(td);
        td.id = vettoreStringhe[0][i];
        const a = document.createElement("a");
        td.appendChild(a);
        a.innerText = vettoreStringhe[1][i];
        a.href = vettoreStringhe[2][i];
    }
    mostrato = true;
}


document.addEventListener("DOMContentLoaded", init);