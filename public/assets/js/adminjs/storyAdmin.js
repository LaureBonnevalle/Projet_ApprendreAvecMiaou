/**
 * StoryAdmin.js - Script d'administration pour la gestion des histoires
 * Gère l'interface utilisateur et les interactions du tableau de bord administrateur
 */

class StoryAdmin {
    constructor() {
        this.modeManager = null;
        this.init();
    }

    /**
     * Initialise les fonctionnalités du script
     */
    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupAutoFocus();
        this.setupProgressAnimation();
        this.setupTextareaCounters();
        this.setupModeManager(); // Nouvelle méthode pour le gestionnaire de modes
        this.setupAutoSave();
        this.setupKeyboardShortcuts();
    }

    /**
     * Configure le gestionnaire de modes de saisie
     */
    setupModeManager() {
        // Configuration du gestionnaire de modes
        this.modeManager = {
            elements: {
                toggleSingleBtn: document.getElementById('toggle-single-mode'),
                toggleMultipleBtn: document.getElementById('toggle-multiple-mode'),
                singleFormsDiv: document.getElementById('single-stories-forms'),
                multipleFormDiv: document.getElementById('multiple-stories-form'),
                multipleForm: document.getElementById('form-multiple-stories')
            },
            currentMode: 'single',
            
            init: () => {
                if (this.validateModeElements()) {
                    this.setupModeEventListeners();
                    this.setInitialModeState();
                    this.setupMultipleFormValidation();
                }
            },
            
            switchToSingle: () => {
                this.switchToSingleMode();
            },
            
            switchToMultiple: () => {
                this.switchToMultipleMode();
            }
        };
        
        this.modeManager.init();
    }

    /**
     * Valide que tous les éléments nécessaires pour les modes sont présents
     */
    validateModeElements() {
        const elements = this.modeManager.elements;
        const requiredElements = ['toggleSingleBtn', 'toggleMultipleBtn', 'singleFormsDiv', 'multipleFormDiv'];
        
        for (const elementName of requiredElements) {
            if (!elements[elementName]) {
                console.warn(`StoryAdmin: Élément ${elementName} non trouvé - gestionnaire de modes désactivé`);
                return false;
            }
        }
        return true;
    }

    /**
     * Configure les écouteurs d'événements pour les modes
     */
    setupModeEventListeners() {
        const elements = this.modeManager.elements;
        
        elements.toggleSingleBtn.addEventListener('click', () => {
            this.switchToSingleMode();
        });
        
        elements.toggleMultipleBtn.addEventListener('click', () => {
            this.switchToMultipleMode();
        });
    }

    /**
     * Définit l'état initial (mode single actif)
     */
    setInitialModeState() {
        this.switchToSingleMode();
    }

    /**
     * Bascule vers le mode "une par une"
     */
    switchToSingleMode() {
        const elements = this.modeManager.elements;
        
        // Affichage des conteneurs
        elements.singleFormsDiv.style.display = 'block';
        elements.multipleFormDiv.style.display = 'none';
        
        // Mise à jour des styles des boutons
        elements.toggleSingleBtn.className = 'btn btn-primary';
        elements.toggleMultipleBtn.className = 'btn btn-secondary';
        
        // Mise à jour du texte des boutons
        elements.toggleSingleBtn.innerHTML = '🔄 Mode : Une par une (actif)';
        elements.toggleMultipleBtn.innerHTML = '📚 Mode : Toutes en même temps';
        
        this.modeManager.currentMode = 'single';
        
        // Notification du changement
        this.onModeChanged('single');
    }

    /**
     * Bascule vers le mode "toutes en même temps"
     */
    switchToMultipleMode() {
        const elements = this.modeManager.elements;
        
        // Affichage des conteneurs
        elements.singleFormsDiv.style.display = 'none';
        elements.multipleFormDiv.style.display = 'block';
        
        // Mise à jour des styles des boutons
        elements.toggleSingleBtn.className = 'btn btn-secondary';
        elements.toggleMultipleBtn.className = 'btn btn-primary';
        
        // Mise à jour du texte des boutons
        elements.toggleSingleBtn.innerHTML = '🔄 Mode : Une par une';
        elements.toggleMultipleBtn.innerHTML = '📚 Mode : Toutes en même temps (actif)';
        
        this.modeManager.currentMode = 'multiple';
        
        // Notification du changement
        this.onModeChanged('multiple');
    }

    /**
     * Callback appelé lors du changement de mode
     */
    onModeChanged(mode) {
        console.log(`Mode changé vers: ${mode}`);
        
        // Remettre les compteurs et validations à jour pour le nouveau mode
        setTimeout(() => {
            this.setupTextareaCounters();
        }, 100);
        
        // Émettre un événement personnalisé
        const event = new CustomEvent('storyModeChanged', {
            detail: { mode: mode, timestamp: Date.now() }
        });
        document.dispatchEvent(event);
    }

    /**
     * Configure la validation du formulaire multiple
     */
    setupMultipleFormValidation() {
        const multipleForm = this.modeManager.elements.multipleForm;
        if (!multipleForm) {
            return;
        }
        
        multipleForm.addEventListener('submit', (e) => {
            if (!this.validateMultipleFormSubmission(multipleForm)) {
                e.preventDefault();
            }
        });
    }

    /**
     * Valide la soumission du formulaire multiple
     */
    validateMultipleFormSubmission(form) {
        const textareas = form.querySelectorAll('textarea[required]');
        let emptyCount = 0;
        const emptyIndices = [];
        
        textareas.forEach((textarea, index) => {
            if (!textarea.value.trim()) {
                emptyCount++;
                emptyIndices.push(index + 1);
            }
        });
        
        // Vérification des champs vides
        if (emptyCount > 0) {
            const message = `Attention : ${emptyCount} histoire(s) sont vides (n° ${emptyIndices.join(', ')}).\nVoulez-vous vraiment continuer ?`;
            if (!confirm(message)) {
                // Focus sur la première histoire vide
                const firstEmptyTextarea = Array.from(textareas).find(ta => !ta.value.trim());
                if (firstEmptyTextarea) {
                    firstEmptyTextarea.focus();
                    firstEmptyTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        }
        
        // Confirmation finale
        const totalStories = textareas.length;
        return confirm(`Vous allez ajouter ${totalStories} histoires. Confirmer ?`);
    }

    /**
     * Retourne le mode actuellement actif
     */
    getCurrentMode() {
        return this.modeManager ? this.modeManager.currentMode : null;
    }

    /**
     * Configure les écouteurs d'événements
     */
    setupEventListeners() {
        // Confirmation avant soumission de plusieurs histoires (mode single)
        const storyForm = document.querySelector('form[action*="process_add_stories"]');
        if (storyForm) {
            storyForm.addEventListener('submit', (e) => this.handleStoryFormSubmit(e));
        }

        // Gestion du formulaire d'ajout d'élément
        const addForm = document.querySelector('form[action*="admin_dashboard"]');
        if (addForm) {
            addForm.addEventListener('submit', (e) => this.handleAddFormSubmit(e));
        }

        // Auto-completion du texte alternatif basé sur le nom
        const nomInput = document.getElementById('nom_nouvel_element');
        const altInput = document.getElementById('alt');
        if (nomInput && altInput) {
            nomInput.addEventListener('input', () => this.updateAltText(nomInput, altInput));
        }

        // Preview de l'URL d'image
        const urlInput = document.getElementById('url');
        if (urlInput) {
            urlInput.addEventListener('blur', () => this.previewImage(urlInput));
        }
    }

    /**
     * Configure la validation des formulaires
     */
    setupFormValidation() {
        // Validation en temps réel du nom d'élément
        const nomInput = document.getElementById('nom_nouvel_element');
        if (nomInput) {
            nomInput.addEventListener('input', (e) => this.validateElementName(e.target));
        }

        // Validation des textareas d'histoires
        const storyTextareas = document.querySelectorAll('textarea[name*="[content]"]');
        storyTextareas.forEach(textarea => {
            textarea.addEventListener('input', (e) => this.validateStoryContent(e.target));
        });
    }

    /**
     * Configure l'auto-focus sur les champs appropriés
     */
    setupAutoFocus() {
        // Auto-focus sur le premier champ si pas de message d'erreur
        const messageDiv = document.querySelector('.message.error');
        if (!messageDiv) {
            const firstInput = document.getElementById('type_ajout');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    /**
     * Configure l'animation de la barre de progression
     */
    setupProgressAnimation() {
        const progressBar = document.querySelector('[style*="background: #28a745"]');
        if (progressBar) {
            // Animation de la barre de progression au chargement
            const originalWidth = progressBar.style.width;
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = originalWidth;
            }, 500);
        }
    }

    /**
     * Configure les compteurs de caractères pour les textareas
     */
    setupTextareaCounters() {
        // Supprimer les anciens compteurs
        document.querySelectorAll('.character-counter').forEach(counter => counter.remove());
        
        // Ajouter les compteurs aux textareas visibles
        const visibleTextareas = document.querySelectorAll('textarea');
        visibleTextareas.forEach(textarea => {
            // Vérifier si le textarea est visible
            const isVisible = textarea.offsetParent !== null;
            if (isVisible) {
                this.addCharacterCounter(textarea);
            }
        });
    }

    /**
     * Gère la soumission du formulaire d'histoires
     */
    handleStoryFormSubmit(event) {
        const form = event.target;
        const requiredTextareas = form.querySelectorAll('textarea[required]');
        const emptyStories = [];
        const shortStories = [];

        requiredTextareas.forEach((textarea, index) => {
            const content = textarea.value.trim();
            if (!content) {
                emptyStories.push(index + 1);
            } else if (content.length < 50) {
                shortStories.push(index + 1);
            }
        });

        let warningMessage = '';
        if (emptyStories.length > 0) {
            warningMessage += `⚠️ ${emptyStories.length} histoire(s) sont vides (n° ${emptyStories.join(', ')}).\n`;
        }
        if (shortStories.length > 0) {
            warningMessage += `📝 ${shortStories.length} histoire(s) semblent très courtes (n° ${shortStories.join(', ')}).\n`;
        }

        if (warningMessage) {
            warningMessage += '\nVoulez-vous continuer quand même ?';
            if (!confirm(warningMessage)) {
                event.preventDefault();
                // Focus sur la première histoire vide ou courte
                const firstProblemIndex = emptyStories.length > 0 ? emptyStories[0] : shortStories[0];
                const problemTextarea = form.querySelector(`textarea[id="story_${firstProblemIndex}"]`);
                if (problemTextarea) {
                    problemTextarea.focus();
                    problemTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    }

    /**
     * Gère la soumission du formulaire d'ajout d'élément
     */
    handleAddFormSubmit(event) {
        const form = event.target;
        const type = form.querySelector('#type_ajout').value;
        const nom = form.querySelector('#nom_nouvel_element').value.trim();

        if (!type || !nom) {
            alert('⚠️ Veuillez sélectionner un type et saisir un nom pour l\'élément.');
            event.preventDefault();
            return;
        }

        // Confirmation pour des noms très courts
        if (nom.length < 3) {
            if (!confirm('Le nom saisi est très court. Êtes-vous sûr de vouloir continuer ?')) {
                event.preventDefault();
                return;
            }
        }

        // Indication de traitement
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Traitement en cours...';
            
            // Restaurer le bouton après 10 secondes si pas de redirection
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '🧮 Calculer et Ajouter';
            }, 10000);
        }
    }

    /**
     * Met à jour automatiquement le texte alternatif basé sur le nom
     */
    updateAltText(nomInput, altInput) {
        if (!altInput.value || altInput.dataset.autoGenerated === 'true') {
            const nom = nomInput.value.trim();
            if (nom) {
                altInput.value = `Image représentant ${nom}`;
                altInput.dataset.autoGenerated = 'true';
            }
        }
    }

    /**
     * Prévisualise l'image si l'URL est fournie
     */
    previewImage(urlInput) {
        const url = urlInput.value.trim();
        let preview = document.getElementById('image-preview');
        
        // Supprimer l'ancienne preview
        if (preview) {
            preview.remove();
        }

        if (url && (url.includes('.jpg') || url.includes('.jpeg') || url.includes('.png') || url.includes('.gif') || url.includes('.webp'))) {
            preview = document.createElement('div');
            preview.id = 'image-preview';
            preview.style.cssText = 'margin-top: 10px; text-align: center;';
            
            const img = document.createElement('img');
            img.src = url;
            img.style.cssText = 'max-width: 200px; max-height: 150px; border: 1px solid #ddd; border-radius: 5px;';
            img.alt = 'Prévisualisation de l\'image';
            
            img.onload = () => {
                preview.innerHTML = '<small style="color: #28a745;">✅ Image chargée avec succès</small>';
                preview.appendChild(img);
            };
            
            img.onerror = () => {
                preview.innerHTML = '<small style="color: #dc3545;">❌ Impossible de charger l\'image</small>';
            };
            
            urlInput.parentNode.appendChild(preview);
        }
    }

    /**
     * Valide le nom de l'élément
     */
    validateElementName(input) {
        const value = input.value.trim();
        const feedback = this.getOrCreateFeedback(input);
        
        if (value.length < 2) {
            this.showFeedback(feedback, 'Le nom doit contenir au moins 2 caractères', 'error');
        } else if (value.length > 100) {
            this.showFeedback(feedback, 'Le nom ne peut pas dépasser 100 caractères', 'error');
        } else {
            this.showFeedback(feedback, '✓ Nom valide', 'success');
        }
    

   
        
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = 'text-align: right; font-size: 0.8em; color: #666; margin-top: 2px;';
    }
         /**
     * Valide le contenu d'une histoire
     */
    validateStoryContent(textarea) {
        const value = textarea.value.trim();
        const feedback = this.getOrCreateFeedback(textarea);
        
        if (value.length === 0) {
            this.showFeedback(feedback, 'Histoire requise', 'error');
        } else if (value.length < 50) {
            this.showFeedback(feedback, `Histoire très courte (${value.length} caractères)`, 'warning');
        } else if (value.length > 5000) {
            this.showFeedback(feedback, 'Histoire très longue, considérez la raccourcir', 'warning');
        } else {
            this.showFeedback(feedback, `✓ Longueur appropriée (${value.length} caractères)`, 'success');
        }
    }

    /**
     * Ajoute un compteur de caractères à un textarea
     */
    addCharacterCounter(textarea) {
        // Éviter les doublons
        if (textarea.nextSibling && textarea.nextSibling.classList && textarea.nextSibling.classList.contains('character-counter')) {
            return;
        }
        const updateCounter = () => {
            const length = textarea.value.length;
            counter.textContent = `${length} caractères`;
            
            // Changer la couleur selon la longueur
            if (length < 50) {
                counter.style.color = '#dc3545'; // Rouge
            } else if (length > 1000) {
                counter.style.color = '#ffc107'; // Orange
            } else {
                counter.style.color = '#28a745'; // Vert
            }
        };
        
        textarea.addEventListener('input', updateCounter);
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        updateCounter();
    }

    /**
     * Obtient ou crée un élément de feedback pour un input
     */
    getOrCreateFeedback(input) {
        let feedback = input.parentNode.querySelector('.input-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'input-feedback';
            feedback.style.cssText = 'font-size: 0.8em; margin-top: 2px; min-height: 1.2em;';
            input.parentNode.appendChild(feedback);
        }
        return feedback;
    }

    /**
     * Affiche un message de feedback
     */
    showFeedback(element, message, type) {
        element.textContent = message;
        element.className = `input-feedback ${type}`;
        
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107'
        };
        
        element.style.color = colors[type] || '#666';
    }

    /**
     * Sauvegarde automatique des brouillons (localStorage si disponible)
     */
    setupAutoSave() {
        if (typeof Storage !== 'undefined') {
            const textareas = document.querySelectorAll('textarea[name*="[content]"]');
            
            textareas.forEach((textarea, index) => {
                const key = `story_draft_${index}`;
                
                // Restaurer le brouillon
                const draft = localStorage.getItem(key);
                if (draft && !textarea.value) {
                    textarea.value = draft;
                    this.showTemporaryMessage('💾 Brouillon restauré', 'info');
                }
                
                // Sauvegarder automatiquement
                let saveTimeout;
                textarea.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        localStorage.setItem(key, textarea.value);
                    }, 1000);
                });
                
                // Nettoyer au submit
                const form = textarea.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        localStorage.removeItem(key);
                    });
                }
            });
        }
    }

    /**
     * Affiche un message temporaire
     */
    showTemporaryMessage(message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 300px;';
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.transition = 'opacity 0.5s';
            setTimeout(() => messageDiv.remove(), 500);
        }, 3000);
    }

    /**
     * Gestion des raccourcis clavier
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S pour sauvegarder
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.click();
                }
            }
            
            // Échap pour annuler/fermer
            if (e.key === 'Escape') {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.tagName === 'TEXTAREA') {
                    activeElement.blur();
                }
            }
            
            // Ctrl+1 pour mode single, Ctrl+2 pour mode multiple
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                this.switchToSingleMode();
            }
            if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                this.switchToMultipleMode();
            }
        });
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    const storyAdmin = new StoryAdmin();
    
    // Optionnel: rendre l'instance globalement accessible pour le débogage
    window.storyAdmin = storyAdmin;
    
    console.log('📚 StoryAdmin.js initialisé avec succès');
});