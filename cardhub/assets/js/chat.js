// Seleziona gli elementi DOM necessari per la chat
const contenitoreMessaggi = document.getElementById("contenitore-messaggi");
const formMessaggio = document.getElementById("form-messaggio");
const inputMessaggio = document.getElementById("input-messaggio");

// Funzione per caricare i messaggi della chat corrente
function caricaMessaggi(){
    // Fetcha i messaggi dal server usando l'ID della chat
    fetch(`../api/messaggi.php?id_chat=${ID_CHAT}`)
        .then(risposta => risposta.json())
        .then(dati => {
            // Se la risposta non è riuscita, registra l'errore e termina
            if(!dati.successo){
                console.error(dati.errore)
                return;
            }

            // Pulisci il contenitore dei messaggi
            contenitoreMessaggi.innerHTML = "";
            // Itera su tutti i messaggi ricevuti
            dati.messaggi.forEach(messaggio => {
                
                const divMessaggio = document.createElement("div");

                // Assegna le classi CSS in base a chi ha scritto il messaggio
                if (Number(messaggio.user_id) === Number(ID_UTENTE_CORRENTE)){
                    divMessaggio.className = "messaggio messaggio-mio";
                }
                else{
                    divMessaggio.className = "messaggio messaggio-altro";
                }
                // Popola il messaggio con testo e data
                divMessaggio.innerHTML = `
                    <p> ${messaggio.testo} </p>
                    <small>${messaggio.created_at}</small>
                `;

                // Aggiungi il messaggio al contenitore
                contenitoreMessaggi.appendChild(divMessaggio);
            });
            // Scorri verso il basso per mostrare gli ultimi messaggi
            contenitoreMessaggi.scrollTop = contenitoreMessaggi.scrollHeight;
        })
        .catch(errore => {
            console.error("Errore nel caricamento dei messaggi", errore);
        });
}

// Listener per l'invio del messaggio tramite il form
formMessaggio.addEventListener("submit", function (evento){
    // Previeni il comportamento di default del form (reload della pagina)
    evento.preventDefault();
    // Ottieni il testo del messaggio e rimuovi gli spazi vuoti
    const testo = inputMessaggio.value.trim();
    // Se il messaggio è vuoto, non fare nulla
    if (testo === ""){return;}

    // Invia il messaggio al server tramite POST
    fetch("../api/messaggi.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_chat: ID_CHAT,
            testo: testo
        })
    })
    .then(risposta => risposta.json())
    .then(dati => {
        // Se l'invio non è riuscito, registra l'errore
        if(!dati.successo){
            console.error(dati.errore);
            return;
        }

        // Pulisci l'input del messaggio
        inputMessaggio.value = "";
        // Ricarica i messaggi per mostrare quello appena inviato
        caricaMessaggi();
    })

    .catch(errore => {
        console.error("Errore nell'invio del messaggio:", errore);
    });
});

// Carica i messaggi al primo caricamento della pagina
caricaMessaggi();
// Polling: ricarica i messaggi ogni 3 secondi per aggiornare la chat in tempo reale
setInterval(caricaMessaggi, 3000);
