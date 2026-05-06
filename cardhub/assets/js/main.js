// Attendi il caricamento completo del DOM prima di eseguire il codice
document.addEventListener('DOMContentLoaded', function () {
    // Seleziona tutti gli elementi alert che hanno l'attributo data-auto-hide
    const alerts = document.querySelectorAll('[data-auto-hide]');

    // Itera su ogni alert trovato
    alerts.forEach(function (alert) {
        // Imposta un timer che nasconterà l'alert dopo 3500 millisecondi (3.5 secondi)
        setTimeout(function () {
            // Aggiungi la classe Bootstrap 'd-none' per nascondere l'elemento
            alert.classList.add('d-none');
        }, 3500);
    });
});
