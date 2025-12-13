/**
 * Jeu Memory - Version corrigée
 */

window.onload = () => {
  // Sélecteurs principaux
  const gameContainer = document.querySelector('.game-container');
  const startButton = document.getElementById('start-game');
  const movesDisplay = document.getElementById('moves-count');
  const timerDisplay = document.getElementById('timer');

  // UI à masquer/afficher pendant la partie
  const levelButtons = document.querySelector('.level-buttons');
  const bestScoreMemo = document.querySelector('.best-score-memo');

  // Boutons audio
  const muteBtn = document.getElementById('mute');
  const volumeBtn = document.getElementById('volume');

  // Base des assets (images/sons)
  const baseSnd = "assets/sounds/game/memory/";
  const bgMusic = new Audio(`${baseSnd}backgroundMusicMemory.wav`);
  const matchSound = new Audio(`${baseSnd}petitDing.mp3`);
  const winSound = new Audio(`${baseSnd}endgame.mp3`);

  // Configuration audio
  bgMusic.loop = true;
  bgMusic.volume = 0.6;
  matchSound.volume = 1;
  winSound.volume = 1;

  // Unlock audio au premier clic utilisateur
  let audioUnlocked = false;
  document.addEventListener('click', () => {
    if (!audioUnlocked) {
      bgMusic.play().then(() => {
        console.log('Audio débloqué!');
      }).catch(() => {});
      audioUnlocked = true;
    }
  }, { once: true });

  const baseImg = "assets/img/game/memory/";

  // Configuration des niveaux
  const levels = {
    easy: {
      pairs: 6,
      grid: 'grid-template-columns: repeat(4, 1fr)',
      images: [
        `${baseImg}chat.jpg`,
        `${baseImg}etoile.png`,
        `${baseImg}coeur.png`,
        `${baseImg}soleil.png`,
        `${baseImg}arcenciel.png`,
        `${baseImg}nuage.png`,
      ],
    },
    intermediate: {
      pairs: 8,
      grid: 'grid-template-columns: repeat(4, 1fr)',
      images: [
        `${baseImg}chat.jpg`,
        `${baseImg}etoile.png`,
        `${baseImg}coeur.png`,
        `${baseImg}soleil.png`,
        `${baseImg}arcenciel.png`,
        `${baseImg}nuage.png`,
        `${baseImg}fleur.jpg`,
        `${baseImg}arbre.jpg`,
      ],
    },
    hard: {
      pairs: 12,
      grid: 'grid-template-columns: repeat(6, 1fr)',
      images: [
        `${baseImg}chat.jpg`,
        `${baseImg}etoile.png`,
        `${baseImg}coeur.png`,
        `${baseImg}soleil.png`,
        `${baseImg}arcenciel.png`,
        `${baseImg}nuage.png`,
        `${baseImg}fleur.jpg`,
        `${baseImg}arbre.jpg`,
        `${baseImg}licorne.jpg`,
        `${baseImg}papillonBleu.jpg`,
        `${baseImg}poisson.jpg`,
        `${baseImg}souris.png`,
      ],
    },
  };

  // État du jeu
  let currentLevel = 'easy';
  let maxScore = 6;
  let score = 0;
  let firstCard = null;
  let secondCard = null;
  let boardDisabled = true;
  let moves = 0;
  let sec = 0;
  let min = 0;
  let interval = null;
  let gameStarted = false;
  let isMuted = false;

  // Mélanger un tableau
  function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
  }

  // Effet de vibration + glow visuel (images restent visibles)
  function successFeedback(cards) {
    if ('vibrate' in navigator) {
      navigator.vibrate(200);
    }
    cards.forEach((card) => {
      card.classList.add('matched-effect');
      setTimeout(() => {
        card.classList.remove('matched-effect');
      }, 1000);
    });
  }

  // Pluie d'étoiles sur toute la page
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

  // Créer les cartes
  function createCards(level) {
    const config = levels[level];
    const faces = [...config.images, ...config.images];
    shuffle(faces);

    gameContainer.innerHTML = '';
    gameContainer.style.cssText = config.grid;
    gameContainer.classList.add('active');

    faces.forEach((face, i) => {
      const card = document.createElement('div');
      card.className = 'card-memo';
      card.dataset.index = i;
      card.innerHTML = `
        <div class="front"></div>
        <div class="back">
          <img src="${face}" alt="carte">
        </div>
      `;
      card.addEventListener('click', flipCard);
      gameContainer.appendChild(card);
    });
  }

  // Démarrer une partie
  function start(level) {
    currentLevel = level;
    score = 0;
    moves = 0;
    sec = 0;
    min = 0;
    gameStarted = true;
    maxScore = levels[level].pairs;

    movesDisplay.textContent = '0';
    timerDisplay.textContent = '00:00';

    createCards(level);
    boardDisabled = false;
    startTimer();
    startButton.textContent = 'Quitter';
    toggleUI(true);

    // Démarrer la musique
    if (!isMuted) {
      bgMusic.play().catch(err => console.log('Musique bloquée:', err));
    }
  }

  // Retourner une carte
  function flipCard() {
    if (boardDisabled || this.classList.contains('show') || this.classList.contains('matched')) {
      return;
    }

    if (!firstCard) {
      firstCard = this;
      this.classList.add('show');
    } else if (!secondCard && this !== firstCard) {
      moves++;
      movesDisplay.textContent = moves;
      secondCard = this;
      this.classList.add('show');

      const img1 = firstCard.querySelector('.back img').src;
      const img2 = secondCard.querySelector('.back img').src;

      if (img1 === img2) {
        // Paire trouvée
        matchSound.currentTime = 0;
        matchSound.play().catch(err => console.log("Son ding bloqué:", err));
        successFeedback([firstCard, secondCard]);

        setTimeout(() => {
          firstCard.classList.add('matched');
          secondCard.classList.add('matched');
          firstCard = null;
          secondCard = null;
          score++;

          // Victoire ?
          if (score === maxScore) {
            clearInterval(interval);
            interval = null;
            boardDisabled = true;
            setTimeout(() => {
              endGame();
            }, 500);
          }
        }, 300);
      } else {
        // Pas paire
        boardDisabled = true;
        setTimeout(() => {
          firstCard.classList.remove('show');
          secondCard.classList.remove('show');
          firstCard = null;
          secondCard = null;
          boardDisabled = false;
        }, 1000);
      }
    }
  }

  // Timer
  function startTimer() {
    if (!interval) {
      interval = setInterval(() => {
        sec++;
        if (sec === 60) {
          min++;
          sec = 0;
        }
        timerDisplay.textContent =
          `${min < 10 ? '0' + min : min}:${sec < 10 ? '0' + sec : sec}`;
      }, 1000);
    }
  }

  // Fin de partie avec affichage des best scores et réinitialisation après 15s
  function endGame() {
    const totalTime = min * 60 + sec;

    // Win sound + confetti
    winSound.currentTime = 0;
    winSound.play();
    starConfetti();

    // Stop musique
    bgMusic.pause();
    bgMusic.currentTime = 0;

    // Sauvegarder le score
    saveScore(moves, totalTime, currentLevel);
  }

  // Sauvegarder le score et afficher les résultats
  function saveScore(moves, time, level) {
    fetch('?route=saveMemoryScore', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ moves, time, level }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Afficher uniquement le meilleur score du niveau joué
          displayEndGameResults(moves, time, level, data.bestScores, data.isNewRecord);
          
          // Mettre à jour l'affichage des best scores si nouveau record
          if (data.isNewRecord) {
            updateBestScores(level, data.bestScores);
          }

          // Réinitialiser le jeu après 15 secondes
          setTimeout(() => {
            resetGame();
          }, 15000);
        }
      })
      .catch((error) => {
        console.error('Erreur sauvegarde score:', error);
        alert('Bravo ! Score: ' + moves + ' coups en ' + timerDisplay.textContent);
        setTimeout(() => resetGame(), 15000);
      });
  }

  // Afficher les résultats de fin de partie
  function displayEndGameResults(moves, time, level, bestScores, isNewRecord) {
    const minutes = Math.floor(time / 60);
    const seconds = time % 60;
    const timeFormatted = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
    
    const levelNames = {
      'easy': 'Facile',
      'intermediate': 'Intermédiaire',
      'hard': 'Difficile'
    };

    // Créer une overlay avec les résultats
    const overlay = document.createElement('div');
    overlay.className = 'end-game-overlay';
    overlay.innerHTML = `
      <div class="end-game-card">
        <h2>🎉 Bravo ! 🎉</h2>
        <p class="level-info">Niveau ${levelNames[level]}</p>
        
        <div class="current-score">
          <h3>Votre score</h3>
          <p>⏱️ Temps : <strong>${timeFormatted}</strong></p>
          <p>🎯 Coups : <strong>${moves}</strong></p>
        </div>

        ${bestScores.time !== null ? `
          <div class="best-score">
            <h3>${isNewRecord ? '🏆 Nouveau Record !' : '📊 Meilleur Score'}</h3>
            <p>⏱️ Meilleur Temps : <strong>${Math.floor(bestScores.time / 60)}:${(bestScores.time % 60).toString().padStart(2, '0')}</strong></p>
            <p>🎯 Moins de Coups : <strong>${bestScores.moves}</strong></p>
          </div>
        ` : ''}
        
        <p class="countdown">Retour dans <span id="countdown">10</span>s...</p>
      </div>
    `;
    
    setTimeout(() => {
      document.body.appendChild(overlay);

      // Compte à rebours
      let countdown = 8;
      const countdownEl = document.getElementById('countdown');
      const countdownInterval = setInterval(() => {
        countdown--;
        if (countdownEl) countdownEl.textContent = countdown;
        if (countdown <= 0) {
          clearInterval(countdownInterval);
          overlay.remove();
        }
      }, 1000);
    }, 3000); 
  }

  // Réinitialiser complètement le jeu
  function resetGame() {
    stop();
    document.querySelector('.end-game-overlay')?.remove();
  }

  // Mettre à jour best scores
  function updateBestScores(level, scores) {
    const timeElement = document.querySelector(`[data-level="${level}-time"]`);
    const movesElement = document.querySelector(`[data-level="${level}-moves"]`);

    if (timeElement && scores.time !== null) {
      const minutes = Math.floor(scores.time / 60);
      const seconds = scores.time % 60;
      timeElement.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
    }
    if (movesElement && scores.moves !== null) {
      movesElement.textContent = scores.moves;
    }
  }

  // Stopper la partie
  function stop() {
    clearInterval(interval);
    interval = null;
    score = 0;
    moves = 0;
    sec = 0;
    min = 0;
    gameStarted = false;

    movesDisplay.textContent = '0';
    timerDisplay.textContent = '00:00';
    gameContainer.innerHTML = '';
    gameContainer.classList.remove('active');
    boardDisabled = true;
    startButton.textContent = 'Démarrer';

    bgMusic.pause();
    bgMusic.currentTime = 0;

    toggleUI(false);
  }

  // Afficher/Masquer UI
  function toggleUI(isPlaying) {
    if (levelButtons) levelButtons.style.display = isPlaying ? 'none' : '';
    if (bestScoreMemo) bestScoreMemo.style.display = isPlaying ? 'none' : '';
  }

  // ========== EVENT LISTENERS ==========

  // Boutons de niveau
  document.getElementById('btn-easy')?.addEventListener('click', () => {
    if (!gameStarted) start('easy');
  });

  document.getElementById('btn-intermediate')?.addEventListener('click', () => {
    if (!gameStarted) start('intermediate');
  });

  document.getElementById('btn-hard')?.addEventListener('click', () => {
    if (!gameStarted) start('hard');
  });

  // Bouton Start/Quitter
  startButton?.addEventListener('click', () => {
    if (boardDisabled && !gameStarted) {
      start('easy');
    } else {
      if (confirm('Voulez-vous vraiment quitter la partie en cours ?')) {
        stop();
      }
    }
  });

  // Bouton Mute avec toggle
  muteBtn?.addEventListener('click', () => {
    isMuted = !isMuted;
    bgMusic.muted = isMuted;
    matchSound.muted = isMuted;
    winSound.muted = isMuted;
    
    // Changer l'image du bouton
    const img = muteBtn.querySelector('img');
    if (img) {
      img.src = isMuted 
        ? 'assets/img/game/memory/musicNote.png' 
        : 'assets/img/game/memory/mute1.png';
    }
    
    console.log('Son:', isMuted ? 'Coupé' : 'Activé');
  });

  // Bouton Volume
  volumeBtn?.addEventListener('click', () => {
    if (bgMusic.volume === 0.6) {
      bgMusic.volume = 0.3;
      matchSound.volume = 0.5;
      winSound.volume = 0.5;
      console.log('Volume: Moyen');
    } else if (bgMusic.volume === 0.3) {
      bgMusic.volume = 1;
      matchSound.volume = 1;
      winSound.volume = 1;
      console.log('Volume: Fort');
    } else {
      bgMusic.volume = 0.6;
      matchSound.volume = 1;
      winSound.volume = 1;
      console.log('Volume: Normal');
    }
  });

  console.log('🎮 Jeu Memory chargé!');
};