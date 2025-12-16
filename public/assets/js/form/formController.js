/**
 * ============================
 * Fonctions utilitaires
 * ============================
 */

// Supprime les espaces au début et à la fin
function trimString(str) {
    return str.trim();
}

/**
 * ============================
 * Fonctions de validation
 * ============================
 */

// Vérifie une adresse email
function validateEmail(email) {
    const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(email);
}

// Vérifie un champ texte avec longueur min/max
function validateText(text, minLength, maxLength) {
    return text.length >= minLength && text.length <= maxLength;
}

// Vérifie un mot de passe (8-23 caractères, majuscule, minuscule, chiffre, spécial)
function validatePassword(password) {
    const re = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,23}$/;
    return re.test(password);
}

// Vérifie que deux mots de passe sont identiques
function validateConfirmPassword(password, confirmPassword) {
    return password === confirmPassword;
}

// Vérifie qu'une valeur n'est pas vide
function notEmpty(value) {
    return value !== "";
}

/**
 * ============================
 * Contrôleur de formulaire
 * ============================
 */
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche la soumission par défaut

            // Réinitialiser les messages d'erreur
            const errorMessages = form.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.textContent = '');

            let formValid = true;

            // Récupérer la valeur du nouveau mot de passe (utile pour la confirmation)
            const newPasswordValue = form.querySelector("#new_password")?.value || '';

            // Liste des champs à valider (utilisés dans tous les formulaires)
            const fields = [
                { id: "firstname", validate: validateText, args: [2, 60], errorMessage: "Le prénom est requis.", invalidMessage: "Le prénom doit contenir entre 2 et 60 caractères." },
                { id: "email", validate: validateEmail, args: [], errorMessage: "L'email est requis.", invalidMessage: "L'email n'est pas valide." },
                { id: "csrf_token", validate: notEmpty, args: [], errorMessage: "Erreur CSRF, veuillez actualiser la page.", invalidMessage: "" },
                { id: "subject", validate: validateText, args: [3, 100], errorMessage: "Le sujet est requis.", invalidMessage: "Le sujet doit contenir entre 3 et 100 caractères." },
                { id: "content", validate: validateText, args: [20, 350], errorMessage: "Le message est requis.", invalidMessage: "Le message doit contenir entre 20 et 350 caractères." },
                { id: "password", validate: validatePassword, args: [], errorMessage: "Le mot de passe est requis.", invalidMessage: "Le mot de passe doit contenir 8-23 caractères, avec majuscule, minuscule, chiffre et caractère spécial." },
                { id: "old_password", validate: validatePassword, args: [], errorMessage: "Le mot de passe provisoire est requis.", invalidMessage: "Le mot de passe provisoire doit respecter les règles de sécurité." },
                { id: "new_password", validate: validatePassword, args: [], errorMessage: "Le nouveau mot de passe est requis.", invalidMessage: "Votre nouveau mot de passe doit respecter les règles de sécurité." },
                { id: "confirm_new_password", validate: validateConfirmPassword, args: [newPasswordValue], errorMessage: "La confirmation est requise.", invalidMessage: "La confirmation doit être identique au nouveau mot de passe." }
            ];

            // Vérification de chaque champ
            fields.forEach(field => {
                const inputElement = form.querySelector(`#${field.id}`);
                const errorElement = form.querySelector(`#error-${field.id}`);

                if (inputElement && errorElement) {
                    const value = trimString(inputElement.value); // Nettoyage automatique

                    if (value === "") {
                        formValid = false;
                        errorElement.textContent = field.errorMessage;
                        inputElement.classList.add("error");
                    } else if (!field.validate(value, ...field.args)) {
                        formValid = false;
                        errorElement.textContent = field.invalidMessage;
                        inputElement.classList.add("error");
                    }

                    // Nettoyer l'erreur en cas de saisie
                    inputElement.addEventListener("input", function() {
                        errorElement.textContent = "";
                        inputElement.classList.remove("error");
                    });
                }
            });

            // Soumettre si tout est valide
            if (formValid) {
                form.submit();
            }
        });
    });
});
