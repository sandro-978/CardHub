// Funzione asincrona per caricare e visualizzare gli annunci da API
async function loadListings(targetId) {
    // Seleziona l'elemento DOM dove inserire gli annunci
    const target = document.getElementById(targetId);
    if (!target) return;

    // Fetcha gli annunci dall'API
    const response = await fetch('/api/listings.php');
    const listings = await response.json();

    // Genera il codice HTML per ogni annuncio e lo inserisce nel DOM
    target.innerHTML = listings.map(function (listing) {
        return `
            <article class="listing-card" data-condition="${listing.condition}">
                <img src="${listing.image_url}" alt="${listing.card_name}">
                <div class="listing-card-body">
                    <h2 class="h5">${listing.card_name}</h2>
                    <p class="listing-meta">${listing.edition} · ${listing.language} · ${listing.condition}</p>
                    <strong>€ ${listing.price}</strong>
                </div>
            </article>
        `;
    }).join('');
}

// Funzione per filtrare gli annunci in base alla condizione selezionata
async function updateListings(filterId) {
    // Seleziona l'elemento filtro (select/dropdown)
    const filterElement = document.getElementById(filterId);
    if (!filterElement) return;

    // Ottieni la condizione selezionata dal filtro
    const condition = filterElement.value;
    // Seleziona tutte le carte degli annunci
    const cards = document.querySelectorAll('.listing-card');

    // Itera su tutte le carte e mostra/nascondi in base al filtro
    cards.forEach(function (card) {
        if (condition === 'all' || card.dataset.condition === condition) {
            // Mostra la carta se corrisponde al filtro
            card.style.display = '';
        } else {
            // Nascondi la carta se non corrisponde
            card.style.display = 'none';
        }
    });

}

async function countListings(currentUserId) {
    const response = await fetch(`/api/listings.php?user_id=${currentUserId}`);
    const listings = await response.json();
    return listings.length;
}

// Attendi il caricamento completo del DOM
document.addEventListener('DOMContentLoaded', function () {
    // Carica gli annunci nel contenitore specificato
    loadListings('listingsContainer');

    // Seleziona l'elemento del filtro
    const filterSelect = document.getElementById('conditionFilter');
    if (filterSelect) {
        // Aggiungi listener al cambio del filtro per aggiornare gli annunci visualizzati
        filterSelect.addEventListener('change', function () {
            updateListings('conditionFilter');
        });
    }
});

