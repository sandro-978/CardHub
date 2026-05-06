// Attendi il caricamento completo del DOM prima di eseguire il codice
document.addEventListener('DOMContentLoaded', function () {
    // Seleziona il dropdown dei filtri per la condizione
    const conditionFilter = document.getElementById('conditionFilter');
    // Seleziona tutti gli elementi con attributo data-condition (le carte)
    const cards = document.querySelectorAll('[data-condition]');

    // Se il filtro non esiste, esci dalla funzione
    if (!conditionFilter) return;

    // Aggiungi listener al cambio del valore del filtro
    conditionFilter.addEventListener('change', function () {
        // Ottieni il valore selezionato dal dropdown
        const selected = conditionFilter.value;

        // Itera su tutte le carte
        cards.forEach(function (card) {
            // Mostra la carta se il filtro è vuoto (tutti) oppure se corrisponde alla condizione
            const visible = selected === '' || card.dataset.condition === selected;
            // Aggiungi/rimuovi la classe 'd-none' (bootstrap) per nascondere/mostrare la carta
            card.classList.toggle('d-none', !visible);
        });
    });
});
