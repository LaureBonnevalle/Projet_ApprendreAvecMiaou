document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addAvatarForm');
    const urlInput = document.getElementById('url');
    const previewImg = document.getElementById('avatar-preview-img');
    let isSubmitting = false; // ✨ Pour éviter la double soumission

    // 🖼️ Prévisualisation de l'image
    if (urlInput && previewImg) {
        urlInput.addEventListener('input', function () {
            const url = urlInput.value.trim();
            if (url) {
                previewImg.src = url;
                previewImg.onload = () => previewImg.style.display = 'block';
                previewImg.onerror = () => {
                    previewImg.style.display = 'none';
                    showAlert('error', 'Image introuvable ou chemin invalide');
                };
            } else {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
        });
    }

    // ✅ Version alternative - Validation au clic du bouton plutôt qu'à la soumission
    if (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.addEventListener('click', function (e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                e.preventDefault();

                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                const requiredFields = ['name', 'url', 'description', 'caracteristique', 'qualite'];

                // Validation des champs
                for (const field of requiredFields) {
                    if (!data[field] || !data[field].trim()) {
                        showAlert('error', `Le champ "${field}" est requis.`);
                        return false;
                    }
                }

                // UNE SEULE confirmation
                const confirmed = confirm('Confirmer l\'ajout de cet avatar ?');
                if (!confirmed) return false;

                // Marquer comme en cours de soumission
                isSubmitting = true;
                
                // Feedback visuel
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout en cours...';

                // Soumettre le formulaire
                form.submit();
            });
        }
    }

    // 🗑️ Suppression d'un avatar avec effet visuel
    window.deleteAvatar = function (id) {
        const confirmed = confirm('Êtes-vous sûr de vouloir supprimer cet avatar ?');
        if (!confirmed) return;

        // Optionnel : effet visuel avant suppression
        const card = document.querySelector(`.avatar-card[data-id='${id}']`);
        if (card) {
            card.style.transition = 'opacity 0.3s ease';
            card.style.opacity = '0.3';
            setTimeout(() => card.remove(), 500);
        }
        showAlert('success', 'Avatar supprimé avec succès.');
    };

    // ✨ Animation d'apparition des cartes avatar
    const cards = document.querySelectorAll('.avatar-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// 🔔 Affichage des alertes
function showAlert(type, message) {
    const alertId = type === 'success' ? 'success-alert' : 'error-alert';
    const alert = document.getElementById(alertId);
    const messageSpan = alert?.querySelector('span');
    
    if (!alert || !messageSpan) return;
    
    messageSpan.textContent = message;
    alert.style.display = 'flex';
    
    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}