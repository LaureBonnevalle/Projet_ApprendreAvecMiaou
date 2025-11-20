// ====================================
// SECTION FOOTER - À remplacer dans votre global.js
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Global JS - Footer initialisé');
    
    initializeFooterToggle();
    initializeBackToTopButton();
    ensureTimeElementExists();

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

// Fonction pour basculer la visibilité du footer
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

// Fonction pour gérer le bouton "retour en haut"
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

// Timer
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

function checkAlerts(elapsed) {
    if (elapsed === 600) {
        alert("⏰ Attention, il s'est écoulé 10 minutes");
    } else if (elapsed === 900) {
        alert("⏰ Attention, il s'est écoulé 15 minutes");
    } else if (elapsed === 1200) {
        alert("⏰ Attention, il s'est écoulé 20 minutes");
    }
}

function setupLogoutEvent() {
    const logoutButton = document.getElementById('logout');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            removeStartTime();
            window.location.href = '../logout';
        });
    }
}

function setupHomepageEvent() {
    const homepageButton = document.getElementById('homepage');
    if (homepageButton) {
        homepageButton.addEventListener('click', function() {
            localStorage.setItem('startTime', Date.now());
            window.location.href = '../../templates/homepage';
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