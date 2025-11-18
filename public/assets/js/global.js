document.addEventListener('DOMContentLoaded', function() {
    initializeFooterToggle();
    initializeBackToTopButton();
    ensureTimeElementExists();

    if (window.location.pathname.includes('homepage')) {
        initializeTimer();
    } else {
        // S'assurer que le timer continue de fonctionner sur les autres pages
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
    if (footer) {
        footer.addEventListener('click', function() {
            footer.style.bottom = (footer.style.bottom === '-40vh') ? '0' : '-40vh';
        });
    }
}

// Fonction pour gérer le comportement du bouton "retour en haut"
function initializeBackToTopButton() {
    window.onscroll = function() { scrollFunction(); };

    function scrollFunction() {
        const button = document.getElementById("backToTopButton");
        if (button) {
            button.style.display = (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) ? "block" : "none";
        }
    }

    const backToTopButton = document.getElementById('backToTopButton');
    if (backToTopButton) {
        backToTopButton.addEventListener('click', function() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        });
    }
}

// Fonction pour initialiser et gérer le chronomètre
function initializeTimer() {
    let startTime = parseInt(localStorage.getItem('startTime')) || Date.now();
    localStorage.setItem('startTime', startTime);
    let display = document.querySelector('#time');
    if (display) {
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
    if (elapsed === 600) { // 10 minutes
        alert("Attention, il s'est écoulé 10 minutes");
    } else if (elapsed === 900) { // 15 minutes
        alert("Attention, il s'est écoulé 15 minutes");
    } else if (elapsed === 1200) { // 20 minutes
        alert("Attention, il s'est écoulé 20 minutes");
    }
}

// Event listener pour la déconnexion
function setupLogoutEvent() {
    const logoutButton = document.getElementById('logout');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            removeStartTime();
            window.location.href = '../logout';
        });
    }
}

// Event listener pour la page d'accueil
function setupHomepageEvent() {
    const homepageButton = document.getElementById('homepage');
    if (homepageButton) {
        homepageButton.addEventListener('click', function() {
            localStorage.setItem('startTime', Date.now());
            window.location.href = '../../templates/homepage';
        });
    }
}

// Assurer l'existence de l'élément #time
function ensureTimeElementExists() {
    if (!document.querySelector('#time')) {
        let timeElement = document.createElement('div');
        timeElement.id = 'time';
        document.body.appendChild(timeElement);
    }
}

// Fonction pour supprimer l'heure de début
function removeStartTime() {
    localStorage.removeItem('startTime');
    console.log("L'heure de début a été supprimée du localStorage.");
}