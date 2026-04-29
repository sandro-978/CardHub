document.addEventListener('DOMContentLoaded', function () {
    const conditionFilter = document.getElementById('conditionFilter');
    const cards = document.querySelectorAll('[data-condition]');

    if (!conditionFilter) return;

    conditionFilter.addEventListener('change', function () {
        const selected = conditionFilter.value;

        cards.forEach(function (card) {
            const visible = selected === '' || card.dataset.condition === selected;
            card.classList.toggle('d-none', !visible);
        });
    });
});
