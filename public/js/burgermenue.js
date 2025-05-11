function toggleBurgerMenu() {
    const menu = document.getElementById('burger-dropdown');
    menu.classList.toggle('open');
}

// Ferme le menu si on clique à l'extérieur
document.addEventListener('click', function (e) {
    const burger = document.querySelector('.burger-menu');
    const dropdown = document.getElementById('burger-dropdown');

    if (!burger.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});