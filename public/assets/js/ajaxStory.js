document.addEventListener('DOMContentLoaded', function() {
    const characterSelect = document.querySelector('select[name="character_id"]');
    const locationSelect = document.querySelector('select[name="location_id"]');
    const itemSelect = document.querySelector('select[name="item_id"]');
    const characterImageDiv = document.getElementById('character-image');
    const itemImageDiv = document.getElementById('item-image');
    const locationImageDiv = document.getElementById('location-image');    
    const storySection = document.getElementById('story-section');
    //const storyButton = document.querySelector('.bouton-3d');
    const storyButton = document.getElementById('generate-story');
    // Son magique à jouer au clic du bouton
    const magicSound = new Audio('assets/sounds/magicStory.mp3');


     // Changement personnage
    characterSelect.addEventListener('change', function() {
        const characterId = this.value;
        if (characterId) {
            fetch(`?route=getImage&entity=character&id=${characterId}`)
                .then(response => response.json())
                .then(data => {
                    characterImageDiv.innerHTML = `<img src="${data.url}" alt="Image de personnage">`;
                   fetchStory();
                })
                .catch(error => console.error('Erreur chargement personnage:', error));
        }
    });

    // Changement item
    itemSelect.addEventListener('change', function() {
        const itemId = this.value;
        if (itemId) {
            fetch(`?route=getImage&entity=item&id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    itemImageDiv.innerHTML = `<img src="${data.url}" alt="Image d'objet">`;
                   fetchStory();
                })
                .catch(error => console.error('Erreur chargement item:', error));
        }
    });

    // Changement location
    locationSelect.addEventListener('change', function() {
        const locationId = this.value;
        if (locationId) {
            fetch(`?route=getImage&entity=location&id=${locationId}`)
                .then(response => response.json())
                .then(data => {
                    locationImageDiv.innerHTML = `<img src="${data.url}" alt="Image de lieu">`;
                    fetchStory();
                })
                .catch(error => console.error('Erreur chargement location:', error));
        }
    });

    // ✨ NOUVELLE FONCTIONNALITÉ : Clic sur le bouton "Générer l'histoire"
    storyButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const characterId = characterSelect.value;
        const itemId = itemSelect.value;
        const locationId = locationSelect.value;

        // Vérifier que tous les champs sont remplis
        if (!characterId || !itemId || !locationId) {
            storySection.innerHTML = `
                <div class="error-message">
                    <p>⚠️ Veuillez sélectionner un personnage, un objet ET un lieu.</p>
                </div>
            `;
            storySection.classList.add('visible');
            return;
        }

        // 🎵 Jouer le son magique immédiatement
        magicSound.currentTime = 0; // Recommencer depuis le début
        magicSound.play().catch(error => {
            console.warn('⚠️ Impossible de jouer le son magique:', error);
        });

        // Afficher un loader pendant le chargement
        storySection.innerHTML = `
            <div class="loading">
                <p>✨ Chargement de l'histoire magique...</p>
            </div>
        `;
        storySection.classList.add('visible');

        // Charger l'histoire
        fetch(`?route=getStory&perso=${characterId}&item=${itemId}&location=${locationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(story => {
                console.log('Story reçue:', story);
                displayStory(story);
            })
            .catch(error => {
                console.error('Erreur récupération histoire:', error);
                storySection.innerHTML = `
                    <div class="error-message">
                        <p>⚠️ Une erreur est survenue lors du chargement de l'histoire.</p>
                        <p>Détails: ${error.message}</p>
                    </div>
                `;
            });
    });

    /**
     * Affiche l'histoire et gère la lecture audio
     */
    function displayStory(story) {
        if (story && story.story_title) {
            storySection.innerHTML = `
                <article id="story-header">
                    <h3>${story.story_title}</h3>
                    <div>
                        ${story.audio ? `
                            <button id="play-audio" aria-label="Lecture audio">
                                <img id="play-icon" src="assets/img/Miaou/play.png" alt="Play">
                                <img id="pause-icon" src="assets/img/Miaou/pause.png" alt="Pause" style="display:none;">
                            </button>
                            <audio id="audio" src="${story.audio}"></audio>
                        ` : ''}
                    </div>
                </article>
                <article id="story-content">${story.story_content}</article>
                ${story.url ? `
                    <div class="download-section">
                        <a href="${story.url}" download class="download-link">
                            <span>📄</span> Télécharger le PDF
                        </a>
                        <div class="progress-bar" id="progress-bar-pdf"></div>
                    </div>
                ` : ''}
                ${story.audio ? `
                    <div class="download-section">
                        <a href="${story.audio}" download class="download-link">
                            <span>🎵</span> Télécharger l'audio MP3
                        </a>
                        <div class="progress-bar" id="progress-bar-audio"></div>
                    </div>
                ` : ''}
            `;
            
            // Initialiser le lecteur audio si présent
            if (story.audio) {
                initAudioPlayer(story.audio);
            }
        } else {
            storySection.innerHTML = `
                <div class="no-story">
                    <p>😕 Aucune histoire trouvée pour cette combinaison.</p>
                    <p>Essayez une autre sélection !</p>
                </div>
            `;
        }
    }

    /**
     * Initialise le lecteur audio avec lecture automatique après 5 secondes
     */
    function initAudioPlayer(audioSrc) {
        const playButton = document.getElementById('play-audio');
        if (!playButton) return;

        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        const audio = document.getElementById('audio');

        // 🎵 LECTURE AUTOMATIQUE après 5 secondes
        setTimeout(() => {
            audio.play()
                .then(() => {
                    console.log('▶️ Lecture audio démarrée automatiquement');
                    playIcon.style.display = 'none';
                    pauseIcon.style.display = 'block';
                    playButton.setAttribute('aria-label', 'Mettre en pause');
                })
                .catch(error => {
                    console.warn('⚠️ Lecture auto impossible (politique du navigateur):', error);
                    // Si la lecture auto échoue, l'utilisateur devra cliquer manuellement
                });
        }, 5000); // 5 secondes

        // Gestion du clic sur le bouton play/pause
        playButton.addEventListener('click', function() {
            if (audio.paused) {
                audio.play()
                    .then(() => {
                        playIcon.style.display = 'none';
                        pauseIcon.style.display = 'block';
                        playButton.setAttribute('aria-label', 'Mettre en pause');
                    })
                    .catch(error => {
                        console.error('Erreur lecture audio:', error);
                    });
            } else {
                audio.pause();
                playIcon.style.display = 'block';
                pauseIcon.style.display = 'none';
                playButton.setAttribute('aria-label', 'Lecture');
            }
        });
        
        // Réinitialiser les icônes quand l'audio se termine
        audio.addEventListener('ended', function() {
            playIcon.style.display = 'block';
            pauseIcon.style.display = 'none';
            playButton.setAttribute('aria-label', 'Lecture');
            console.log('⏹️ Audio terminé');
        });

        // Gestion des erreurs audio
        audio.addEventListener('error', function() {
            console.error('❌ Erreur de chargement de l\'audio');
            playButton.style.display = 'none';
        });

        // Mise à jour visuelle pendant la lecture
        audio.addEventListener('play', function() {
            playIcon.style.display = 'none';
            pauseIcon.style.display = 'block';
            playButton.setAttribute('aria-label', 'Mettre en pause');
        });

        audio.addEventListener('pause', function() {
            playIcon.style.display = 'block';
            pauseIcon.style.display = 'none';
            playButton.setAttribute('aria-label', 'Lecture');
        });
    }

    /**
     * Initialise la barre de progression pour les téléchargements
     */
    function initDownloadProgress() {
        const downloadLinks = document.querySelectorAll('.download-link');
        
        downloadLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const progressBar = this.parentElement.querySelector('.progress-bar');
                if (progressBar) {
                    progressBar.classList.add('active');
                    
                    // Simuler une progression
                    setTimeout(() => {
                        progressBar.classList.remove('active');
                    }, 2000);
                }
            });
        });
    }
    });