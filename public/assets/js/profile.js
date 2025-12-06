/**
 * Gestion de la page de profil utilisateur
 * - Mise à jour du profil (firstname, age, email, avatar)
 * - Toggle newsletter
 * - Réinitialisation mot de passe
 * - Chargement formulaire de contact
 */

    // ===================== MISE À JOUR DU PROFIL =====================
  /**
 * VERSION SANS RECHARGEMENT
 * Met à jour l'affichage dynamiquement après modification
 */

document.addEventListener("DOMContentLoaded", () => {
    
    console.log("Profile.js chargé");
    
    // ===================== MISE À JOUR DU PROFIL =====================
    const profileForm = document.getElementById("profile-form");
    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            console.log("=== PROFILE JS === Soumission du formulaire profil");
            
            // Désactiver le bouton
            const submitBtn = profileForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = "⏳ Mise à jour...";
            
            // Préparer les données
            const formData = new FormData(profileForm);
            
            // Récupérer les valeurs pour mise à jour dynamique
            const newFirstname = formData.get('firstname');
            const newAge = formData.get('age');
            const newEmail = formData.get('email');
            const newAvatarId = formData.get('avatar');
            
            try {
                console.log("=== PROFILE JS === Envoi de la requête...");
                
                const response = await fetch("?route=updateProfile", {
                    method: "POST",
                    body: formData
                });
                
                console.log("=== PROFILE JS === Réponse reçue:", response.status);
                
                const text = await response.text();
                console.log("=== PROFILE JS === Texte brut:", text);
                
                let result;
                try {
                   .then(response => response.json()) // ← bien parser le JSON
.then(result => {
                    console.log("=== PROFILE JS === JSON parsé:", result);
                } catch (parseError) {
                    console.error("=== PROFILE JS === Erreur parsing JSON:", parseError);
                    throw new Error("Réponse JSON invalide");
                }
                
                // Trouver ou créer la zone de message
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                if (result.success) {
                    // Succès
                    console.log("=== PROFILE JS === Succès!");
                    messageDiv.className = "profile-message alert alert-success";
                    messageDiv.textContent = "✅ " + result.message;
                    messageDiv.style.display = "block";
                    
                    // Mettre à jour le bouton
                    submitBtn.disabled = false;
                    submitBtn.textContent = "✅ Mis à jour !";
                    submitBtn.style.backgroundColor = "#28a745";
                    submitBtn.style.color = "white";
                    
                    // ✅ MISE À JOUR DYNAMIQUE DE L'AFFICHAGE (sans recharger)
                    updateDisplayedInfo(newFirstname, newAge, newEmail, newAvatarId);
                    
                    // Restaurer le bouton après 3 secondes
                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.style.backgroundColor = "";
                        submitBtn.style.color = "";
                        messageDiv.style.display = "none";
                    }, 3000);
                    
                } else {
                    // Erreur
                    console.error("=== PROFILE JS === Erreur:", result.message);
                    messageDiv.className = "profile-message alert alert-danger";
                    messageDiv.textContent = "❌ " + result.message;
                    messageDiv.style.display = "block";
                    
                    // Réactiver le bouton
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                
            } catch (err) {
                console.error("=== PROFILE JS === Exception:", err);
                
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                messageDiv.className = "profile-message alert alert-danger";
                messageDiv.textContent = "❌ Erreur: " + err.message;
                messageDiv.style.display = "block";
                
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    /**
     * Met à jour l'affichage des informations actuelles
     */
    function updateDisplayedInfo(firstname, age, email, avatarId) {
        console.log("=== PROFILE JS === Mise à jour affichage");
        
        // Mettre à jour le prénom affiché
        const firstnameDisplay = document.querySelector('.container-profile .form-group:nth-child(2) p');
        if (firstnameDisplay) {
            firstnameDisplay.textContent = firstname;
            console.log("  - Prénom mis à jour:", firstname);
        }
        
        // Mettre à jour l'âge affiché
        const ageDisplay = document.querySelector('.container-profile .form-group:nth-child(3) p');
        if (ageDisplay) {
            ageDisplay.textContent = age + " ans";
            console.log("  - Âge mis à jour:", age);
        }
        
        // Mettre à jour l'email affiché
        const emailDisplay = document.querySelector('.container-profile .form-group:nth-child(1) p');
        if (emailDisplay) {
            emailDisplay.textContent = email;
            console.log("  - Email mis à jour:", email);
        }
        
        // Mettre à jour l'avatar affiché
        const avatarImg = document.querySelector('.avatar-current');
        const selectedAvatarOption = document.querySelector(`input[name="avatar"][value="${avatarId}"]`);
        
        if (avatarImg && selectedAvatarOption) {
            const selectedLabel = selectedAvatarOption.nextElementSibling;
            const selectedImg = selectedLabel ? selectedLabel.querySelector('img') : null;
            
            if (selectedImg) {
                // Copier l'URL de la miniature vers l'avatar actuel
                // Note: idéalement il faudrait l'URL complète, pas la mini
                avatarImg.src = selectedImg.src;
                console.log("  - Avatar mis à jour");
            }
        }
    }
    
    // ===================== RÉINITIALISATION MOT DE PASSE =====================
   console.log("Profile.js chargé");
    
    // ===================== MISE À JOUR DU PROFIL =====================
    const profileForm = document.getElementById("profile-form");
    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            console.log("=== PROFILE JS === Soumission du formulaire profil");
            
            // Désactiver le bouton
            const submitBtn = profileForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = "⏳ Mise à jour...";
            
            // Préparer les données
            const formData = new FormData(profileForm);
            
            // Debug : afficher les données
            console.log("=== PROFILE JS === Données du formulaire:");
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            try {
                console.log("=== PROFILE JS === Envoi de la requête...");
                
                const response = await fetch("?route=updateProfile", {
                    method: "POST",
                    body: formData
                });
                
                console.log("=== PROFILE JS === Réponse reçue:", response.status, response.statusText);
                console.log("=== PROFILE JS === Content-Type:", response.headers.get('content-type'));
                
                // Lire la réponse en texte d'abord
                const text = await response.text();
                console.log("=== PROFILE JS === Texte brut:", text);
                
                // Essayer de parser le JSON
                let result;
                try {
                    result = JSON.parse(text);
                    console.log("=== PROFILE JS === JSON parsé:", result);
                } catch (parseError) {
                    console.error("=== PROFILE JS === Erreur parsing JSON:", parseError);
                    throw new Error("La réponse n'est pas du JSON valide: " + text.substring(0, 100));
                }
                
                // Trouver ou créer la zone de message
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                if (result.success) {
                    // Succès
                    console.log("=== PROFILE JS === Succès!");
                    messageDiv.className = "profile-message alert alert-success";
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = "block";
                    
                    // Réactiver le bouton
                    submitBtn.disabled = false;
                    submitBtn.textContent = "✅ Mis à jour !";
                    submitBtn.style.backgroundColor = "#28a745";
                    submitBtn.style.color = "white";
                    
                    // Recharger la page après 2 secondes pour voir les changements
                    console.log("=== PROFILE JS === Rechargement dans 2 secondes...");
                    setTimeout(() => {
                        console.log("=== PROFILE JS === Rechargement NOW");
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    // Erreur
                    console.error("=== PROFILE JS === Erreur:", result.message);
                    messageDiv.className = "profile-message alert alert-danger";
                    messageDiv.textContent = result.message;
                    
                    // Réactiver le bouton
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                
                messageDiv.style.display = "block";
                
            } catch (err) {
                console.error("=== PROFILE JS === Exception:", err);
                
                let messageDiv = document.querySelector(".profile-message");
                if (!messageDiv) {
                    messageDiv = document.createElement("div");
                    messageDiv.className = "profile-message";
                    profileForm.appendChild(messageDiv);
                }
                
                messageDiv.className = "profile-message alert alert-danger";
                messageDiv.textContent = "Erreur: " + err.message;
                messageDiv.style.display = "block";
                
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // ===================== RÉINITIALISATION MOT DE PASSE =====================
    const resetPasswordForm = document.querySelector(".reset-password");
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            console.log("Réinitialisation mot de passe demandée");
            
            // Confirmation
            const confirmMsg = "⚠️ ATTENTION ⚠️\n\n" +
                "Ceci va :\n" +
                "- Générer un nouveau mot de passe aléatoire\n" +
                "- Désactiver votre compte (vous devrez vous reconnecter)\n" +
                "- Vous envoyer le nouveau mot de passe par email\n\n" +
                "Continuer ?";
            
            if (!confirm(confirmMsg)) {
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
            
            // Désactiver le bouton
            const submitBtn = resetPasswordForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = "⏳ Réinitialisation...";
            
            // Préparer les données
            const formData = new FormData(resetPasswordForm);
            
            try {
                const response = await fetch("?route=resetPasswordFromProfile", {
                    method: "POST",
                    body: formData
                });
                
                const result = await response.json();
                console.log("Résultat reset password:", result);
                
                if (result.success) {
                    alert(result.message);
                    
                    // Rediriger vers logout
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    alert("Erreur : " + result.message);
                    
                    // Réactiver le bouton
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
                
            } catch (err) {
                console.error("Erreur réinitialisation:", err);
                alert("Erreur de connexion au serveur.");
                
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
    
    // ===================== CHARGEMENT FORMULAIRE CONTACT =====================
    const openContactBtn = document.getElementById("open-contact");
    const contactContainer = document.getElementById("contact-form-container");
    
    if (openContactBtn && contactContainer) {
        openContactBtn.addEventListener("click", async () => {
            console.log("Chargement formulaire de contact");
            
            // Toggle : si déjà ouvert, fermer
            if (contactContainer.innerHTML.trim() !== "") {
                contactContainer.innerHTML = "";
                openContactBtn.textContent = "Ouvrir le formulaire de contact";
                return;
            }
            
            openContactBtn.textContent = "⏳ Chargement...";
            
            try {
                // Charger le formulaire de contact
                const response = await fetch("?route=contactUsForm");
                const html = await response.text();
                
                // Extraire juste le formulaire (pas toute la page)
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formElement = doc.querySelector('#contact-form');
                
                if (formElement) {
                    contactContainer.innerHTML = formElement.outerHTML;
                    openContactBtn.textContent = "Fermer le formulaire";
                    
                    // Ajouter un listener sur le formulaire chargé
                    const contactForm = contactContainer.querySelector("form");
                    if (contactForm) {
                        contactForm.addEventListener("submit", async (e) => {
                            e.preventDefault();
                            
                            console.log("Envoi du message de contact");
                            
                            const submitBtn = contactForm.querySelector('button[type="submit"], input[type="submit"]');
                            if (submitBtn) {
                                submitBtn.disabled = true;
                            }
                            
                            const formData = new FormData(contactForm);
                            
                            try {
                                const res = await fetch("?route=contactUsForm", {
                                    method: "POST",
                                    body: formData
                                });
                                
                                // Vérifier si c'est une redirection
                                if (res.redirected) {
                                    // Message envoyé avec succès, redirection vers homepage
                                    alert("Message envoyé avec succès !");
                                    contactContainer.innerHTML = "";
                                    openContactBtn.textContent = "Ouvrir le formulaire de contact";
                                    return;
                                }
                                
                                const text = await res.text();
                                
                                // Si c'est du JSON
                                try {
                                    const result = JSON.parse(text);
                                    
                                    let feedback = contactContainer.querySelector(".contact-feedback");
                                    if (!feedback) {
                                        feedback = document.createElement("div");
                                        feedback.classList.add("contact-feedback");
                                        contactContainer.appendChild(feedback);
                                    }
                                    
                                    if (result.success) {
                                        feedback.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                                        contactForm.reset();
                                    } else {
                                        feedback.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                                    }
                                } catch (parseError) {
                                    // Pas du JSON, probablement du HTML avec erreurs
                                    alert("Message envoyé !");
                                    contactContainer.innerHTML = "";
                                    openContactBtn.textContent = "Ouvrir le formulaire de contact";
                                }
                                
                            } catch (err) {
                                console.error("Erreur envoi message:", err);
                                alert("Erreur lors de l'envoi du message.");
                            } finally {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                }
                            }
                        });
                    }
                } else {
                    throw new Error("Formulaire de contact introuvable");
                }
                
            } catch (err) {
                console.error("Erreur chargement formulaire contact:", err);
                contactContainer.innerHTML = '<div class="alert alert-danger">Erreur de chargement du formulaire.</div>';
                openContactBtn.textContent = "Ouvrir le formulaire de contact";
            }
        });
    }
});