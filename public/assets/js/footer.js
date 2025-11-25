// ====================================
// FOOTER & TIMER - Version avec sessionStorage
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Footer JS - Footer initialisé');
    
    initializeFooterToggle();
    initializeBackToTopButton();
    ensureTimeElementExists();
    initializeTimer();
    initializeModalCloseHandlers();

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
const modalIllustration = document.getElementById("modal-illustration");

// URL de redirection pour la pédagogie
const PEDAGOGIE_URL = "?route=notre-pedagogie-dangers-ecrans";

// ✅ CLASSES CSS pour les illustrations
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
    illustrationClass: ILLUSTRATION_CLASSES.INQUIET,
    buttons: [{ text: "OK", type: "close" }]
};

const ALERTE_15_MIN_DATA = {
    title: "ATTENTION !",
    message: "Vous avez dépassé la limite de temps d'écran recommandée pour les enfants de 3 ans.",
    illustrationClass: ILLUSTRATION_CLASSES.STOP,
    buttons: [{ text: "OK", type: "close" }]
};

const ALERTE_20_MIN_DATA = {
    title: "ATTENTION !!",
    message: "Souhaitez-vous être informé sur les dangers de la surexposition aux écrans chez les enfants ?",
    illustrationClass: ILLUSTRATION_CLASSES.DANGER,
    buttons: [
        { text: "Oui", type: "link", action: PEDAGOGIE_URL },
        { text: "Non", type: "close" }
    ]
};

// ====================================
// FONCTIONS DE GESTION DE LA MODALE
// ====================================

function closeModal() {
    if (!modal) return;
    
    modal.style.display = "none";
    modal.setAttribute('aria-hidden', 'true');
    
    document.removeEventListener('keydown', handleFocusTrap);
    
    if (modalIllustration) {
        modalIllustration.className = 'illustration-placeholder';
    }
    
    console.log('❌ Modale fermée');
}

function initializeModalCloseHandlers() {
    if (!modal) return;
    
    const closeButton = modal.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
        
        closeButton.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                closeModal();
            }
        });
    }
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
}

const FOCUSABLE_SELECTORS = 
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

function handleFocusTrap(e) {
    if (modal.style.display !== "block" || e.key !== 'Tab') return;
   
    const focusableElements = modal.querySelectorAll(FOCUSABLE_SELECTORS);

    if (focusableElements.length === 0) {
        e.preventDefault();
        return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (e.shiftKey && document.activeElement === firstElement) {
        lastElement.focus();
        e.preventDefault();
    }
    else if (!e.shiftKey && document.activeElement === lastElement) {
        firstElement.focus();
        e.preventDefault();
    }
}

function openDynamicModal(title, message, illustrationClass, buttons) {
    if (!modal) {
        console.error('❌ Modale introuvable');
        return;
    }
   
    console.log(`📢 Ouverture modale: ${title}`);
    
    if (modalTitle) {
        modalTitle.textContent = title;
    }
    
    if (modalMessage) {
        modalMessage.textContent = message;
    }

    if (modalIllustration) {
        modalIllustration.className = 'illustration-placeholder';
        
        if (illustrationClass) {
            modalIllustration.classList.add(illustrationClass);
        }
        
        modalIllustration.setAttribute('aria-label', title + ' - Illustration');
    }
   
    if (modalActions) {
        modalActions.innerHTML = '';
        
        buttons.forEach(button => {
            let buttonElement;
           
            if (button.type === 'link') {
                buttonElement = document.createElement('a');
                buttonElement.href = button.action;
                buttonElement.className = 'btn-tertiary';
                buttonElement.setAttribute('role', 'button');
                
                buttonElement.addEventListener('click', function(e) {
                    console.log('🔗 Redirection vers:', button.action);
                });
            } 
            else {
                buttonElement = document.createElement('button');
                buttonElement.type = 'button';
                buttonElement.onclick = closeModal;
                buttonElement.className = 'btn-primary';
                
                if (button.text.toLowerCase() === 'non') {
                    buttonElement.classList.add('btn-secondary');
                }
            }
           
            buttonElement.textContent = button.text;
            modalActions.appendChild(buttonElement);
        });
    }

    modal.style.display = "block";
    modal.setAttribute('aria-hidden', 'false');
   
    document.addEventListener('keydown', handleFocusTrap);

    const closeButton = modal.querySelector('.close-button');
    if (closeButton) {
        setTimeout(() => closeButton.focus(), 100);
    }
}

// ====================================
// TIMER ET ALERTES - VERSION SESSIONSTORAGE
// ====================================

let timerInterval = null;

function initializeTimer() {
    const isConnected = document.body.dataset.isConnected === 'true';
    const serverStartTime = document.body.dataset.startTime;
    
    console.log('⏱️ Init Timer:', { isConnected, serverStartTime });
    
    // Si pas connecté, arrêter le timer
    if (!isConnected || !serverStartTime) {
        stopTimer();
        sessionStorage.removeItem('session_start_time');
        sessionStorage.removeItem('alert_10min_shown');
        sessionStorage.removeItem('alert_15min_shown');
        sessionStorage.removeItem('alert_20min_shown');
        return;
    }
    
    // Récupérer ou initialiser le start_time
    let storedStartTime = sessionStorage.getItem('session_start_time');
    
    if (!storedStartTime || storedStartTime !== serverStartTime) {
        // Nouvelle session → réinitialiser
        sessionStorage.setItem('session_start_time', serverStartTime);
        sessionStorage.removeItem('alert_10min_shown');
        sessionStorage.removeItem('alert_15min_shown');
        sessionStorage.removeItem('alert_20min_shown');
        console.log('🆕 Nouvelle session - Timer initialisé');
    } else {
        console.log('▶️ Reprise de la session existante');
    }
    
    // Démarrer le timer
    let display = document.querySelector('#time');
    if (display) {
        console.log('✅ Timer démarré');
        startTimer(parseInt(serverStartTime), display);
    } else {
        console.error('❌ No #time element found');
    }
}

function startTimer(startTime, display) {
    // Nettoyer l'ancien interval si existe
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    timerInterval = setInterval(function() {
        let now = Math.floor(Date.now() / 1000);
        let elapsed = now - startTime;
        
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

function stopTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
        console.log('⏸️ Timer arrêté');
    }
    
    const display = document.querySelector('#time');
    if (display) {
        display.textContent = '00:00:00';
    }
}

function checkAlerts(elapsed) {
    // 10 minutes (600 secondes)
    if (elapsed >= 600 && elapsed < 602 && !sessionStorage.getItem('alert_10min_shown')) {
        console.log('⏰ 10 minutes écoulées');
        sessionStorage.setItem('alert_10min_shown', 'true');
        openDynamicModal(
            ALERTE_10_MIN_DATA.title,
            ALERTE_10_MIN_DATA.message,
            ALERTE_10_MIN_DATA.illustrationClass,
            ALERTE_10_MIN_DATA.buttons
        );
    }
    // 15 minutes (900 secondes)
    else if (elapsed >= 900 && elapsed < 902 && !sessionStorage.getItem('alert_15min_shown')) {
        console.log('⏰ 15 minutes écoulées');
        sessionStorage.setItem('alert_15min_shown', 'true');
        openDynamicModal(
            ALERTE_15_MIN_DATA.title,
            ALERTE_15_MIN_DATA.message,
            ALERTE_15_MIN_DATA.illustrationClass,
            ALERTE_15_MIN_DATA.buttons
        );
    }
    // 20 minutes (1200 secondes)
    else if (elapsed >= 1200 && elapsed < 1202 && !sessionStorage.getItem('alert_20min_shown')) {
        console.log('⏰ 20 minutes écoulées');
        sessionStorage.setItem('alert_20min_shown', 'true');
        openDynamicModal(
            ALERTE_20_MIN_DATA.title,
            ALERTE_20_MIN_DATA.message,
            ALERTE_20_MIN_DATA.illustrationClass,
            ALERTE_20_MIN_DATA.buttons
        );
    }
}

// ====================================
// ÉVÉNEMENTS LOGOUT ET HOMEPAGE
// ====================================

function setupLogoutEvent() {
    const logoutLink = document.querySelector('a[href*="logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', function() {
            console.log('🚪 Déconnexion - Arrêt du timer');
            stopTimer();
            sessionStorage.clear();
        });
    }
}

function setupHomepageEvent() {
    const homepageButton = document.getElementById('homepage');
    if (homepageButton) {
        homepageButton.addEventListener('click', function() {
            window.location.href = '?route=homepage';
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
    sessionStorage.removeItem('session_start_time');
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