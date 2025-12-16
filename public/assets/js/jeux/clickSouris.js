/**
 * Jeu Click Souris - Version finale 
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // ========== CONFIGURATION DES NIVEAUX ==========
    const difficultyLevels = {
        'facile': {
            key: 'facile',
            name: 'Facile',
            mouseSize: 80,
            chatSize:120,
            mouseVisibilityMs: 1500,
            hitboxTolerance: 20,
            gameDurationSeconds: 60
        },
        'intermediaire': {
            key: 'intermediaire',
            name: 'Intermédiaire',
            mouseSize: 60,
            chatSize:100,
            mouseVisibilityMs: 1000,
            hitboxTolerance: 10,
            gameDurationSeconds: 60
        },
        'difficile': {
            key: 'difficile',
            name: 'Difficile',
            mouseSize: 45,
            chatSize:100,
            mouseVisibilityMs: 700,
            hitboxTolerance: 5,
            gameDurationSeconds: 60
        }
    };

    // ========== ÉLÉMENTS DOM ==========
    const gameContainer = document.getElementById('game-container');
    const mouse = document.getElementById('mouse');
    const chatCursor = document.getElementById('chat-cursor');
    const scoreDisplay = document.getElementById('score');
    const gameTimeDisplay = document.getElementById('game-time');
    const resultMessage = document.getElementById('result-message');
    const startButton = document.getElementById('start-game');
    
    // Boutons de niveau
    const levelButtons = document.querySelectorAll('.level-buttons-click');
    
    // Boutons audio
    const muteBtn = document.getElementById('mute');
    const volumeBtn = document.getElementById('volume');
   
    const bestScoreDisplays = {
        'facile': document.getElementById('best-score-facile'),
        'intermediaire': document.getElementById('best-score-intermediaire'),
        'difficile': document.getElementById('best-score-difficile')
    };

    // ========== AUDIO ==========
    const baseSnd = "assets/sounds/game/clickSouris/";
    const bgMusic = new Audio(`${baseSnd}backgroundMusicClick.wav`);
    const clickSound = new Audio(`${baseSnd}petitDing.mp3`);
    const winSound = new Audio(`${baseSnd}endgame.mp3`);

    bgMusic.loop = true;
    bgMusic.volume = 0.4;
    clickSound.volume = 1;
    winSound.volume = 1;

    let audioUnlocked = false;
    let isMuted = false;

    // Unlock audio au premier clic
    document.addEventListener('click', () => {
        if (!audioUnlocked) {
            bgMusic.play().then(() => {
                console.log('Audio débloqué!');
            }).catch(() => {});
            audioUnlocked = true;
        }
    }, { once: true });

    // ========== VARIABLES D'ÉTAT ==========
    let currentDifficulty = difficultyLevels['facile'];
    let score = 0;
    let timeLeft = 60;
    let gameTimerInterval = null;
    let gameRunning = false;
    let mouseTimeout = null;
    let mousePosition = { x: 0, y: 0 };
    let cursorPosition = { x: 0, y: 0 };

    // ========== PLUIE D'ÉTOILES ==========
    function starConfetti() {
        const count = 50;
        for (let i = 0; i < count; i++) {
            const star = document.createElement('div');
            star.className = 'star-confetti';
            star.style.left = Math.random() * 100 + '%';
            star.style.animationDuration = 2 + Math.random() * 2 + 's';
            star.style.animationDelay = Math.random() * 0.5 + 's';
            document.body.appendChild(star);
            setTimeout(() => star.remove(), 5000);
        }
    }

    // ========== OVERLAY DE VICTOIRE ==========
    function showVictoryOverlay(isNewRecord) {
        const overlay = document.createElement('div');
        overlay.className = 'victory-overlay';
        overlay.innerHTML = `
            <div class="victory-card">
                <h2>🎉 Partie terminée ! 🎉</h2>
                ${isNewRecord ? '<div class="trophy">🏆</div>' : ''}
                <p class="level-name">Niveau ${currentDifficulty.name}</p>
                <div class="final-score">
                    <p>Score final</p>
                    <span>${score}</span>
                </div>
                ${isNewRecord ? '<p class="record-text">✨ Nouveau record ! ✨</p>' : ''}
                <p class="countdown-text">Retour dans <span id="victory-countdown">8</span>s...</p>
            </div>
        `;
        
        setTimeout(() => {
      document.body.appendChild(overlay);

        let countdown = 8;
        const countdownEl = document.getElementById('victory-countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownEl) countdownEl.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                overlay.remove();
                resetGame();
            }
        }, 1000);
    }, 3000);
    }

    function showDefeatOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'victory-overlay';
    overlay.innerHTML = `
        <div class="victory-card">
            <h2>🐭 Belle partie de chasse !</h2>
            <p class="level-name">Niveau ${currentDifficulty.name}</p>
            <div class="final-score">
                <p>Score final</p>
                <span>${score}</span>
            </div>
            <p class="record-text">Mais tu peux faire mieux !</p>
            <p class="countdown-text">Retour dans <span id="victory-countdown">8</span>s...</p>
        </div>
    `;

    document.body.appendChild(overlay);

    let countdown = 8;
    const countdownEl = document.getElementById('victory-countdown');
    const countdownInterval = setInterval(() => {
        countdown--;
        if (countdownEl) countdownEl.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            overlay.remove();
            resetGame();
        }
    }, 1000);
}


    // ========== GESTION DU CURSEUR CHAT ==========
    function updateChatCursor(e) {
        if (!gameRunning) return;
        
        const rect = gameContainer.getBoundingClientRect();
        cursorPosition.x = e.clientX - rect.left;
        cursorPosition.y = e.clientY - rect.top;

        chatCursor.style.left = `${cursorPosition.x}px`;
        chatCursor.style.top = `${cursorPosition.y}px`;
    }

    gameContainer?.addEventListener('mousemove', updateChatCursor);

    // ========== DÉTECTION DE COLLISION ==========
    function checkCollision() {
        const tolerance = currentDifficulty.hitboxTolerance;
        const mouseSize = currentDifficulty.mouseSize;

        const mouseLeft = mousePosition.x - tolerance;
        const mouseRight = mousePosition.x + mouseSize + tolerance;
        const mouseTop = mousePosition.y - tolerance;
        const mouseBottom = mousePosition.y + mouseSize + tolerance;

        return (
            cursorPosition.x >= mouseLeft &&
            cursorPosition.x <= mouseRight &&
            cursorPosition.y >= mouseTop &&
            cursorPosition.y <= mouseBottom
        );
    }

    // ========== POSITION ALÉATOIRE ==========
    function getRandomPosition() {
        const containerWidth = gameContainer.offsetWidth;
        const containerHeight = gameContainer.offsetHeight;
        const mouseSize = currentDifficulty.mouseSize;

        const margin = 20;
        const maxX = containerWidth - mouseSize - margin;
        const maxY = containerHeight - mouseSize - margin;

        const randomX = Math.max(margin, Math.floor(Math.random() * maxX));
        const randomY = Math.max(margin, Math.floor(Math.random() * maxY));

        return { x: randomX, y: randomY };
    }

    // ========== APPARITION DE LA SOURIS ==========
    function spawnMouse() {
        if (!gameRunning) return;

        clearTimeout(mouseTimeout);

        const pos = getRandomPosition();
        mousePosition = pos;
        
        mouse.style.left = `${pos.x}px`;
        mouse.style.top = `${pos.y}px`;
        mouse.style.width = `${currentDifficulty.mouseSize}px`;
        mouse.style.height = `${currentDifficulty.mouseSize}px`;
        mouse.style.display = 'block';
        mouse.style.opacity = '1';
        mouse.style.transform = 'scale(1)';

        // Disparition automatique après le temps défini
        mouseTimeout = setTimeout(() => {
            if (gameRunning) {
                mouse.style.opacity = '0';
                setTimeout(() => {
                    spawnMouse(); // Réapparaît immédiatement ailleurs
                }, 100);
            }
        }, currentDifficulty.mouseVisibilityMs);
    }

    // ========== CLIC SUR LE CONTAINER ==========
    function handleGameClick(e) {
        if (!gameRunning || mouse.style.display !== 'block') return;

        updateChatCursor(e);
        chatCursor.style.width = `${currentDifficulty.chatSize}px`;
        chatCursor.style.height = `${currentDifficulty.chatSize}px`;


        if (checkCollision()) {
            // ✅ Souris attrapée !
            score++;
            scoreDisplay.textContent = score;

            // ✅ Son petitDing
            clickSound.currentTime = 0;
            clickSound.play().catch(() => {});

            // Animation de capture
            mouse.style.transform = 'scale(1.3) rotate(15deg)';
            mouse.style.opacity = '0';

            clearTimeout(mouseTimeout);

            setTimeout(() => {
                spawnMouse();
            }, 100);
        }
    }

    gameContainer?.addEventListener('click', handleGameClick);

    // ========== TIMER DU JEU ==========
    function startGameTimer() {
        if (gameTimerInterval) {
            clearInterval(gameTimerInterval);
            gameTimerInterval = null;
        }

        gameTimerInterval = setInterval(() => {
            timeLeft--;
            
            if (gameTimeDisplay) {
                gameTimeDisplay.textContent = timeLeft;

                // Alerte visuelle à 10s
                if (timeLeft <= 10) {
                    gameTimeDisplay.style.color = '#FF5733';
                    gameTimeDisplay.style.fontWeight = 'bold';
                }
            }

            if (timeLeft <= 0) {
                clearInterval(gameTimerInterval);
                gameTimerInterval = null;
                endGame();
            }
        }, 1000);
    }

    // ========== DÉMARRAGE DU JEU ==========
    function startGame() {
        if (gameRunning) return;

        gameRunning = true;
        score = 0;
        timeLeft = currentDifficulty.gameDurationSeconds;

        scoreDisplay.textContent = score;
        
        if (gameTimeDisplay) {
            gameTimeDisplay.textContent = timeLeft;
            gameTimeDisplay.style.color = '';
            gameTimeDisplay.style.fontWeight = '';
        }
        
        resultMessage.textContent = `🎮 Partie en cours : Niveau ${currentDifficulty.name}`;
        resultMessage.style.color = '#333';

        // ✅ Afficher le container de jeu
        gameContainer.style.display = 'block';
        gameContainer.classList.add('active');
        chatCursor.style.display = 'block';
        gameContainer.style.cursor = 'none';

        // Désactiver les boutons
        levelButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
        });
        if (startButton) {
            startButton.disabled = true;
            //startButton.style.opacity = '0.5';
            //startButton.textContent = 'En cours...';
        }

        // ✅ Démarrer la musique
        if (!isMuted) {
            bgMusic.play().catch(err => console.log('Musique bloquée:', err));
        }

        startGameTimer();
        spawnMouse();
    }

    // ========== FIN DU JEU ==========
    function endGame() {
        gameRunning = false;
        mouse.style.display = 'none';
        chatCursor.style.display = 'none';
        gameContainer.style.cursor = 'default';
        
        clearTimeout(mouseTimeout);
        if (gameTimerInterval) {
            clearInterval(gameTimerInterval);
            gameTimerInterval = null;
        }

        // Arrêter la musique
        bgMusic.pause();
        bgMusic.currentTime = 0;

        // ✅ Son endgame
        winSound.currentTime = 0;
        winSound.play().catch(() => {});
        
        // ✅ Pluie d'étoiles
        starConfetti();

        resultMessage.textContent = `🎉 Partie terminée ! Score : ${score}`;
        resultMessage.style.color = '#ed55c0';
        resultMessage.style.fontWeight = 'bold';

        // Sauvegarder le score
        sendScoreToServer(score, currentDifficulty.key);
    }

    // ========== SAUVEGARDE DU SCORE ==========
    function sendScoreToServer(finalScore, levelKey) {
    fetch('?route=saveClickScore', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            score: finalScore,
            level: levelKey
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse serveur:', data);

        if (data.success) {
            const isNewRecord = data.isNewRecord === true;

            // Mise à jour du meilleur score affiché
            if (data.newBestScore !== undefined && data.level) {
                const displayElement = bestScoreDisplays[data.level];
                if (displayElement) {
                    displayElement.textContent = data.newBestScore;
                }
            }

            // ✅ Overlay dans tous les cas
            setTimeout(() => {
                if (isNewRecord) {
                    showVictoryOverlay(true); // record
                } else {
                    showDefeatOverlay(); // pas record
                }
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        setTimeout(() => {
            showDefeatOverlay();
        }, 1000);
    });
}


    // ========== RÉINITIALISATION ==========
    function resetGame() {
        gameContainer.style.display = 'none';
        gameContainer.classList.remove('active');
        mouse.style.display = 'none';
        chatCursor.style.display = 'none';
        
        score = 0;
        timeLeft = 60;
        
        scoreDisplay.textContent = '0';
        if (gameTimeDisplay) {
            gameTimeDisplay.textContent = '60';
            gameTimeDisplay.style.color = '';
            gameTimeDisplay.style.fontWeight = '';
        }
        resultMessage.textContent = '';

        // Réactiver les boutons
        levelButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
        if (startButton) {
            startButton.disabled = false;
            startButton.style.opacity = '1';
            //startButton.textContent = 'Démarrer';
        }
    }

    // ========== SÉLECTION DU NIVEAU ET DÉMARRAGE ==========
    function selectAndStartGame(event) {
        const selectedKey = event.currentTarget.dataset.difficulty;

        if (difficultyLevels[selectedKey]) {
            currentDifficulty = difficultyLevels[selectedKey];

            levelButtons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            startGame();
        }
    }

    // Event listeners sur les boutons de niveau
    levelButtons.forEach(button => {
        button.addEventListener('click', selectAndStartGame);
    });

    // Event listener sur le bouton Démarrer (lance avec le niveau par défaut)
    startButton?.addEventListener('click', () => {
        if (!gameRunning) {
            startGame();
        }
    });

    // ========== BOUTONS AUDIO ==========
    muteBtn?.addEventListener('click', () => {
        isMuted = !isMuted;
        bgMusic.muted = isMuted;
        clickSound.muted = isMuted;
        winSound.muted = isMuted;
        
        const img = muteBtn.querySelector('img');
        if (img) {
            img.src = isMuted 
                ? 'assets/img/game/memory/musicNote.png' 
                : 'assets/img/game/memory/mute1.png';
        }
        
        console.log('Son:', isMuted ? 'Coupé' : 'Activé');
    });

    volumeBtn?.addEventListener('click', () => {
        if (bgMusic.volume === 0.4) {
            bgMusic.volume = 0.2;
            clickSound.volume = 0.6;
            winSound.volume = 0.6;
            console.log('Volume: Moyen');
        } else if (bgMusic.volume === 0.2) {
            bgMusic.volume = 1;
            clickSound.volume = 1;
            winSound.volume = 1;
            console.log('Volume: Fort');
        } else {
            bgMusic.volume = 0.4;
            clickSound.volume = 1;
            winSound.volume = 1;
            console.log('Volume: Normal');
        }
    });

    // ========== INITIALISATION ==========
    if (mouse) mouse.style.display = 'none';
    if (chatCursor) chatCursor.style.display = 'none';
    if (gameContainer) {
        gameContainer.style.display = 'none';
        gameContainer.classList.remove('active');
    }

    console.log('🎮 Jeu Click Souris chargé!');
    console.log('Éléments trouvés:', {
        gameContainer: !!gameContainer,
        mouse: !!mouse,
        chatCursor: !!chatCursor,
        levelButtons: levelButtons.length,
        startButton: !!startButton
    });
});