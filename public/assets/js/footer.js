// ====================================
// FOOTER & TIMER - Version corrigée avec gestion CSS des images
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Global JS - Footer initialisé');
    
    initializeFooterToggle();
    initializeBackToTopButton();
    ensureTimeElementExists();
    initializeModalCloseHandlers(); // ✅ Nouveau : Initialise les handlers de fermeture

    // Timer
    if (window.location.pathname.includes('homepage')) {
        initializeTimer();
    } else {
        let startTime = parseInt(localStorage.getItem('startTime')) || Date.now();
        let display = document.querySelector('#time');
        if (display) {
            startTimer(startTime, display);
        }
    }

    setupLogoutEvent();
    setupHomepageEvent();
});

// ====================================
// GESTION DU FOOTER
// ====================================

function initializeFooterToggle() {
    const footer = document.getElementById('footer');
    const footBox = document.querySelector('.foot-box');
    
    if (!footer || !footBox) {
        console.error('❌ Footer ou foot-box introuvable');
        return;
    }

    console.log('✅ Footer toggle initialisé');

    // Empêcher la propagation du clic sur le bouton retour haut
    const backToTopButton = document.getElementById('backToTopButton');
    if (backToTopButton) {
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔝 Clic bouton retour haut');
        });
    }

    // Toggle du footer au clic sur foot-box
    footBox.addEventListener('click', function(e) {
        // Ne pas toggle si clic sur le bouton
        if (e.target.closest('#backToTopButton')) {
            return;
        }
        
        const wasOpen = footer.classList.contains('open');
        footer.classList.toggle('open');
        
        console.log(`📦 Footer ${wasOpen ? 'fermé' : 'ouvert'}`);
    });

    // Effets hover
    footBox.addEventListener('mouseenter', function() {
        footBox.style.transition = 'transform 0.2s ease';
        footBox.style.transform = 'translateY(-2px)';
    });

    footBox.addEventListener('mouseleave', function() {
        footBox.style.transform = 'translateY(0)';
    });
}

// ====================================
// BOUTON RETOUR HAUT
// ====================================

function initializeBackToTopButton() {
    const button = document.getElementById("backToTopButton");
    
    if (!button) {
        console.warn('⚠️ Bouton retour haut introuvable');
        return;
    }

    console.log('✅ Bouton retour haut initialisé');

    // Fonction scroll
    function scrollFunction() {
        const scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
        button.style.opacity = scrollTop > 300 ? "1" : "0.6";
    }

    // Écouter le scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(scrollFunction, 50);
    });
    
    // Action au clic
    button.addEventListener('click', function(e) {
        console.log('⬆️ Scroll vers le haut');
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    scrollFunction();
}

// ====================================
// GESTION DE LA MODALE (AVEC IMAGES CSS)
// ====================================

// Récupérer les éléments du DOM de la modale
const modal = document.getElementById("customModal");
const modalTitle = document.getElementById("modal-title");
const modalMessage = document.getElementById("modal-message");
const modalActions = document.getElementById("modal-actions");
const modalIllustration = document.getElementById("modal-illustration"); // ✅ DIV pour classe CSS

// URL de redirection pour la pédagogie
const PEDAGOGIE_URL = "?route=notre-pedagogie-dangers-ecrans"; // ✅ Adaptez selon votre routing

// ✅ CLASSES CSS pour les illustrations (au lieu des URLs)
const ILLUSTRATION_CLASSES = {
    INQUIET: 'illustration-inquiet',   // 10 minutes
    STOP: 'illustration-stop',         // 15 minutes
    DANGER: 'illustration-danger'      // 20 minutes
};

// ====================================
// DONNÉES DES 3 ALERTES
// ====================================

const ALERTE_10_MIN_DATA = {
    title: "ATTENTION !",
    message: "Vous avez atteint la limite de temps d'écran recommandée pour les enfants de 3 ans.",
    illustrationClass: ILLUSTRATION_CLASSES.INQUIET, // ✅ Classe CSS
    buttons: [{ text: "OK", type: "close" }]
};

const ALERTE_15_MIN_DATA = {
    title: "ATTENTION !",
    message: "Vous avez dépassé la limite de temps d'écran recommandée pour les enfants de 3 ans.",
    illustrationClass: ILLUSTRATION_CLASSES.STOP, // ✅ Classe CSS
    buttons: [{ text: "OK", type: "close" }]
};

const ALERTE_20_MIN_DATA = {
    title: "ATTENTION !!",
    message: "Souhaitez-vous être informé sur les dangers de la surexposition aux écrans chez les enfants ?",
    illustrationClass: ILLUSTRATION_CLASSES.DANGER, // ✅ Classe CSS
    buttons: [
        { text: "Oui", type: "link", action: PEDAGOGIE_URL },
        { text: "Non", type: "close" }
    ]
};

// ====================================
// FONCTIONS DE GESTION DE LA MODALE
// ====================================

/**
 * Ferme la modale et réinitialise son état
 */
function closeModal() {
    if (!modal) return;
    
    modal.style.display = "none";
    modal.setAttribute('aria-hidden', 'true');
    
    // Désactive le piège de focus
    document.removeEventListener('keydown', handleFocusTrap);
    
    // ✅ Nettoie les classes CSS d'illustration
    if (modalIllustration) {
        modalIllustration.className = 'illustration-placeholder';
    }
    
    console.log('❌ Modale fermée');
}

/**
 * Initialise les gestionnaires d'événements pour fermer la modale
 */
function initializeModalCloseHandlers() {
    if (!modal) return;
    
    // Fermeture avec le bouton X
    const closeButton = modal.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
        
        // Accessibilité clavier pour le bouton X
        closeButton.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                closeModal();
            }
        });
    }
    
    // Fermeture en cliquant en dehors du contenu
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Fermeture avec la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
}

// Liste des sélecteurs pour les éléments focalisables
const FOCUSABLE_SELECTORS = 
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

/**
 * Gère le piège de focus (Focus Trap) pour l'accessibilité
 */
function handleFocusTrap(e) {
    if (modal.style.display !== "block" || e.key !== 'Tab') return;
   
    const focusableElements = modal.querySelectorAll(FOCUSABLE_SELECTORS);

    // Si aucun élément focalisable, bloquer la tabulation
    if (focusableElements.length === 0) {
        e.preventDefault();
        return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    // Shift + Tab : retour au dernier élément
    if (e.shiftKey && document.activeElement === firstElement) {
        lastElement.focus();
        e.preventDefault();
    }
    // Tab : retour au premier élément
    else if (!e.shiftKey && document.activeElement === lastElement) {
        firstElement.focus();
        e.preventDefault();
    }
}

/**
 * Ouvre une modale dynamique avec un contenu et des actions personnalisés
 * @param {string} title - Titre du bandeau
 * @param {string} message - Message principal
 * @param {string} illustrationClass - ✅ Classe CSS pour l'illustration (au lieu de l'URL)
 * @param {Array<Object>} buttons - Tableau des actions à afficher
 */
function openDynamicModal(title, message, illustrationClass, buttons) {
    if (!modal) {
        console.error('❌ Modale introuvable');
        return;
    }
   
    console.log(`📢 Ouverture modale: ${title}`);
    
    // 1. MISE À JOUR DU CONTENU
    if (modalTitle) {
        modalTitle.textContent = title;
    }
    
    if (modalMessage) {
        modalMessage.textContent = message;
    }

    // ✅ 2. MISE À JOUR DE L'ILLUSTRATION VIA CLASSE CSS
    if (modalIllustration) {
        // Réinitialise les classes
        modalIllustration.className = 'illustration-placeholder';
        
        // Ajoute la nouvelle classe d'illustration
        if (illustrationClass) {
            modalIllustration.classList.add(illustrationClass);
        }
        
        // Met à jour l'aria-label pour l'accessibilité
        modalIllustration.setAttribute('aria-label', title + ' - Illustration');
    }
   
    // 3. GESTION DES BOUTONS D'ACTION
    if (modalActions) {
        modalActions.innerHTML = '';
        
        buttons.forEach(button => {
            let buttonElement;
           
            // Le bouton "Oui" est un lien (type: 'link')
            if (button.type === 'link') {
                buttonElement = document.createElement('a');
                buttonElement.href = button.action;
                buttonElement.className = 'btn-tertiary'; // Vert pour l'action positive
                buttonElement.setAttribute('role', 'button');
                
                // Ferme la modale après le clic (optionnel)
                buttonElement.addEventListener('click', function(e) {
                    console.log('🔗 Redirection vers:', button.action);
                    // Laisse le lien se comporter normalement
                });
            } 
            // Boutons de fermeture (OK, Non)
            else {
                buttonElement = document.createElement('button');
                buttonElement.type = 'button';
                buttonElement.onclick = closeModal;
                buttonElement.className = 'btn-primary';
                
                // Style spécial pour "Non"
                if (button.text.toLowerCase() === 'non') {
                    buttonElement.classList.add('btn-secondary');
                }
            }
           
            buttonElement.textContent = button.text;
            modalActions.appendChild(buttonElement);
        });
    }

    // 4. OUVERTURE ET GESTION DU FOCUS A11Y
    modal.style.display = "block";
    modal.setAttribute('aria-hidden', 'false');
   
    // Active le piège de focus
    document.addEventListener('keydown', handleFocusTrap);

    // Donne le focus au premier élément interactif
    const closeButton = modal.querySelector('.close-button');
    if (closeButton) {
        // Petit délai pour assurer que la modale est bien affichée
        setTimeout(() => closeButton.focus(), 100);
    }
}

// ====================================
// TIMER ET ALERTES
// ====================================

function initializeTimer() {
    let startTime = parseInt(localStorage.getItem('startTime')) || Date.now();
    localStorage.setItem('startTime', startTime);
    let display = document.querySelector('#time');
    
    if (display) {
        console.log('✅ Timer démarré');
        startTimer(startTime, display);
    }
}

function startTimer(startTime, display) {
    setInterval(function() {
        let now = Date.now();
        let elapsed = Math.floor((now - startTime) / 1000);
        
        let hours = Math.floor(elapsed / 3600);
        let minutes = Math.floor((elapsed % 3600) / 60);
        let seconds = elapsed % 60;
        
        hours = hours < 10 ? "0" + hours : hours;
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        
        display.textContent = hours + ":" + minutes + ":" + seconds;
        
        checkAlerts(elapsed);
    }, 1000);
}

/**
 * Vérifie les seuils de temps et affiche les modales appropriées
 * @param {number} elapsed - Temps écoulé en secondes
 */
function checkAlerts(elapsed) {
    // 10 minutes (600 secondes)
    if (elapsed === 600) {
        console.log('⏰ 10 minutes écoulées');
        openDynamicModal(
            ALERTE_10_MIN_DATA.title,
            ALERTE_10_MIN_DATA.message,
            ALERTE_10_MIN_DATA.illustrationClass, // ✅ Classe CSS au lieu de l'URL
            ALERTE_10_MIN_DATA.buttons
        );
    }
    // 15 minutes (900 secondes)
    else if (elapsed === 900) {
        console.log('⏰ 15 minutes écoulées');
        openDynamicModal(
            ALERTE_15_MIN_DATA.title,
            ALERTE_15_MIN_DATA.message,
            ALERTE_15_MIN_DATA.illustrationClass, // ✅ Classe CSS au lieu de l'URL
            ALERTE_15_MIN_DATA.buttons
        );
    }
    // 20 minutes (1200 secondes)
    else if (elapsed === 1200) {
        console.log('⏰ 20 minutes écoulées');
        openDynamicModal(
            ALERTE_20_MIN_DATA.title,
            ALERTE_20_MIN_DATA.message,
            ALERTE_20_MIN_DATA.illustrationClass, // ✅ Classe CSS au lieu de l'URL
            ALERTE_20_MIN_DATA.buttons
        );
    }
}

// ====================================
// ÉVÉNEMENTS LOGOUT ET HOMEPAGE
// ====================================

function setupLogoutEvent() {
    const logoutButton = document.getElementById('logout');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            removeStartTime();
            window.location.href = '?route=logout'; // ✅ Adaptez selon votre routing
        });
    }
}

function setupHomepageEvent() {
    const homepageButton = document.getElementById('homepage');
    if (homepageButton) {
        homepageButton.addEventListener('click', function() {
            localStorage.setItem('startTime', Date.now());
            window.location.href = '?route=homepage'; // ✅ Adaptez selon votre routing
        });
    }
}

function ensureTimeElementExists() {
    if (!document.querySelector('#time')) {
        let timeElement = document.createElement('span');
        timeElement.id = 'time';
        timeElement.textContent = '00:00:00';
        const timerParagraph = document.querySelector('.timer p');
        if (timerParagraph) {
            timerParagraph.appendChild(timeElement);
        }
    }
}

function removeStartTime() {
    localStorage.removeItem('startTime');
    console.log("✅ StartTime supprimé");
}

// ====================================
// EXPOSITION POUR DEBUGGING (OPTIONNEL)
// ====================================
window.modalManager = {
    open: openDynamicModal,
    close: closeModal,
    test: function() {
        openDynamicModal(
            "Test Modal",
            "Ceci est une modale de test",
            ILLUSTRATION_CLASSES.INQUIET,
            [{ text: "OK", type: "close" }]
        );
    }
};