document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Coloring JS chargé');
    
    const categorieSelect = document.getElementById('categorie-select');
    const coloringsList = document.getElementById('colorings-list');
    const previewSection = document.getElementById('preview-section');

    if (!categorieSelect || !coloringsList || !previewSection) {
        console.error('❌ Éléments manquants dans le DOM');
        return;
    }

    // 🎯 Changement de catégorie
    categorieSelect.addEventListener('change', function() {
        const categorieId = this.value;
        
        // Réinitialiser
        coloringsList.innerHTML = '';
        previewSection.innerHTML = '<p class="placeholder-text">Chargement...</p>';

        if (!categorieId) {
            previewSection.innerHTML = '<p class="placeholder-text">👆 Sélectionnez une catégorie</p>';
            return;
        }

        console.log('📂 Catégorie sélectionnée:', categorieId);

        // Appel AJAX pour récupérer les coloriages
        fetch('?route=coloringsListe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: categorieId })
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(colorings => {
            console.log('✅ Coloriages reçus:', colorings);
            displayColoringsList(colorings);
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            coloringsList.innerHTML = '<li class="error">Erreur de chargement</li>';
            previewSection.innerHTML = '<p class="error">⚠️ Impossible de charger les coloriages</p>';
        });
    });

    /**
     * Affiche la liste des coloriages
     */
    function displayColoringsList(colorings) {
        coloringsList.innerHTML = '';

        if (!colorings || colorings.length === 0) {
            coloringsList.innerHTML = '<li class="empty">Aucun coloriage disponible</li>';
            previewSection.innerHTML = '<p class="placeholder-text">😕 Aucun coloriage dans cette catégorie</p>';
            return;
        }

        colorings.forEach(coloring => {
            const li = document.createElement('li');
            li.classList.add('coloring-item');
            li.textContent = coloring.name || 'Sans nom';

            // ✅ Aperçu au survol
            li.addEventListener('mouseover', function() {
                showPreview(coloring);
            });

            coloringsList.appendChild(li);
        });

        // ✅ Afficher automatiquement le premier coloriage
        showPreview(colorings[0]);
    }

    /**
     * Affiche l'aperçu du coloriage avec bouton de téléchargement
     */
    function showPreview(coloring) {
        console.log('🖼️ Affichage aperçu:', coloring.name);

        const pdfUrl = coloring.url;
        const thumbnailUrl = coloring.thumbnail_url; // ✅ on utilise le PNG
        const downloadFilename = (coloring.name || 'coloriage').replace(/[^a-z0-9]/gi, '_') + '.pdf';

        previewSection.innerHTML = `
            <div class="preview-content">
                <h3 class="preview-title">${coloring.name}</h3>
                
                <div class="preview-image-container">
                    <img 
                        src="${thumbnailUrl}" 
                        alt="Aperçu de ${coloring.name}" 
                        class="coloring-thumbnail">
                </div>

                <div class="download-container">
                    <a 
                        href="${pdfUrl}" 
                        download="${downloadFilename}"
                        class="download-button"
                        title="Télécharger le coloriage">
                        <img 
                            src="assets/img/Miaou/Telechargement.svg" 
                            alt="Télécharger"
                            class="download-icon">
                        <span>Télécharger</span>
                    </a>
                </div>
            </div>
        `;
    }
});
