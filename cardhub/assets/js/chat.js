const contenitoreMessaggi = document.getElementById("contenitore-messaggi");
const formMessaggio = document.getElementById("form-messaggio");
const inputMessaggio = document.getElementById("input-messaggio");

function caricaMessaggi(){
    fetch('../api/messaggi.php?id_chat=${ID_CHAT}')
        .then(risposta => risposta.json())
        .then(dati => {
            if(!dati.successo){
                console.error(dati.errore)
                return;
            }

            contenitoreMessaggi.innerHTML = "";
            dati.messaggi.forEach(messaggio => {
                
                const divMessaggio = document.createElement("div");

                if (Number(messaggio.user.id) === Number(ID_UTENTE_CORRENTE)){
                    divMessaggio.className = "messaggio messaggio-mio";
                }
                else{
                    divMessaggio.className = "messaggio messagio-altro";
                }

                contenitoreMessaggi.appendChild(divMessaggio);
            });
            contenitoreMessaggi.scrollTop = contenitoreMessaggi.scrollHeight;
        })
        .catch(errore => {
            console.error("Errore nel caricamento dei messaggi", errore);
        });
}

formMessaggio.addEventListener("submit", function (evento){
    evento.preventDefault();
    const testo = inputMessaggio.value.trim();
    if (testo === ""){return;}

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
        if(!dati.successo){
            console.error(dati.errore);
            return;
        }

        inputMessaggio.value = "";
        caricaMessaggi();
    })

    .catch(errore => {
        console.error("Errore nell'invio del messaggio:", errore);
    });
});

caricaMessaggi();
setInterval(caricaMessaggi, 3000);
