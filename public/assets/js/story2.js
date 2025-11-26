document.addEventListener('DOMContentLoaded', function() {

  // Fonction pour afficher image + texte dans Select2
  function formatOption(option) {
    if (!option.id) return option.text;
    const img = $(option.element).data('image');
    if (img) {
      return $(`
        <span class="option-with-image" data-image="${img}" data-text="${option.text}">
          <img src="${img}" style="width:24px;height:24px;margin-right:8px;vertical-align:middle;">
          ${option.text}
        </span>
      `);
    }
    return option.text;
  }

  // Initialisation Select2
  $('#character_id, #item_id, #location_id').select2({
    templateResult: formatOption,
    templateSelection: formatOption,
    width: '100%'
  });

  // Survol d’une option → affiche l’image dans la div correspondante
  $(document).on('mouseenter', '.option-with-image', function() {
    const img = $(this).data('image');
    const text = $(this).data('text');
    const parentSelectId = $(this).closest('.select2-container').prev('select').attr('id');

    if (parentSelectId === 'character_id') {
      $('#character-image').html(`<img src="${img}" alt="${text}" style="max-width:100px;">`);
    } else if (parentSelectId === 'item_id') {
      $('#item-image').html(`<img src="${img}" alt="${text}" style="max-width:100px;">`);
    } else if (parentSelectId === 'location_id') {
      $('#location-image').html(`<img src="${img}" alt="${text}" style="max-width:100px;">`);
    }
  });

  // Clic sur le bouton → charge l’histoire
  document.querySelector('.bouton-3d').addEventListener('click', function() {
    const characterId = $('#character_id').val();
    const itemId = $('#item_id').val();
    const locationId = $('#location_id').val();

    if (!characterId || !itemId || !locationId) {
      $('#story-section').html('<p>⚠️ Sélectionnez un personnage, un objet et un lieu.</p>');
      return;
    }

    $('#story-section').html('<p>✨ Chargement de l’histoire...</p>');

    fetch(`?route=getStory&perso=${characterId}&item=${itemId}&location=${locationId}`)
      .then(response => response.json())
      .then(story => {
        if (story && story.story_title) {
          $('#story-section').html(`
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
          `);

          initAudioPlayer();
          initDownloadProgress();
        } else {
          $('#story-section').html('<p>😕 Aucune histoire trouvée.</p>');
        }
      })
      .catch(error => {
        $('#story-section').html(`<p style="color:red;">Erreur: ${error.message}</p>`);
      });
  });

  // Lecteur audio
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

    audio.addEventListener('ended', function() {
      playIcon.style.display = 'block';
      pauseIcon.style.display = 'none';
      playButton.setAttribute('aria-label', 'Lecture');
    });
  }

  // Barre de progression pour téléchargements
  function initDownloadProgress() {
    const downloadLinks = document.querySelectorAll('.download-link');
    downloadLinks.forEach(link => {
      link.addEventListener('click', function() {
        const progressBar = this.parentElement.querySelector('.progress-bar');
        if (progressBar) {
          progressBar.classList.add('active');
          setTimeout(() => {
            progressBar.classList.remove('active');
          }, 2000);
        }
      });
    });
  }
});
