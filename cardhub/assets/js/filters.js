// Gestione pannelli marketplace: filtri avanzati + ordinamento
document.addEventListener('DOMContentLoaded', function () {
    const MAX_PRICE = 99999999.99;

    const toggleFilters = document.getElementById('filters');
    const filterPanel = document.getElementById('filterPanel');

    const toggleSort = document.getElementById('sortToggle');
    const sortPanel = document.getElementById('sortPanel');

    const priceMinInput = document.getElementById('price_min');
    const priceMaxInput = document.getElementById('price_max');

    if (toggleFilters && filterPanel) {
        toggleFilters.addEventListener('click', function () {
            filterPanel.classList.toggle('open');

            if (sortPanel) {
                sortPanel.classList.remove('open');
            }
        });
    }

    if (toggleSort && sortPanel) {
        toggleSort.addEventListener('click', function () {
            sortPanel.classList.toggle('open');

            if (filterPanel) {
                filterPanel.classList.remove('open');
            }
        });
    }

    // Se ci sono filtri reali nell'URL, apro solo il pannello filtri.
    // Non apro i filtri se nell'URL c'è solo sort.
    const params = new URLSearchParams(window.location.search);

    const hasFilterParams = [
        'q',
        'game',
        'edition',
        'language',
        'condition',
        'seller',
        'price_min',
        'price_max'
    ].some(function (paramName) {
        return params.get(paramName);
    });

    if (hasFilterParams && filterPanel) {
        filterPanel.classList.add('open');
    }

    // Se c'è un ordinamento diverso dal default, apro il menu ordina.
    const currentSort = params.get('sort');

    if (currentSort && currentSort !== 'newest' && sortPanel) {
        sortPanel.classList.add('open');
    }

    [priceMinInput, priceMaxInput].forEach(function (input) {
        if (!input) {
            return;
        }

        input.addEventListener('keydown', function (event) {
            if (['e', 'E', '+', '-'].includes(event.key)) {
                event.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            normalizePriceInput(input, MAX_PRICE);
            validatePriceRange(priceMinInput, priceMaxInput);
        });
    });

    if (filterPanel) {
        filterPanel.addEventListener('submit', function (event) {
            const isValid = validatePriceRange(priceMinInput, priceMaxInput);

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

function normalizePriceInput(input, maxPrice) {
    const value = input.value;

    if (value === '') {
        input.classList.remove('is-invalid');
        return;
    }

    const numericValue = Number(value);

    if (Number.isNaN(numericValue) || numericValue < 0 || numericValue > maxPrice) {
        input.classList.add('is-invalid');
        return;
    }

    input.classList.remove('is-invalid');
}

function validatePriceRange(priceMinInput, priceMaxInput) {
    if (!priceMinInput || !priceMaxInput) {
        return true;
    }

    const minValue = priceMinInput.value === '' ? null : Number(priceMinInput.value);
    const maxValue = priceMaxInput.value === '' ? null : Number(priceMaxInput.value);

    clearPriceRangeError(priceMinInput, priceMaxInput);

    if (
        minValue !== null &&
        maxValue !== null &&
        !Number.isNaN(minValue) &&
        !Number.isNaN(maxValue) &&
        minValue > maxValue
    ) {
        showPriceRangeError(priceMinInput, priceMaxInput);
        return false;
    }

    return true;
}

function showPriceRangeError(priceMinInput, priceMaxInput) {
    priceMinInput.classList.add('is-invalid');
    priceMaxInput.classList.add('is-invalid');

    let errorBox = document.getElementById('priceRangeError');

    if (!errorBox) {
        errorBox = document.createElement('div');
        errorBox.id = 'priceRangeError';
        errorBox.className = 'text-danger small mt-2';
        errorBox.textContent = 'Il prezzo minimo non può essere maggiore del prezzo massimo.';

        priceMaxInput.parentElement.appendChild(errorBox);
    }
}

function clearPriceRangeError(priceMinInput, priceMaxInput) {
    priceMinInput.classList.remove('is-invalid');
    priceMaxInput.classList.remove('is-invalid');

    const errorBox = document.getElementById('priceRangeError');

    if (errorBox) {
        errorBox.remove();
    }
}