/**
 * Admin User Management System
 * Système de gestion des utilisateurs administrateur
 * 
 * Features / Fonctionnalités :
 * - Password reset / Réinitialisation de mot de passe
 * - User status toggle / Basculement du statut utilisateur
 * - Role management / Gestion des rôles
 * - Avatar update / Mise à jour d'avatar
 * - Newsletter subscription / Gestion de l'abonnement newsletter
 */

// ===== CONFIGURATION / CONFIGURATION =====

// Initialize routes object if not exists
// Initialiser l'objet routes s'il n'existe pas
if (typeof routes === 'undefined') {
    var routes = {};
}

// Define API routes / Définir les routes API
routes.resetPassword = routes.resetPassword || 'index.php?route=resetPassword';
routes.updateStatus = routes.updateStatus || 'index.php?route=updateStatus';
routes.updateRole = routes.updateRole || 'index.php?route=updateRole';
routes.updateAvatar = routes.updateAvatar || 'index.php?route=updateUserAvatar';
routes.updateNewsletter = routes.updateNewsletter || 'index.php?route=updateNewsletter';

// ===== UTILITY FUNCTIONS / FONCTIONS UTILITAIRES =====

/**
 * Disables a button with loading state
 * Désactive un bouton avec état de chargement
 * 
 * @param {HTMLElement} button - Button element / Élément bouton
 * @param {string} loadingText - Loading text / Texte de chargement
 */
function setButtonLoading(button, loadingText = 'Traitement...') {
    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${loadingText}`;
    }
}

/**
 * Restores button to original state
 * Restaure le bouton à son état original
 * 
 * @param {HTMLElement} button - Button element / Élément bouton
 */
function restoreButton(button) {
    if (button && button.dataset.originalText) {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}
/***********************GESTION RESET PASSWORD ADMIN*******************/
/**
 * Réinitialise le mot de passe d'un utilisateur
 * Reset user password
 */
function resetPassword(userId) {
    console.log("=== RESET PASSWORD === User ID:", userId);
    
    // Validation des paramètres
    if (!userId) {
        alert('Erreur : ID utilisateur manquant');
        return;
    }
    
    // Trouver le bouton de réinitialisation
    const button = document.querySelector(`button[data-user-id="${userId}"]`);
    
    console.log("Bouton trouvé:", button);
    
    // Empêcher la double exécution
    if (button && button.disabled) {
        return;
    }
    
    // Dialogue de confirmation
    const confirmMessage = `Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?
    
Ceci va :
- Générer un nouveau mot de passe aléatoire
- Passer le statut à "Inactif"  
- Envoyer le nouveau mot de passe par email`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // État de chargement
    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Réinitialisation...';
    }
    
    // Récupérer le CSRF token
    const csrfTokenElement = document.getElementById(`csrf-token-${userId}`);
    const csrfToken = csrfTokenElement ? csrfTokenElement.value : null;
    
    console.log("CSRF Token:", csrfToken);
    
    if (!csrfToken) {
        alert('Erreur : Token CSRF manquant');
        if (button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText;
        }
        return;
    }
    
    // Préparer les données de la requête
    const formData = new URLSearchParams();
    formData.append('id', userId);
    formData.append('csrf_token', csrfToken);
    
    console.log("Envoi de la requête POST...");
    
    // Requête API
    fetch('?route=resetPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => {
        console.log("Réponse reçue:", response.status, response.statusText);
        
        return response.text().then(text => {
            console.log("Texte brut:", text);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            
            // Vérifier si c'est du HTML au lieu de JSON
            if (text.trim().startsWith('<')) {
                throw new Error('Le serveur a retourné du HTML au lieu de JSON. Vérifiez les logs serveur.');
            }
            
            // Vérifier si la réponse est vide
            if (!text.trim()) {
                throw new Error('Réponse vide du serveur');
            }
            
            // Parser le JSON
            try {
                const data = JSON.parse(text);
                console.log("JSON parsé:", data);
                return data;
            } catch (parseError) {
                console.error("Erreur parsing JSON:", parseError);
                throw new Error('Réponse JSON invalide du serveur: ' + parseError.message);
            }
        });
    })
    .then(data => {
        console.log("Traitement des données:", data);
        
        if (data.success) {
            console.log("✅ SUCCÈS - Mise à jour de l'interface");
            
            // Mettre à jour l'affichage du statut si possible
            try {
                updateStatusDisplay(0);
            } catch (statusError) {
                console.warn("Impossible de mettre à jour le statut:", statusError);
            }
            
            // Message de succès
            const successMessage = data.message || 'Réinitialisation réussie ! Email envoyé à l\'utilisateur.';
            
            // ✅ MISE À JOUR DU BOUTON AVEC LE CHECK
            if (button) {
                button.innerHTML = '<i class="fas fa-check"></i> Mot de passe réinitialisé';
                button.className = button.className.replace('btn-outline-warning', 'btn-success');
                button.disabled = true; // Garder disabled
                
                console.log("Bouton mis à jour:", button.innerHTML);
            }
            
            // Afficher le message sous le bouton
            const msgDiv = document.getElementById(`resetPasswordMsg-${userId}`);
            if (msgDiv) {
                msgDiv.textContent = successMessage;
                msgDiv.style.color = 'green';
                msgDiv.style.display = 'block';
            }
            
            // Alert de confirmation
            alert(successMessage);
            
        } else {
            throw new Error(data.error || 'Échec de la réinitialisation du mot de passe');
        }
    })
    .catch(error => {
        console.error("❌ ERREUR:", error);
        
        // Restaurer le bouton
        if (button && button.dataset.originalText) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText;
        }
        
        // Afficher le message d'erreur
        const msgDiv = document.getElementById(`resetPasswordMsg-${userId}`);
        if (msgDiv) {
            msgDiv.textContent = error.message;
            msgDiv.style.color = 'red';
            msgDiv.style.display = 'block';
        }
        
        // Message d'erreur
        let errorMessage = `Erreur lors de la réinitialisation : ${error.message}`;
        
        if (error.message.includes('HTML')) {
            errorMessage += '\n\nCeci indique une erreur PHP côté serveur.';
        }
        
        alert(errorMessage);
    });
}
// ===== STATUS MANAGEMENT / GESTION DU STATUT =====

/**
 * Toggles user status (active/inactive)
 * Bascule le statut utilisateur (actif/inactif)
 * 
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} newStatus - New status (0=inactive, 1=active) / Nouveau statut
 */
function toggleStatus(userId, newStatus) {
    const action = newStatus == 1 ? 'activer' : 'désactiver';
    const statusText = newStatus == 1 ? 'Actif' : 'Inactif';
    
    if (!confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur ?`)) {
        return;
    }

    const button = document.querySelector(`button[onclick*="toggleStatus(${userId}"]`);
    setButtonLoading(button, 'Traitement...');

    const requestData = {
        user_id: parseInt(userId),
        status: parseInt(newStatus)
    };

    fetch(routes.updateStatus, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
            if (data.success) {
                updateStatusDisplay(newStatus);
                updateStatusButton(button, userId, newStatus);
                alert(`Utilisateur ${action} avec succès !`);
            } else {
                throw new Error(data.error || 'Échec de la mise à jour du statut');
            }
        })
        .catch(error => {
            restoreButton(button);
            alert(`Erreur lors de la mise à jour du statut : ${error.message}`);
        });
}

/**
 * Updates status display elements
 * Met à jour les éléments d'affichage du statut
 * 
 * @param {number} status - New status / Nouveau statut
 */
function updateStatusDisplay(status) {
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.textContent = status == 1 ? 'Actif' : 'Inactif';
        statusBadge.className = `status-badge ${status == 1 ? 'status-active' : 'status-inactive'}`;
    }
}

/**
 * Updates status toggle button
 * Met à jour le bouton de basculement du statut
 * 
 * @param {HTMLElement} button - Button element / Élément bouton
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} currentStatus - Current status / Statut actuel
 */
function updateStatusButton(button, userId, currentStatus) {
    if (button) {
        const nextStatus = currentStatus == 1 ? 0 : 1;
        const buttonText = currentStatus == 1 ? 'Désactiver' : 'Activer';
        const buttonClass = currentStatus == 1 ? 'btn-outline-danger' : 'btn-outline-success';
        
        button.disabled = false;
        button.className = `btn btn-sm ${buttonClass} btn-action`;
        button.innerHTML = buttonText;
        button.setAttribute('onclick', `toggleStatus(${userId}, ${nextStatus})`);
    }
}

// ===== ROLE MANAGEMENT / GESTION DES RÔLES =====

/**
 * Updates user role
 * Met à jour le rôle utilisateur
 * 
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} newRole - New role (1=user, 2=admin) / Nouveau rôle
 */
function updateRole(userId, newRole) {
    const roleText = newRole == 2 ? 'Administrateur' : 'Utilisateur';
    
    if (!confirm(`Êtes-vous sûr de vouloir modifier le rôle en "${roleText}" ?`)) {
        // Restore original value / Restaurer la valeur originale
        restoreRoleSelect(userId);
        return;
    }

    // Show loading message / Afficher le message de chargement
    showRoleMessage(userId, 'Mise à jour...', 'info');

    const requestData = {
        user_id: parseInt(userId),
        role: parseInt(newRole)
    };

    fetch(routes.updateRole, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
            if (data.success) {
                updateRoleDisplay(userId, newRole);
                showRoleMessage(userId, 'Rôle mis à jour avec succès !', 'success');
                alert('Rôle mis à jour avec succès !');
            } else {
                throw new Error(data.error || 'Échec de la mise à jour du rôle');
            }
        })
        .catch(error => {
            restoreRoleSelect(userId);
            showRoleMessage(userId, `Erreur : ${error.message}`, 'danger');
            alert(`Erreur lors de la mise à jour du rôle : ${error.message}`);
        });
}

/**
 * Updates role display elements
 * Met à jour les éléments d'affichage du rôle
 * 
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} role - New role / Nouveau rôle
 */
function updateRoleDisplay(userId, role) {
    const roleBadge = document.getElementById(`role-badge-${userId}`);
    if (roleBadge) {
        roleBadge.textContent = role == 2 ? 'Administrateur' : 'Utilisateur';
        roleBadge.className = `role-badge ${role == 2 ? 'admin' : 'user'}`;
    }
}

/**
 * Shows role update message
 * Affiche le message de mise à jour du rôle
 * 
 * @param {number} userId - User ID / ID utilisateur
 * @param {string} message - Message to display / Message à afficher
 * @param {string} type - Message type (info, success, danger) / Type de message
 */
function showRoleMessage(userId, message, type) {
    const messageDiv = document.getElementById(`role-message-${userId}`);
    if (messageDiv) {
        messageDiv.style.display = 'block';
        messageDiv.innerHTML = `<small class="text-${type}"><i class="fas fa-${type === 'info' ? 'spinner fa-spin' : type === 'success' ? 'check' : 'exclamation-triangle'}"></i> ${message}</small>`;
        
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    }
}

/**
 * Restores role select to original value
 * Restaure le select de rôle à sa valeur originale
 * 
 * @param {number} userId - User ID / ID utilisateur
 */
function restoreRoleSelect(userId) {
    const select = document.getElementById(`role-select-${userId}`);
    const roleBadge = document.getElementById(`role-badge-${userId}`);
    
    if (select && roleBadge) {
        const currentRole = roleBadge.textContent.includes('Administrateur') ? 2 : 1;
        select.value = currentRole;
    }
}

// ===== AVATAR MANAGEMENT / GESTION DES AVATARS =====

/**
 * Updates user avatar (called by HTML onchange event)
 * Met à jour l'avatar utilisateur (appelé par l'événement HTML onchange)
 */
function updateAvatar(selectElement) {
    if (!selectElement || !selectElement.value) {
        console.warn('Select element ou valeur manquante');
        return;
    }
    
    const avatarId = selectElement.value;
    const container = selectElement.closest('div');
    const userIdInput = container?.querySelector('input[name="user_id"]');
    
    if (!userIdInput || !userIdInput.value) {
        alert('Erreur : ID utilisateur non trouvé');
        return;
    }
    
    updateUserAvatar(userIdInput.value, avatarId);
}

/**
 * Updates user avatar via API
 * Met à jour l'avatar utilisateur via API
 */
function updateUserAvatar(userId, avatarId) {
    if (!userId || !avatarId) {
        alert('Erreur : Paramètres manquants');
        return;
    }
    
    const requestData = {
        user_id: parseInt(userId),
        avatar_id: parseInt(avatarId)
    };
    
    // Afficher un indicateur de chargement
    const originalSelectElement = document.querySelector(`input[name="user_id"][value="${userId}"]`)
        ?.closest('div')?.querySelector('select');
    
    if (originalSelectElement) {
        originalSelectElement.disabled = true;
    }
    
    fetch(routes.updateAvatar, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Succès - afficher une notification discrète
            showNotification('Avatar mis à jour avec succès', 'success');
            
            // Optionnel : mettre à jour l'affichage si les éléments existent
            updateAvatarDisplay(data.avatar);
            
        } else {
            throw new Error(data.message || 'Échec de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur mise à jour avatar:', error);
        showNotification(`Erreur: ${error.message}`, 'error');
    })
    .finally(() => {
        // Réactiver le select
        if (originalSelectElement) {
            originalSelectElement.disabled = false;
        }
    });
}

/**
 * Updates avatar display elements (seulement si ils existent)
 * Met à jour les éléments d'affichage de l'avatar
 */
function updateAvatarDisplay(avatarData) {
    if (!avatarData) return;
    
    // Mise à jour de l'image avatar si elle existe
    const avatarImg = document.querySelector('.avatar-display img');
    if (avatarImg && avatarData.url) {
        avatarImg.src = avatarData.url;
        avatarImg.alt = avatarData.description || avatarData.name;
    }
    
    // Mise à jour du nom si l'élément existe
    const avatarName = document.querySelector('.avatar-name');
    if (avatarName && avatarData.name) {
        avatarName.textContent = avatarData.name;
    }
    
    // Mise à jour de la description si l'élément existe
    const avatarDescription = document.querySelector('.avatar-description');
    if (avatarDescription && avatarData.description) {
        avatarDescription.textContent = avatarData.description;
    }
}

/**
 * Affiche une notification temporaire
 */
function showNotification(message, type = 'info') {
    // Vérifier si une notification existe déjà
    const existingNotification = document.querySelector('.avatar-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `avatar-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Supprimer automatiquement après 3 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Ajouter les styles CSS pour l'animation
if (!document.querySelector('#avatar-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'avatar-notification-styles';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

// ===== NEWSLETTER MANAGEMENT / GESTION NEWSLETTER =====

/**
 * Toggles newsletter subscription
 * Bascule l'abonnement newsletter
 * 
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} newNewsletter - New subscription status / Nouveau statut d'abonnement
 */
function toggleNewsletter(userId, newNewsletter) {
    const action = newNewsletter == 1 ? 'abonner' : 'désabonner';
    const newsletterText = newNewsletter == 1 ? 'Abonné' : 'Non abonné';

    if (!confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur à la newsletter ?`)) {
        return;
    }

    const button = document.querySelector(`button[onclick*="toggleNewsletter(${userId}"]`);
    setButtonLoading(button, 'Traitement...');

    const requestData = {
        user_id: parseInt(userId),
        newsletter: parseInt(newNewsletter)
    };

    fetch(routes.updateNewsletter, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
            if (data.success) {
                updateNewsletterDisplay(newNewsletter);
                updateNewsletterButton(button, userId, newNewsletter);
                alert(`Utilisateur ${action} avec succès !`);
            } else {
                throw new Error(data.error || 'Échec de la mise à jour de la newsletter');
            }
        })
        .catch(error => {
            restoreButton(button);
            alert(`Erreur lors de la mise à jour de la newsletter : ${error.message}`);
        });
}

/**
 * Updates newsletter display elements
 * Met à jour les éléments d'affichage de la newsletter
 * 
 * @param {number} newsletter - Newsletter status / Statut newsletter
 */
function updateNewsletterDisplay(newsletter) {
    const newsletterBadge = document.querySelector('.newsletter-badge');
    if (newsletterBadge) {
        newsletterBadge.textContent = newsletter == 1 ? 'Abonné' : 'Non abonné';
        newsletterBadge.className = `newsletter-badge ${newsletter == 1 ? 'newsletter-active' : 'newsletter-inactive'}`;
    }
}

/**
 * Updates newsletter toggle button
 * Met à jour le bouton de basculement de la newsletter
 * 
 * @param {HTMLElement} button - Button element / Élément bouton
 * @param {number} userId - User ID / ID utilisateur
 * @param {number} currentNewsletter - Current newsletter status / Statut newsletter actuel
 */
function updateNewsletterButton(button, userId, currentNewsletter) {
    if (button) {
        const nextNewsletter = currentNewsletter == 1 ? 0 : 1;
        const buttonText = currentNewsletter == 1 ? 'Désabonner' : 'Abonner';
        const buttonClass = currentNewsletter == 1 ? 'btn-outline-danger' : 'btn-outline-success';
        
        button.disabled = false;
        button.className = `btn btn-sm ${buttonClass} btn-action`;
        button.innerHTML = buttonText;
        button.setAttribute('onclick', `toggleNewsletter(${userId}, ${nextNewsletter})`);
    }
}

// ===== INITIALIZATION / INITIALISATION =====

/**
 * Initialize the admin panel when DOM is ready
 * Initialise le panneau d'administration quand le DOM est prêt
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin User Management System initialized / Système de gestion des utilisateurs initialisé');
    
    // Initialize password reset button / Initialiser le bouton de réinitialisation
    const resetBtn = document.getElementById('resetPasswordBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                resetPassword(userId);
            }
        });
    }
    
    // Log available elements for debugging / Enregistrer les éléments disponibles pour le débogage
    const elements = {
        resetButton: !!resetBtn,
        statusButton: !!document.querySelector('button[onclick*="toggleStatus"]'),
        statusBadge: !!document.querySelector('.status-badge'),
        roleSelects: document.querySelectorAll('select[id^="role-select-"]').length,
        newsletterButton: !!document.querySelector('button[onclick*="toggleNewsletter"]'),
        newsletterBadge: !!document.querySelector('.newsletter-badge')
    };
    
    console.log('Available elements / Éléments disponibles:', elements);
});