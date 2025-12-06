/**
 * Réinitialise le mot de passe d'un utilisateur
 * @param {number} userId - L'ID de l'utilisateur
 */
function resetPassword(userId) {
    console.log("=== RESET PASSWORD === User ID:", userId); // DEBUG
    
    const btn = document.getElementById(`resetPasswordBtn-${userId}`);
    const msg = document.getElementById(`resetPasswordMsg-${userId}`);
    const csrfToken = document.getElementById(`csrf-token-${userId}`).value;

    console.log("Bouton trouvé:", btn); // DEBUG
    console.log("Message div trouvé:", msg); // DEBUG
    console.log("CSRF Token:", csrfToken); // DEBUG

    if (!btn || !msg) {
        console.error("ERREUR: Éléments introuvables!");
        return;
    }

    // Désactiver le bouton pendant le traitement
    btn.disabled = true;
    btn.textContent = "⏳ Réinitialisation en cours...";
    msg.textContent = "";
    msg.style.display = "none";

    // Préparer les données
    const formData = new URLSearchParams();
    formData.append('id', userId);
    formData.append('csrf_token', csrfToken);

    // Envoyer la requête
    fetch('?route=resetPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status); // DEBUG
        console.log("Response headers:", response.headers.get('content-type')); // DEBUG
        
        if (!response.ok) {
            throw new Error('Erreur HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("Données reçues:", data); // DEBUG - IMPORTANT
        
        if (data.success) {
            // Succès
            btn.textContent = "✅ Mot de passe réinitialisé";
            btn.style.backgroundColor = "#28a745";
            msg.textContent = data.message;
            msg.style.color = "green";
            msg.style.display = "block";
            
            // Désactiver définitivement le bouton
            btn.disabled = true;
            
        } else {
            // Erreur
            btn.textContent = "Réinitialiser le mot de passe";
            btn.disabled = false;
            msg.textContent = data.error || "Erreur lors de la réinitialisation.";
            msg.style.color = "red";
            msg.style.display = "block";
        }
    })
    .catch(error => {
        console.error('Erreur resetPassword:', error);
        btn.textContent = "Réinitialiser le mot de passe";
        btn.disabled = false;
        msg.textContent = "Erreur de connexion au serveur.";
        msg.style.color = "red";
        msg.style.display = "block";
    });
}





/*// --- Toggle Newsletter ---
// --- Toggle Newsletter ---
function toggleNewsletter(userId, nextState) {
    const formData = new URLSearchParams();
    formData.append('user_id', userId);
    formData.append('newsletter', nextState);

    fetch('?route=updateNewsletter', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId,
            newsletter: nextState
        })
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById(`newsletter-badge-${userId}`);
        const btn = document.getElementById(`newsletter-button-${userId}`);
        const msg = document.getElementById(`newsletter-message-${userId}`);

        if (!data.success) {
            msg.textContent = data.error || "Erreur lors de la mise à jour.";
            msg.style.color = "red";
            msg.style.display = "block";
            return;
        }

        // Mise à jour badge
        badge.classList.toggle("active", data.new_newsletter === 1);
        badge.classList.toggle("inactive", data.new_newsletter === 0);
        badge.textContent = data.newsletter_text;

        // Mise à jour bouton
        btn.textContent = data.new_newsletter === 1 ? "Désabonner" : "Abonner";
        btn.setAttribute("onclick", `toggleNewsletter(${userId}, ${data.new_newsletter === 1 ? 0 : 1})`);

        // Message de feedback
        msg.textContent = data.message || "";
        msg.style.color = "green";
        msg.style.display = "block";
    })
    .catch(err => {
        console.error("Erreur newsletter:", err);
        const msg = document.getElementById(`newsletter-message-${userId}`);
        msg.textContent = "Erreur de connexion au serveur.";
        msg.style.color = "red";
        msg.style.display = "block";
    });
}


// --- Toggle Statut ---
function toggleStatus(userId, nextState) {
    const formData = new URLSearchParams();
    formData.append('user_id', userId);
    formData.append('statut', nextState);

    fetch('?route=updateStatus', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId,
            statut: nextState
        })
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById(`status-badge-${userId}`);
        const btn = document.getElementById(`status-button-${userId}`);
        const msg = document.getElementById(`status-message-${userId}`);

        if (!data.success) {
            msg.textContent = data.error || "Erreur lors de la mise à jour.";
            msg.style.color = "red";
            msg.style.display = "block";
            return;
        }

        // Mise à jour badge
        badge.classList.toggle("active", data.new_statut === 1);
        badge.classList.toggle("inactive", data.new_statut === 0);
        badge.textContent = data.statut_text;

        // Mise à jour bouton
        btn.textContent = data.new_statut === 1 ? "Désactiver" : "Activer";
        btn.setAttribute("onclick", `toggleStatus(${userId}, ${data.new_statut === 1 ? 0 : 1})`);

        // Message de feedback
        msg.textContent = data.message || "";
        msg.style.color = "green";
        msg.style.display = "block";
    })
    .catch(err => {
        console.error("Erreur statut:", err);
        const msg = document.getElementById(`status-message-${userId}`);
        msg.textContent = "Erreur de connexion au serveur.";
        msg.style.color = "red";
        msg.style.display = "block";
    });
}
}*/
