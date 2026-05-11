// Attendi il caricamento completo del DOM prima di eseguire il codice
document.addEventListener('DOMContentLoaded', function () {
    // Seleziona il bottone che apre/chiude il pannello dei filtri
    const toggleFilters = document.getElementById("filters");

    // Seleziona il pannello che contiene tutti i filtri
    const filterPanel = document.getElementById("filterPanel");

    // Se il bottone o il pannello non esistono, esci dalla funzione
    if (!toggleFilters || !filterPanel) return;

    // Aggiungi listener al click del bottone dei filtri
    toggleFilters.addEventListener("click", function () {
        // Aggiungi/rimuovi la classe 'open' per mostrare/nascondere il pannello
        filterPanel.classList.toggle("open");
    });
});