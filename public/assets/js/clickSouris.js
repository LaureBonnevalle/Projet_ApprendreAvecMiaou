document.addEventListener('DOMContentLoaded', () => {
    // --- Configuration des Niveaux de Difficulté ---
    const difficultyLevels = {
        'facile': {
            key: 'facile',
            name: 'Facile (1 Coeur)',
            clickTolerance: 50,    // Grande zone de clic (facile à attraper)
            mouseVisibilityMs: 1500, // 1.5 seconde
            gameDurationSeconds: 60
        },
        'intermediaire': {
            key: 'intermediaire',
            name: 'Intermédiaire (2 Coeurs)',
            clickTolerance: 20,    // Zone de clic moyenne
            mouseVisibilityMs: 1000, // 1 seconde
            gameDurationSeconds: 60
        },
        'difficile': {
            key: 'difficile',
            name: 'Difficile (3 Coeurs)',
            clickTolerance: 5,     // Petite zone de clic (précision requise)
            mouseVisibilityMs: 700,  // 0.7 seconde
            gameDurationSeconds: 45 // Durée plus courte
        }
    };

    // --- Éléments du DOM ---
    const gameContainer = document.getElementById('game-container');
    const mouse = document.getElementById('mouse');
    const chatCursor = document.getElementById('chat-cursor');
    const scoreDisplay = document.getElementById('score');
    const timeDisplay = document.getElementById('time');
    const startButtons = document.querySelectorAll('.game-controls button[data-difficulty]');
    const resultMessage = document.getElementById('result-message');
   
    // Éléments d'affichage du meilleur score
    const bestScoreDisplays = {
        'facile': document.getElementById('best-score-facile'),
        'intermediaire': document.getElementById('best-score-intermediaire'),
        'difficile': document.getElementById('best-score-difficile'),
    };

    // --- Variables d'État du Jeu ---
    let currentDifficulty = difficultyLevels['facile']; // Niveau par défaut
    let score = 0;
    let timeLeft = currentDifficulty.gameDurationSeconds;
    let timerInterval = null;
    let gameRunning = false;
    let mouseTimeout = null;
    let isMouseHovered = false; // Indicateur de collision
   
    // Dimensions (fixées par le CSS)
    const mouseWidth = 50;
    const mouseHeight = 50;
   
    // ----------------------------------------------------
    // --- Gestion du Curseur Spécial (Chat) et Collision ---
    // ----------------------------------------------------

    /**
     * Calcule si le point touche la zone de clic élargie de la souris, en fonction de la tolérance du niveau actif.
     */
    function checkCollision(pointX, pointY) {
        if (mouse.style.display !== 'block') return false;
       
        // La clé du jeu : utiliser la tolérance du niveau ACTIF
        const tolerance = currentDifficulty.clickTolerance;
       
        const mouseObjLeft = parseFloat(mouse.style.left);
        const mouseObjTop = parseFloat(mouse.style.top);

        // Définition de la zone de clic (souris + tolérance)
        const clickZoneLeft = mouseObjLeft - tolerance;
        const clickZoneTop = mouseObjTop - tolerance;
        const clickZoneRight = mouseObjLeft + mouseWidth + tolerance;
        const clickZoneBottom = mouseObjTop + mouseHeight + tolerance;
       
        return (
            pointX >= clickZoneLeft &&
            pointX <= clickZoneRight &&
            pointY >= clickZoneTop &&
            pointY <= clickZoneBottom
        );
    }
   
    /**
     * Met à jour la position du chat-curseur et l'état de collision.
     */
    function updateCursorAndCollision(clientX, clientY) {
        const rect = gameContainer.getBoundingClientRect();
       
        // Coordonnées du curseur dans le conteneur du jeu
        const cursorX = clientX - rect.left;
        const cursorY = clientY - rect.top;

        // Limiter le chat à l'intérieur du conteneur pour éviter les artefacts
        // NOTE: le chatCursor est positionné par son centre (transform: translate(-50%, -50%))
        chatCursor.style.left = `${Math.min(Math.max(0, cursorX), rect.width)}px`;
        chatCursor.style.top = `${Math.min(Math.max(0, cursorY), rect.height)}px`;

        isMouseHovered = checkCollision(cursorX, cursorY);
    }

    /**
     * Traite l'événement de capture (clic/tap) si la souris est dans la zone de tolérance.
     */
    function handleCapture() {
        if (gameRunning && isMouseHovered && mouse.style.display === 'block') {
            score++;
            scoreDisplay.textContent = score;
            mouse.style.display = 'none';
            clearTimeout(mouseTimeout);
            isMouseHovered = false;
            spawnMouse();
        }
    }
   
    // --- Écouteurs pour le Curseur de Jeu ---
    gameContainer.addEventListener('mousemove', (e) => {
        if (!gameRunning) return;
        updateCursorAndCollision(e.clientX, e.clientY);
    });
   
    gameContainer.addEventListener('click', handleCapture);

    gameContainer.addEventListener('touchstart', (e) => {
        if (!gameRunning) return;
        e.preventDefault();
        updateCursorAndCollision(e.touches[0].clientX, e.touches[0].clientY);
        handleCapture();
    }, { passive: false });

    gameContainer.addEventListener('touchmove', (e) => {
        if (!gameRunning) return;
        e.preventDefault();
        updateCursorAndCollision(e.touches[0].clientX, e.touches[0].clientY);
    }, { passive: false });

    // ------------------------------------
    // --- Logique du Jeu et Contrôleurs ---
    // ------------------------------------

    /**
     * Calcule une position aléatoire pour la souris, en tenant compte de la tolérance de clic
     * pour que la zone de clic (souris + tolérance) reste dans les limites.
     */
    function getRandomPosition() {
        const containerWidth = gameContainer.offsetWidth;
        const containerHeight = gameContainer.offsetHeight;
       
        // L'espace doit laisser de la place pour la tolérance de chaque côté
        const tolerance = currentDifficulty.clickTolerance;
        const effectiveWidth = containerWidth - (mouseWidth + 2 * tolerance);
        const effectiveHeight = containerHeight - (mouseHeight + 2 * tolerance);

        // Assurez-vous que les positions X/Y aléatoires restent non négatives
        const randomX = Math.max(0, Math.floor(Math.random() * effectiveWidth)) + tolerance;
        const randomY = Math.max(0, Math.floor(Math.random() * effectiveHeight)) + tolerance;

        return { x: randomX, y: randomY };
    }

    /**
     * Fait apparaître la souris à une nouvelle position.
     */
    function spawnMouse() {
        if (!gameRunning) return;

        clearTimeout(mouseTimeout);

        const pos = getRandomPosition();
        mouse.style.left = `${pos.x}px`;
        mouse.style.top = `${pos.y}px`;
        mouse.style.display = 'block';
        isMouseHovered = false;

        // Utilisation du temps de visibilité du niveau actif
        mouseTimeout = setTimeout(() => {
            if (gameRunning) {
                mouse.style.display = 'none';
                spawnMouse();
            }
        }, currentDifficulty.mouseVisibilityMs);
    }

    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            timeDisplay.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                endGame();
            }
        }, 1000);
    }
   
    /**
     * Démarre la partie avec la difficulté sélectionnée.
     */
    function startGame() {
        if (gameRunning) return;
       
        // Réinitialisation des variables de jeu selon le niveau
        gameRunning = true;
        score = 0;
        timeLeft = currentDifficulty.gameDurationSeconds;
       
        scoreDisplay.textContent = score;
        timeDisplay.textContent = timeLeft;
        resultMessage.textContent = `Partie en cours : ${currentDifficulty.name}...`;
       
        // Désactiver les boutons de niveau pendant le jeu
        startButtons.forEach(btn => btn.disabled = true);
       
        startTimer();
        spawnMouse();
        chatCursor.style.display = 'block'; // Affiche le curseur de jeu
    }

    function endGame() {
        gameRunning = false;
        mouse.style.display = 'none';
        chatCursor.style.display = 'none';
        clearTimeout(mouseTimeout);
        clearInterval(timerInterval);

        resultMessage.textContent = `Partie terminée ! Votre score final en ${currentDifficulty.name} : ${score}.`;

        // Réactiver les boutons
        startButtons.forEach(btn => btn.disabled = false);

        // 🚨 Sauvegarde du score avec le niveau
        sendScoreToServer(score, currentDifficulty.key);
    }

    // ------------------------------------
    // --- Communication Serveur (Ajax) ---
    // ------------------------------------

    function sendScoreToServer(finalScore, levelKey) {
        fetch('/api/score/save', { // 🚨 Assurez-vous que cette route est correcte
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                score: finalScore,
                level: levelKey // Envoi du niveau au contrôleur
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultMessage.textContent += ` ${data.message}`;
                // Mise à jour de l'affichage du meilleur score si un nouveau est défini
                if (data.newBestScore !== undefined && data.level) {
                    const displayElement = bestScoreDisplays[data.level];
                    if (displayElement) {
                         displayElement.textContent = data.newBestScore;
                    }
                }
            } else {
                resultMessage.textContent += ` Erreur de sauvegarde : ${data.message}`;
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'envoi du score :', error);
            resultMessage.textContent += ' Erreur de communication avec le serveur.';
        });
    }

    // ------------------------------------
    // --- Initialisation et Écouteurs ---
    // ------------------------------------
   
    /**
     * Met à jour le niveau actif et démarre le jeu.
     */
    function selectAndStartGame(event) {
        const selectedDifficultyKey = event.currentTarget.dataset.difficulty;
       
        if (difficultyLevels[selectedDifficultyKey]) {
            // Mettre à jour la variable globale du niveau
            currentDifficulty = difficultyLevels[selectedDifficultyKey];
           
            // Mettre en évidence le bouton sélectionné
            startButtons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
           
            // Assurez-vous que le temps est bien affiché avant de démarrer
            timeDisplay.textContent = currentDifficulty.gameDurationSeconds;
           
            startGame();
        }
    }

    startButtons.forEach(button => {
        button.addEventListener('click', selectAndStartGame);
    });

    // Initialisation : on cache les éléments du jeu au chargement
    mouse.style.display = 'none';
    chatCursor.style.display = 'none';
   
    // Mettre en évidence le niveau Facile par défaut
    const defaultButton = document.querySelector('.game-controls button[data-difficulty="facile"]');
    if(defaultButton) {
        defaultButton.classList.add('active');
    }
});
