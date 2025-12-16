document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#histoire-form');
    const storySection = document.querySelector('#story-section');

    // Empêcher la soumission du formulaire (car on utilise déjà les selects onChange)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
       
        // CORRECTION: Utiliser les bons noms d'attributs name
        const characterId = document.querySelector('select[name="character_id"]').value;
        const locationId = document.querySelector('select[name="location_id"]').value;
        const itemId = document.querySelector('select[name="item_id"]').value;

        // Vérifier que tous les champs sont remplis
        if (!characterId || !locationId || !itemId) {
            storySection.innerHTML = `
                <div class="error-message">
                    <p>⚠️ Veuillez sélectionner un personnage, un objet ET un lieu.</p>
                </div>
            `;
            return;
        }

        // Afficher un loader pendant le chargement
        storySection.innerHTML = `
            <div class="loading">
                <p>✨ Chargement de l'histoire magique...</p>
            </div>
        `;

        // CORRECTION: Utiliser la bonne route et les bons noms de paramètres
        fetch(`?route=getStory&perso=${characterId}&item=${itemId}&location=${locationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(story => {
                console.log('Story reçue:', story);

                // CORRECTION: Utiliser les bons noms de colonnes de la BDD
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
                    initAudioPlayer();
                    
                    // Initialiser les téléchargements avec barre de progression
                    initDownloadProgress();
                    
                } else {
                    storySection.innerHTML = `
                        <div class="no-story">
                            <p>😕 Aucune histoire trouvée pour cette combinaison.</p>
                            <p>Essayez une autre sélection !</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération de l\'histoire:', error);
                storySection.innerHTML = `
                    <div class="error-message">
                        <p>⚠️ Une erreur est survenue lors du chargement de l'histoire.</p>
                        <p>Détails: ${error.message}</p>
                    </div>
                `;
            });
    });

    /**
     * Initialise le lecteur audio avec gestion des icônes
     */
    function initAudioPlayer() {
        const playButton = document.getElementById('play-audio');
        if (!playButton) return;

        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        const audio = document.getElementById('audio');

        playButton.addEventListener('click', function() {
            if (audio.paused) {
                audio.play();
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'block';
                playButton.setAttribute('aria-label', 'Mettre en pause');
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
        });

        // Gestion des erreurs audio
        audio.addEventListener('error', function() {
            console.error('Erreur de chargement de l\'audio');
            playButton.style.display = 'none';
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
                    
                    // Simuler une progression (à remplacer par une vraie progression si nécessaire)
                    setTimeout(() => {
                        progressBar.classList.remove('active');
                    }, 2000);
                }
            });
        });
    }
});