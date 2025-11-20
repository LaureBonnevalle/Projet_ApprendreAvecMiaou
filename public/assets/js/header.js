// ====================================
// GESTION DU MENU BURGER
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🍔 Header JS chargé');
    
    initializeBurgerMenu();
    highlightActiveLink();
});

function initializeBurgerMenu() {
    const burgerBtn = document.getElementById('openBtn');
    const nav = document.getElementById('nav');
    const cache = document.querySelector('.cache');
    
    if (!burgerBtn || !nav) {
        console.warn('⚠️ Bouton burger ou navigation introuvable');
        return;
    }

    console.log('✅ Menu burger initialisé');

    // Ouvrir/fermer le menu au clic sur le burger
    burgerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMenu();
    });

    // Fermer le menu au clic sur l'overlay
    if (cache) {
        cache.addEventListener('click', function() {
            closeMenu();
        });
    }

    // Fermer le menu au clic sur un lien
    const navLinks = nav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Petit délai pour voir le clic avant de fermer
            setTimeout(closeMenu, 200);
        });
    });

    // Fermer le menu avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && nav.classList.contains('open')) {
            closeMenu();
        }
    });

    // Fermer le menu si on redimensionne vers desktop
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (window.innerWidth >= 768 && nav.classList.contains('open')) {
                closeMenu();
            }
        }, 150);
    });
}

function toggleMenu() {
    const nav = document.getElementById('nav');
    const cache = document.querySelector('.cache');
    const burgerBtn = document.getElementById('openBtn');
    const body = document.body;

    const isOpen = nav.classList.contains('open');

    if (isOpen) {
        closeMenu();
    } else {
        openMenu();
    }

    console.log(`🍔 Menu ${isOpen ? 'fermé' : 'ouvert'}`);
}

function openMenu() {
    const nav = document.getElementById('nav');
    const cache = document.querySelector('.cache');
    const burgerBtn = document.getElementById('openBtn');
    const body = document.body;

    nav.classList.add('open');
    
    if (cache) {
        cache.classList.add('active');
    }

    if (burgerBtn) {
        burgerBtn.setAttribute('aria-expanded', 'true');
        burgerBtn.setAttribute('aria-label', 'Fermer le menu');
    }

    // Empêcher le scroll du body quand le menu est ouvert
    body.style.overflow = 'hidden';
}

function closeMenu() {
    const nav = document.getElementById('nav');
    const cache = document.querySelector('.cache');
    const burgerBtn = document.getElementById('openBtn');
    const body = document.body;

    nav.classList.remove('open');
    
    if (cache) {
        cache.classList.remove('active');
    }

    if (burgerBtn) {
        burgerBtn.setAttribute('aria-expanded', 'false');
        burgerBtn.setAttribute('aria-label', 'Ouvrir le menu');
    }

    // Réactiver le scroll du body
    body.style.overflow = '';
}

// ====================================
// GESTION DU SCROLL - HEADER STICKY
// ====================================

let lastScroll = 0;
const header = document.getElementById('header');

window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;

    // Optionnel : masquer le header au scroll vers le bas
    // Décommentez si vous voulez cet effet
    /*
    if (currentScroll > lastScroll && currentScroll > 100) {
        // Scroll vers le bas
        header.style.transform = 'translateY(-100%)';
    } else {
        // Scroll vers le haut
        header.style.transform = 'translateY(0)';
    }
    */

    lastScroll = currentScroll;
});

// ====================================
// HIGHLIGHT DU LIEN ACTIF
// ====================================

function highlightActiveLink() {
    const currentPath = window.location.search;
    const navLinks = document.querySelectorAll('#nav a');

    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        
        if (linkPath && currentPath.includes(linkPath.split('=')[1])) {
            link.classList.add('active');
            link.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        }
    });
}

// Appeler au chargement
document.addEventListener('DOMContentLoaded', highlightActiveLink);