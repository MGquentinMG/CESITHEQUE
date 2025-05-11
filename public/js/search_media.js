function searchMedia() {
    const searchInput = document.getElementById('searchInput-list_user');
    const filter = searchInput.value.toLowerCase();
    const mediaCards = document.querySelectorAll('.media-card');

    mediaCards.forEach(function(card) {
        const title = card.getAttribute('data-title').toLowerCase();
        const description = card.querySelector('.media-card-description') ? card.querySelector('.media-card-description').innerText.toLowerCase() : '';
        const type = card.querySelector('.media-card-type') ? card.querySelector('.media-card-type').innerText.toLowerCase() : '';

        // Comparer la recherche avec le titre, la description et le type
        if (title.indexOf(filter) > -1 || description.indexOf(filter) > -1 || type.indexOf(filter) > -1) {
            card.style.display = '';  // Afficher si un des éléments correspond
        } else {
            card.style.display = 'none';  // Masquer sinon
        }
    });
}
