async function loadListings(targetId) {
    const target = document.getElementById(targetId);
    if (!target) return;

    const response = await fetch('/api/listings.php');
    const listings = await response.json();

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
