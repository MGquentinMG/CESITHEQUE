function openModal(id) {
    const modal = document.getElementById(`modal-${id}-list_user`);
    if (modal) {
        modal.classList.add('active'); // Ajoute la classe active pour afficher la modal
    }
}

function closeModal(id) {
    const modal = document.getElementById(`modal-${id}-list_user`);
    if (modal) {
        modal.classList.remove('active'); // Enlève la classe active pour cacher la modal
    }
}

// Fermer la modal si on clique en dehors
window.addEventListener('click', function (event) {
    const modals = document.querySelectorAll('.media-modal.list_user');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active'); // Ferme la modal si on clique en dehors
        }
    });
});


