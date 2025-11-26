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

    
    
    /*storyButton.addEventListener('click', function() {
        const characterId = characterSelect.value;
        const itemId = itemSelect.value;
        const locationId = locationSelect.value;

        if (!characterId || !itemId || !locationId) {
            storySection.innerHTML = '<p>⚠️ Sélectionnez un personnage, un objet et un lieu.</p>';
            return;
        }

        storySection.innerHTML = '<p>✨ Chargement de l’histoire...</p>';

        fetch(`?route=getStory&perso=${characterId}&item=${itemId}&location=${locationId}`)
            .then(response => response.json())
            .then(story => {
                if (story && story.story_title) {
                    // … ton rendu HTML avec titre, audio, PDF/MP3 …
                } else {
                    storySection.innerHTML = '<p>😕 Aucune histoire trouvée.</p>';
                }
            })
            .catch(error => {
                storySection.innerHTML = `<p style="color:red;">Erreur: ${error.message}</p>`;
            });
    });*/



    function fetchStory() {
        const characterId = characterSelect.value;
        const itemId = itemSelect.value;
        const locationId = locationSelect.value;

        // Vérifier que les 3 éléments sont sélectionnés
        if (characterId && itemId && locationId) {
            // CORRECTION: utiliser les bons noms de paramètres
            fetch(`?route=getStory&perso=${characterId}&item=${itemId}&location=${locationId}`)              
                .then(response => response.json())
                .then(story => {
                    console.log('Story reçue:', story);
   
                    // CORRECTION: utiliser les noms de colonnes de la BDD
                    if (story && story.story_title) {
                        storySection.innerHTML = `
                            <article id="story-header">
                                <h3>${story.story_title}</h3>
                                <div>
                                    ${story.audio ? `
                                        <button id="play-audio">
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
                        const playButton = document.getElementById('play-audio');
                        if (playButton) {
                            const playIcon = document.getElementById('play-icon');
                            const pauseIcon = document.getElementById('pause-icon');
                            const audio = document.getElementById('audio');

                            playButton.addEventListener('click', function() {
                                if (audio.paused) {
                                    audio.play();
                                    playIcon.style.display = 'none';
                                    pauseIcon.style.display = 'block';
                                } else {
                                    audio.pause();
                                    playIcon.style.display = 'block';
                                    pauseIcon.style.display = 'none';
                                }
                            });
                            
                            // Réinitialiser les icônes quand l'audio se termine
                            audio.addEventListener('ended', function() {
                                playIcon.style.display = 'block';
                                pauseIcon.style.display = 'none';
                            });
                        }
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
                    console.error('Erreur récupération histoire:', error);
                    storySection.innerHTML = `
                        <div class="error-message">
                            <p>⚠️ Une erreur est survenue lors du chargement de l'histoire.</p>
                        </div>
                    `;
                });
        } else {
            // Réinitialiser si sélection incomplète
            storySection.innerHTML = '';
        }
    }

    storyButton.addEventListener('click', function() {
        storySection.classList.add('visible');
    });
});