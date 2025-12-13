/**
 * Jeu Pixel Art - Version complète
 */

document.addEventListener('DOMContentLoaded', () => {

  // ========== CONFIGURATION DES NIVEAUX ==========
  const levels = {
    easy: {
      gridSize: 8,
      colors: ['#000000', '#FFFFFF', '#FF5733', '#33FF57', '#3357FF', '#FFFF33']
      // Noir, Blanc, Rouge, Vert, Bleu, Jaune
    },
    intermediate: {
      gridSize: 12,
      colors: ['#000000', '#FFFFFF', '#3357FF', '#FFFF33', '#FF33FF', '#33FFFF',
               '#FF8800', '#8800FF', '#0088FF', '#00FF88', '#FF0088', '#888888']
      // Noir, Blanc, Bleu, Jaune, Magenta, Cyan, Orange, Violet, Bleu clair, Vert clair, Rose, Gris
    },
    hard: {
      gridSize: 16,
      colors: ['#FF5733', '#33FF57', '#3357FF', '#FFFF33', '#FF33FF', '#33FFFF',
               '#FF8800', '#8800FF', '#0088FF', '#00FF88', '#FF0088', '#888888',
               '#000000', '#FFFFFF', '#AA5533', '#55AA33']
      // Rouge, Vert, Bleu, Jaune, Magenta, Cyan, Orange, Violet, Bleu clair, Vert clair, Rose, Gris, Noir, Blanc, Marron, Vert foncé
    }
  };

  // ========== BIBLIOTHÈQUE DE MODÈLES ==========
  const models = {
    easy: {
      heart: generateHeartModel(8),
      smiley: generateSmileyModel(8),
      flower: generateFlowerModel(8)
    },
    intermediate: {
      star: generateStarModel(12),
      sun: generateSunModel(12),
      tree: generateTreeModel(12)
    },
    hard: {
      castle: generateCastleModel(16),
      cat: generateCatModel(16),
      rainbow: generateRainbowModel(16)
    }
  };

  // ========== SÉLECTEURS DOM ==========
  const container = document.getElementById('pixelArtContainer');
  const colorPalette = document.getElementById('colorPalette');
  const resetButton = document.getElementById('resetButton');
  const validateButton = document.getElementById('validateButton');
  const modelPreview = document.getElementById('modelPreview');
  const freeDrawBtn = document.getElementById('free-draw-btn');
  const modelSelection = document.querySelector('.model-selection');
  const modelSelect = document.getElementById('model-select');
  const gameArea = document.querySelector('.game-area');

  // Boutons audio
  const muteBtn = document.getElementById('mute-pixel');
  const volumeBtn = document.getElementById('volume-pixel');

  // ========== AUDIO ==========
  const bgMusic = new Audio('assets/sounds/game/pixelart/backgroundMusicMemory.wav');
  const successSound = new Audio('assets/sounds/game/pixelart/success.mp3');

  bgMusic.loop = true;
  bgMusic.volume = 0.3;
  successSound.volume = 1;

  let audioUnlocked = false;
  let isMuted = false;

  // Unlock audio au premier clic
  document.addEventListener('click', () => {
    if (!audioUnlocked) {
      bgMusic.play().catch(() => {});
      audioUnlocked = true;
    }
  }, { once: true });

  // ========== VARIABLES GLOBALES ==========
  let selectedColor = '#000000';
  let currentLevel = 'easy';
  let currentModel = null;
  let currentModelData = null;
  let isFreeDrawMode = false;

  // ========== FONCTIONS DE GÉNÉRATION DE MODÈLES ==========
  function generateHeartModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    const heart = [
      [0,0,1,1,0,0,1,1],
      [0,1,2,2,1,1,2,2],
      [1,2,2,2,2,2,2,2],
      [1,2,2,2,2,2,2,2],
      [0,1,2,2,2,2,2,1],
      [0,0,1,2,2,2,1,0],
      [0,0,0,1,2,1,0,0],
      [0,0,0,0,1,0,0,0]
    ];
    const colors = ['#FFFFFF', '#000000', '#FF5733'];
    heart.forEach((row, i) => {
      row.forEach((val, j) => {
        model[i * size + j] = colors[val];
      });
    });
    return model;
  }

  function generateSmileyModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    const smiley = [
      [0,0,1,1,1,1,0,0],
      [0,1,2,2,2,2,1,0],
      [1,2,2,1,2,1,2,1],
      [1,2,2,2,2,2,2,1],
      [1,2,1,2,2,1,2,1],
      [1,2,2,1,1,2,2,1],
      [0,1,2,2,2,2,1,0],
      [0,0,1,1,1,1,0,0]
    ];
    const colors = ['#FFFFFF', '#000000', '#FFFF33'];
    smiley.forEach((row, i) => {
      row.forEach((val, j) => {
        model[i * size + j] = colors[val];
      });
    });
    return model;
  }

  function generateFlowerModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    const flower = [
      [0,0,1,1,1,1,0,0],
      [0,1,1,1,1,1,1,0],
      [1,1,2,1,1,2,1,1],
      [1,1,1,2,2,1,1,1],
      [1,1,1,2,2,1,1,1],
      [0,0,3,3,3,3,0,0],
      [0,0,0,3,3,0,0,0],
      [0,0,0,3,3,0,0,0]
    ];
    // Utilise uniquement les couleurs de la palette easy
    const colors = ['#FFFFFF', '#FF5733', '#FFFF33', '#33FF57']; // Blanc, Rouge, Jaune, Vert
    flower.forEach((row, i) => {
      row.forEach((val, j) => {
        model[i * size + j] = colors[val];
      });
    });
    return model;
  }

  function generateStarModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    // Étoile jaune simple
    const star = [
      [0,0,0,0,0,1,0,0,0,0,0,0],
      [0,0,0,0,1,1,1,0,0,0,0,0],
      [0,0,0,0,1,1,1,0,0,0,0,0],
      [0,0,1,1,1,1,1,1,1,0,0,0],
      [0,0,0,1,1,1,1,1,0,0,0,0],
      [0,0,1,1,1,1,1,1,1,0,0,0],
      [0,1,1,1,0,1,0,1,1,1,0,0],
      [1,1,1,0,0,1,0,0,1,1,1,0],
      [0,1,0,0,0,0,0,0,0,1,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0]
    ];
    const colors = ['#FFFFFF', '#FFFF33']; // Blanc, Jaune
    star.forEach((row, i) => {
      row.forEach((val, j) => {
        model[i * size + j] = colors[val];
      });
    });
    return model;
  }

  function generateSunModel(size) {
    const model = Array(size * size).fill('#3357FF'); // Fond bleu
    // Soleil avec rayons
    for (let i = 0; i < size; i++) {
      for (let j = 0; j < size; j++) {
        const centerX = size / 2;
        const centerY = size / 2;
        const dx = j - centerX;
        const dy = i - centerY;
        const dist = Math.sqrt(dx * dx + dy * dy);
        
        // Cœur du soleil en jaune
        if (dist < 2.5) {
          model[i * size + j] = '#FFFF33';
        }
        // Rayons en orange
        else if (dist < 4 && (Math.abs(dx) < 0.5 || Math.abs(dy) < 0.5 || Math.abs(dx - dy) < 0.5 || Math.abs(dx + dy) < 0.5)) {
          model[i * size + j] = '#FF8800';
        }
      }
    }
    return model;
  }

  function generateTreeModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    // Sapin avec feuillage vert et tronc violet
    for (let i = 0; i < size; i++) {
      for (let j = 0; j < size; j++) {
        // Feuillage (triangle vert)
        if (i < size * 0.7 && Math.abs(j - size/2) < (size * 0.4 - i * 0.3)) {
          model[i * size + j] = '#00FF88'; // Vert clair
        }
        // Tronc (rectangle violet)
        else if (i >= size * 0.7 && i < size * 0.9 && Math.abs(j - size/2) < 1.5) {
          model[i * size + j] = '#8800FF'; // Violet
        }
      }
    }
    return model;
  }

  function generateCastleModel(size) {
    const model = Array(size * size).fill('#3357FF'); // Fond bleu (ciel)
    
    // Château en gris avec porte noire
    for (let i = 0; i < size; i++) {
      for (let j = 0; j < size; j++) {
        // Tours gauche et droite
        if (i > size * 0.3 && i < size * 0.9) {
          if ((j > 1 && j < 4) || (j > size - 5 && j < size - 2)) {
            model[i * size + j] = '#888888'; // Gris
          }
        }
        
        // Corps central du château
        if (i > size * 0.4 && i < size * 0.9 && j > 4 && j < size - 4) {
          model[i * size + j] = '#888888'; // Gris
        }
        
        // Porte
        if (i > size * 0.6 && i < size * 0.85 && j > size/2 - 2 && j < size/2 + 2) {
          model[i * size + j] = '#000000'; // Noir
        }
        
        // Créneaux en haut
        if (i > size * 0.3 && i < size * 0.35) {
          if ((j > 1 && j < 4) || (j > size - 5 && j < size - 2) || 
              (j > size/2 - 2 && j < size/2 + 2)) {
            model[i * size + j] = '#888888';
          }
        }
      }
    }
    return model;
  }

  function generateCatModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    
    // Chat noir simplifié
    for (let i = 0; i < size; i++) {
      for (let j = 0; j < size; j++) {
        // Oreilles (triangles en haut)
        if (i < 3) {
          if ((j > 2 && j < 6) || (j > size - 7 && j < size - 3)) {
            model[i * size + j] = '#000000';
          }
        }
        
        // Tête (ovale)
        if (i > 2 && i < size * 0.5 && Math.abs(j - size/2) < 4) {
          model[i * size + j] = '#000000';
        }
        
        // Corps
        if (i >= size * 0.5 && i < size * 0.85 && Math.abs(j - size/2) < 5) {
          model[i * size + j] = '#000000';
        }
        
        // Queue
        if (i > size * 0.7 && i < size * 0.9 && j < 3) {
          model[i * size + j] = '#000000';
        }
      }
    }
    return model;
  }

  function generateRainbowModel(size) {
    const model = Array(size * size).fill('#FFFFFF');
    // Arc-en-ciel avec les couleurs disponibles dans hard
    const colors = ['#FF5733', '#FF8800', '#FFFF33', '#33FF57', '#3357FF', '#8800FF', '#FF33FF'];
    
    for (let i = 0; i < size; i++) {
      for (let j = 0; j < size; j++) {
        const centerX = size / 2;
        const centerY = size;
        const dx = j - centerX;
        const dy = i - centerY;
        const dist = Math.sqrt(dx * dx + dy * dy);
        
        // Arc-en-ciel (plusieurs arcs de différentes tailles)
        if (dist < size * 0.8 && dist > size * 0.2 && i < size * 0.7) {
          const colorIndex = Math.floor((dist - size * 0.2) / (size * 0.6 / colors.length));
          if (colorIndex >= 0 && colorIndex < colors.length) {
            model[i * size + j] = colors[colorIndex];
          }
        }
      }
    }
    return model;
  }

  // ========== FONCTIONS PRINCIPALES ==========

  function createGrid(size) {
    container.innerHTML = '';
    container.style.gridTemplateColumns = `repeat(${size}, 1fr)`;
    container.style.gridTemplateRows = `repeat(${size}, 1fr)`;
    container.dataset.level = currentLevel; // Pour le CSS spécifique

    for (let i = 0; i < size * size; i++) {
      const pixel = document.createElement('div');
      pixel.classList.add('pixel');
      pixel.style.backgroundColor = '#FFFFFF';
      pixel.dataset.index = i;
      
      pixel.addEventListener('click', () => {
        pixel.style.backgroundColor = selectedColor;
      });
      
      container.appendChild(pixel);
    }

    container.classList.add('active');
  }

  function updateColorPalette(colors) {
    colorPalette.innerHTML = '';
    colors.forEach((hex, index) => {
      const swatch = document.createElement('div');
      swatch.className = 'color';
      swatch.style.backgroundColor = hex;
      if (index === 0) swatch.classList.add('selected');
      colorPalette.appendChild(swatch);
    });
    bindPaletteSelection();
  }

  function bindPaletteSelection() {
    colorPalette.querySelectorAll('.color').forEach(colorDiv => {
      colorDiv.addEventListener('click', () => {
        selectedColor = colorDiv.style.backgroundColor;
        colorPalette.querySelectorAll('.color').forEach(c => c.classList.remove('selected'));
        colorDiv.classList.add('selected');
      });
    });
  }

  function showModel(modelData, size) {
    if (!modelData) return;
    
    modelPreview.innerHTML = '';
    modelPreview.style.display = 'grid';
    modelPreview.style.gridTemplateColumns = `repeat(${size}, 1fr)`;
    modelPreview.style.gridTemplateRows = `repeat(${size}, 1fr)`;
    modelPreview.classList.add('active');

    modelData.forEach(color => {
      const pixel = document.createElement('div');
      pixel.className = 'pixel-preview';
      pixel.style.backgroundColor = color;
      modelPreview.appendChild(pixel);
    });
  }

  function rgbToHex(rgb) {
    const result = rgb.match(/\d+/g);
    if (!result) return rgb;
    return '#' + result.map(x => {
      const hex = parseInt(x).toString(16);
      return hex.length === 1 ? '0' + hex : hex;
    }).join('');
  }

  function validateModel() {
    if (isFreeDrawMode || !currentModelData) {
      alert('Mode dessin libre ou pas de modèle à valider !');
      return;
    }

    const pixels = container.querySelectorAll('.pixel');
    let success = true;

    for (let i = 0; i < currentModelData.length; i++) {
      const expected = currentModelData[i].toLowerCase();
      let actual = pixels[i].style.backgroundColor.toLowerCase();
      
      if (actual.startsWith('rgb')) {
        actual = rgbToHex(actual);
      }
      
      if (expected !== actual) {
        success = false;
        break;
      }
    }

    if (success) {
      successSound.currentTime = 0;
      successSound.play().catch(() => {});
      starConfetti();
      showSuccessToast();
    } else {
      alert("Essaie encore ! Compare bien avec le modèle.");
    }
  }

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

  function showSuccessToast() {
    const toast = document.createElement('div');
    toast.className = 'pixelart-toast';
    toast.textContent = '🎉 Bravo ! Modèle réussi ! 🎉';
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => {
      toast.classList.remove('visible');
      setTimeout(() => toast.remove(), 300);
    }, 2500);
  }

  function resetGrid() {
    container.querySelectorAll('.pixel').forEach(pixel => {
      pixel.style.backgroundColor = '#FFFFFF';
    });
  }

  function showModelSelection(level) {
    const levelModels = models[level];
    
    // Créer les options du select
    modelSelect.innerHTML = '<option value="">-- Choisis un modèle --</option>';
    Object.keys(levelModels).forEach(modelName => {
      const option = document.createElement('option');
      option.value = modelName;
      option.textContent = modelName.charAt(0).toUpperCase() + modelName.slice(1);
      modelSelect.appendChild(option);
    });

    modelSelection.style.display = 'flex';
    gameArea.style.display = 'none';
  }

  // ========== EVENT LISTENERS ==========

  // Boutons de niveau
  document.querySelectorAll('.btn-level').forEach(btn => {
    btn.addEventListener('click', () => {
      currentLevel = btn.dataset.level;
      showModelSelection(currentLevel);
    });
  });

  // Select de modèle
  modelSelect?.addEventListener('change', (e) => {
    const modelName = e.target.value;
    if (!modelName) return;

    const levelModels = models[currentLevel];
    currentModel = modelName;
    currentModelData = levelModels[modelName];
    isFreeDrawMode = false;
    
    const config = levels[currentLevel];
    createGrid(config.gridSize);
    updateColorPalette(config.colors);
    showModel(currentModelData, config.gridSize);
    
    gameArea.style.display = 'flex';
    validateButton.style.display = 'inline-flex';
    modelSelection.style.display = 'none'; // Masquer le select après choix
  });

  // Dessin libre
  freeDrawBtn.addEventListener('click', () => {
    isFreeDrawMode = true;
    currentModel = null;
    currentModelData = null;
    
    const config = levels[currentLevel];
    createGrid(config.gridSize);
    updateColorPalette(config.colors);
    
    modelPreview.style.display = 'none';
    modelPreview.classList.remove('active');
    gameArea.style.display = 'flex';
    validateButton.style.display = 'none';
    modelSelection.style.display = 'none';
  });

  // Reset
  resetButton.addEventListener('click', resetGrid);

  // Validation
  validateButton.addEventListener('click', validateModel);

  // Bouton Mute
  muteBtn?.addEventListener('click', () => {
    isMuted = !isMuted;
    bgMusic.muted = isMuted;
    successSound.muted = isMuted;
    
    const img = muteBtn.querySelector('img');
    if (img) {
      img.src = isMuted 
        ? 'assets/img/game/memory/musicNote.png' 
        : 'assets/img/game/memory/mute1.png';
    }
  });

  // Bouton Volume
  volumeBtn?.addEventListener('click', () => {
    if (bgMusic.volume === 0.3) {
      bgMusic.volume = 0.6;
      successSound.volume = 1;
    } else if (bgMusic.volume === 0.6) {
      bgMusic.volume = 1;
    } else {
      bgMusic.volume = 0.3;
    }
  });

  // ========== INITIALISATION ==========
  gameArea.style.display = 'none';
  validateButton.style.display = 'none';

  console.log('🎨 Pixel Art chargé!');
});