/**
 * Gestion de la page de profil utilisateur
 */

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ profile.js chargé");
    
    // ===================== MISE À JOUR DU PROFIL =====================
    const profileForm = document.getElementById("profile-form");
    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            // ✅ BLOQUER TOUTE SOUMISSION CLASSIQUE
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log("🔄 Soumission du formulaire profil");
            
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = "⏳ Mise à jour...";
            
            const formData = new FormData(profileForm);
            
            // Récupérer les valeurs pour mise à jour dynamique
            const newFirstname = formData.get('firstname');
            const newAge = formData.get('age');
            const newEmail = formData.get('email');
            const newAvatarId = formData.get('avatar');
            
            try {
                const response = await fetch("?route=updateProfile", {
                    method: "POST",
                    body: formData
                }
    
    /**
     * Met à jour l'affichage des informations actuelles
     */);
                
                const result = await response.json();
                console.log("📥 Réponse reçue:", result);
                
                // Trouver ou créer la zone de message
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                if (result.success) {
                    // ✅ Succès
                    messageDiv.className = "profile-message alert alert-success";
                    messageDiv.textContent = "✅ " + result.message;
                    messageDiv.style.display = "block";
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = "✅ Mis à jour !";
                    submitBtn.style.backgroundColor = "#28a745";
                    submitBtn.style.color = "white";
                    
                    // Mise à jour dynamique de l'affichage
                    updateDisplayedInfo(newFirstname, newAge, newEmail, newAvatarId);
                    
                    // Restaurer le bouton après 3 secondes
                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.style.backgroundColor = "";
                        submitBtn.style.color = "";
                        messageDiv.style.display = "none";
                    }, 3000);
                    
                } else {
                    // ❌ Erreur
                    messageDiv.className = "profile-message alert alert-danger";
                    messageDiv.textContent = "❌ " + result.message;
                    messageDiv.style.display = "block";
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                
            } catch (err) {
                console.error("❌ Exception:", err);
                
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                messageDiv.className = "profile-message alert alert-danger";
                messageDiv.textContent = "❌ Erreur: " + err.message;
                messageDiv.style.display = "block";
                
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    function updateDisplayedInfo(firstname, age, email, avatarId) {
        console.log("🔄 Mise à jour de l'affichage");
        
        // Mettre à jour le prénom
        const firstnameDisplay = document.querySelector('.container-profile .form-group:nth-child(2) p');
        if (firstnameDisplay) {
            firstnameDisplay.textContent = firstname;
        }
        
        // Mettre à jour l'âge
        const ageDisplay = document.querySelector('.container-profile .form-group:nth-child(3) p');
        if (ageDisplay) {
            ageDisplay.textContent = age + " ans";
        }
        
        // Mettre à jour l'email
        const emailDisplay = document.querySelector('.container-profile .form-group:nth-child(1) p');
        if (emailDisplay) {
            emailDisplay.textContent = email;
        }
        
        // Mettre à jour l'avatar
        const avatarImg = document.querySelector('.container-profile .avatar');
        const selectedAvatarInput = document.querySelector(`input[name="avatar"][value="${avatarId}"]`);
        
        if (avatarImg && selectedAvatarInput) {
            const selectedLabel = selectedAvatarInput.nextElementSibling;
            const selectedImg = selectedLabel ? selectedLabel.querySelector('img') : null;
            
            if (selectedImg) {
                avatarImg.src = selectedImg.src;
                avatarImg.alt = selectedImg.alt;
            }
        }
    }
    
    // ===================== RÉINITIALISATION MOT DE PASSE =====================
    // Fonction globale pour la réinitialisation (appelée par onclick)
    window.resetPasswordProfile = function(userId) {
        console.log("🔒 Réinitialisation mot de passe pour user:", userId);
        
        // Confirmation
        const confirmMsg = "⚠️ ATTENTION ⚠️\n\n" +
            "Ceci va :\n" +
            "- Générer un nouveau mot de passe aléatoire\n" +
            "- Vous envoyer ce mot de passe par email\n" +
            "- Vous déconnecter automatiquement dans 10 secondes\n\n" +
            "Continuer ?";
        
        if (!confirm(confirmMsg)) {
            return;
        }
        
        const btn = document.getElementById(`resetPasswordBtn-${userId}`);
        const msg = document.getElementById(`resetPasswordMsg-${userId}`);
        const csrfToken = document.getElementById(`csrf-token-${userId}`).value;

        if (!btn || !msg) {
            console.error("❌ Éléments introuvables!");
            return;
        }

        // Désactiver le bouton
        btn.disabled = true;
        btn.textContent = "⏳ Réinitialisation en cours...";
        msg.textContent = "";
        msg.style.display = "none";

        // Préparer les données
        const formData = new URLSearchParams();
        formData.append('id', userId);
        formData.append('csrf_token', csrfToken);

        // Envoyer la requête
        fetch('?route=resetPasswordFromProfile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        })
        .then(response => {
            console.log("📡 Response status:", response.status);
            
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log("📦 Données reçues:", data);
            
            if (data.success) {
                // ✅ Succès
                btn.textContent = "✅ Mot de passe réinitialisé";
                btn.style.backgroundColor = "#28a745";
                btn.style.color = "white";
                msg.textContent = "✅ " + data.message + " Déconnexion dans 10 secondes...";
                msg.className = "reset-message alert alert-success";
                msg.style.display = "block";
                
                // Désactiver définitivement le bouton
                btn.disabled = true;
                
                // Compteur de déconnexion
                let countdown = 10;
                const countdownInterval = setInterval(() => {
                    countdown--;
                    msg.textContent = `✅ ${data.message} Déconnexion dans ${countdown} seconde${countdown > 1 ? 's' : ''}...`;
                    
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = '?route=logout';
                    }
                }, 1000);
                
            } else {
                // ❌ Erreur
                btn.textContent = "🔒 Réinitialiser mon mot de passe";
                btn.disabled = false;
                msg.textContent = "❌ " + (data.error || data.message || "Erreur lors de la réinitialisation.");
                msg.className = "reset-message alert alert-danger";
                msg.style.display = "block";
            }
        })
        .catch(error => {
            console.error('❌ Erreur resetPassword:', error);
            btn.textContent = "🔒 Réinitialiser mon mot de passe";
            btn.disabled = false;
            msg.textContent = "❌ Erreur de connexion au serveur.";
            msg.className = "reset-message alert alert-danger";
            msg.style.display = "block";
        });
    };
    
    // ===================== CHARGEMENT FORMULAIRE CONTACT =====================
    const openContactBtn = document.getElementById("open-contact");
    const contactContainer = document.getElementById("contact-form-container");

    if (openContactBtn && contactContainer) {
        openContactBtn.addEventListener("click", () => {
            console.log("💬 Bouton contact cliqué");

            // Toggle : si déjà ouvert, fermer
            if (contactContainer.innerHTML.trim() !== "") {
                contactContainer.innerHTML = "";
                openContactBtn.textContent = "💬 Ouvrir le formulaire de contact";
                return;
            }

            // ✅ Récupérer les infos du user depuis les variables globales
            const firstname = window.userFirstname || "";
            const email = window.userEmail || "";
            const csrfToken = window.csrfToken || "";

            console.log("📋 Données user:", { firstname, email });

            // ✅ Injecter le formulaire avec les champs préremplis
            contactContainer.innerHTML = `
                <form id="contact-form" method="POST" class="contact-form-inline">
                    <div class="form-group">
                        <label for="contact-firstname">Prénom <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="contact-firstname" 
                            name="firstname" 
                            value="${firstname}" 
                            required
                            minlength="2"
                            maxlength="60">
                    </div>

                    <div class="form-group">
                        <label for="contact-email">Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="contact-email" 
                            name="email" 
                            value="${email}" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="contact-subject">Sujet <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="contact-subject" 
                            name="subject" 
                            required
                            minlength="3"
                            maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="contact-content">Message <span class="required">*</span></label>
                        <textarea 
                            id="contact-content" 
                            name="content" 
                            rows="5" 
                            required
                            minlength="10"></textarea>
                    </div>

                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    
                    <button type="submit" class="btn btn-primary btn-profile">
                        📨 Envoyer le message
                    </button>
                    
                    <div class="contact-message" style="display: none; margin-top: 10px;"></div>
                </form>
            `;

            openContactBtn.textContent = "❌ Fermer le formulaire";

            // ✅ Listener sur le formulaire injecté
            const contactForm = document.getElementById("contact-form");
            contactForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                console.log("📨 Envoi du formulaire contact");

                const submitBtn = contactForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = "⏳ Envoi en cours...";

                const formData = new FormData(contactForm);

                try {
                    const res = await fetch("?route=contactFromProfile", {
                        method: "POST",
                        body: formData
                    });

                    console.log("📡 Réponse status:", res.status);

                    const result = await res.json();
                    console.log("📦 Réponse data:", result);

                    const messageDiv = contactForm.querySelector('.contact-message');

                    if (result.success) {
                        // ✅ Succès
                        messageDiv.className = "contact-message alert alert-success";
                        messageDiv.textContent = "✅ " + result.message;
                        messageDiv.style.display = "block";
                        
                        // Vider le formulaire (sauf firstname et email)
                        document.getElementById('contact-subject').value = "";
                        document.getElementById('contact-content').value = "";
                        
                        submitBtn.textContent = "✅ Message envoyé !";
                        submitBtn.style.backgroundColor = "#28a745";
                        submitBtn.style.color = "white";
                        
                        // Fermer le formulaire après 3 secondes
                        setTimeout(() => {
                            contactContainer.innerHTML = "";
                            openContactBtn.textContent = "💬 Ouvrir le formulaire de contact";
                        }, 3000);
                        
                    } else {
                        // ❌ Erreur
                        messageDiv.className = "contact-message alert alert-danger";
                        messageDiv.textContent = "❌ " + (result.message || result.error || "Erreur lors de l'envoi");
                        messageDiv.style.display = "block";
                        
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }

                } catch (err) {
                    console.error("❌ Erreur AJAX:", err);
                    
                    const messageDiv = contactForm.querySelector('.contact-message');
                    messageDiv.className = "contact-message alert alert-danger";
                    messageDiv.textContent = "❌ Erreur de connexion au serveur.";
                    messageDiv.style.display = "block";
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    } else {
        console.error("❌ openContactBtn ou contactContainer introuvable");
    }
});