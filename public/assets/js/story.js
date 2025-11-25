document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#histoire-form');
    const storySection = document.querySelector('#story-section');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
       
        const personnageId = document.querySelector('select[name="personnage_id"]').value;
        const lieuId = document.querySelector('select[name="lieu_id"]').value;
        const objetId = document.querySelector('select[name="objet_id"]').value;

        fetch(`/index.php?route=getHistoire&personnage_id=${personnageId}&lieu_id=${lieuId}&objet_id=${objetId}`)
            .then(response => response.json())
            .then(histoire => {
                storySection.innerHTML = `
                    <div>
                        <h2>${histoire.titre}</h2>
                        <p>${histoire.texte}</p>
                    </div>
                    <div>
                        <img src="${histoire.image_url}" alt="Image">
                    </div>
                    <div>
                        <a href="${histoire.pdf_url}" download>Télécharger</a>
                        <div class="progress-bar" id="progress-bar"></div>
                    </div>
                    <div>
                        <button id="play-audio">Play</button>
                        <audio id="audio" src="${histoire.audio_url}"></audio>
                    </div>
                `;

                const playButton = document.getElementById('play-audio');
                const audio = document.getElementById('audio');

                playButton.addEventListener('click', function() {
                    if (audio.paused) {
                        audio.play();
                    } else {
                        audio.pause();
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    });
});