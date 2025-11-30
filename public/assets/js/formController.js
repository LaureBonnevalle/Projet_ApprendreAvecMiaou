/**
 * Validate an email address.
 * 
 * @param {string} email    - The email address to validate.
 * @returns {boolean} True if the email is valid, false otherwise.
 */
function validateEmail(email) {
    const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return re.test(email);
}

/**
 * Validate a text field.
 * 
 * @param {string} text         - The text to validate.
 * @param {number} minLength    - The minimum length of the text.
 * @param {number} maxLength    - The maximum length of the text.
 * @returns {boolean} True if the text is valid, false otherwise.
 */
function validateText(text, minLength, maxLength) {
    //const re = /^[a-zA-ZÀ-ÿ\s-]+$/;
    //return re.test(text) && text.length >= minLength && text.length <= maxLength;
    return text.length >= minLength && text.length <= maxLength;
}

/**
 * Validate a password.
 * 
 * @param {string} password     - The password to validate.
 * @returns {boolean} True if the password is valid, false otherwise.
 */
function validatePassword(password, length = 8) {
    const re = new RegExp(`^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[\\W_]).{${length},}$`);
    return re.test(password);
}

/**
 * Checks if the two passwords are the same.
 * 
 * @param {string} password         - The new password.
 * @param {string} confirmPassword  - The confirmPassword.
 * @returns {boolean} True if both passwords are the same.
 */
function validateConfirmPassword(password, confirmPassword) {
    return password === confirmPassword;
}



/**
 * Capitalize the first letter of each word in a string.
 * 
 * @param {string} str - The string to capitalize.
 * @returns {string} The capitalized string.
 */
function capitalize(str) {
    return str.replace(/\b\w/g, char => char.toUpperCase());
}

/**
 * Trim whitespace from both ends of a string.
 * 
 * @param {string} str - The string to trim.
 * @returns {string} The trimmed string.
 */
function trimString(str) {
    return str.trim();
}

/**
 * Truncate a string to a specific length and add ellipses if needed.
 * 
 * @param {string} str - The string to truncate.
 * @param {number} length - The maximum length of the string.
 * @returns {string} The truncated string.
 */
function truncateString(str, length) {
    return str.length > length ? str.slice(0, length) + '...' : str;
}

/**
 * Format a date to a specific format (e.g., YYYY-MM-DD).
 * 
 * @param {Date} date - The date to format.
 * @returns {string} The formatted date string.
 */
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}


/**
 * Check if a value is empty (null, undefined, or empty string).
 * 
 * @param {*} value - The value to check.
 * @returns {boolean} True if the value is empty, false otherwise.
 */
function isEmpty(value) {
    return value == null || value.trim() === '';
}

/**
 * Check if a value is numeric.
 * 
 * @param {*} value - The value to check.
 * @returns {boolean} True if the value is numeric, false otherwise.
 */
function isNumeric(value) {
    return !isNaN(value) && !isNaN(parseFloat(value));
}

/**
 * Get the current date in a specified time zone in the format YYYY-MM-DD.
 * 
 * @param {string} timeZone - The desired time zone (e.g., 'America/New_York').
 * @returns {string} The formatted date string.
 */
function getCurrentDate(timeZone) {
    const now = new Date();

    // Create a DateTimeFormat object for date
    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        timeZone: timeZone,
    });

    // Format the date
    const [month, day, year] = dateFormatter.format(now).split('/');

    // Combine date in YYYY-MM-DD format
    return `${year}-${month}-${day}`;

    /*  Example usage:
        console.log(getCurrentDate('Europe/Paris')); // Outputs: 2024-08-02
    */
}

/**
 * Get the current date and time in a specified time zone in the format YYYY-MM-DD HH:MM:SS.
 * 
 * @param {string} timeZone - The desired time zone (e.g., 'America/New_York').
 * @returns {string} The formatted date and time string.
 */
function getCurrentDateTime(timeZone) {
    const now = new Date();

    // Create formatters for date and time
    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        timeZone: timeZone,
    });

    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: timeZone,
    });

    // Format the date and time separately
    const [month, day, year] = dateFormatter.format(now).split('/');
    const [hour, minute, second] = timeFormatter.format(now).split(':');

    // Combine date and time with a space separator
    return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
    
    /*  Example usage:
        console.log(getCurrentDateTime('Europe/Paris')); // Outputs: 2024-08-02 15:25:56
    */
}

/**
 * Get the current time in a specified time zone.
 * 
 * @param {string} timeZone - The desired time zone (e.g., 'America/New_York').
 * @returns {string} The formatted time string.
 */
function getCurrentTime(timeZone) {
    const now = new Date();

    // Create a DateTimeFormat object with the desired time zone and options
    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: timeZone,
    });

    // Format the current time
    return timeFormatter.format(now);

    /*  Example usage:
        console.log(getCurrentTime('Europe/Paris'));
    */
}

/**
 * Get the current URL of the page.
 * 
 * @param {void}
 * @returns {string} The full URL of the current page.
 */
function getCurrentURL() {
    return window.location.href;
    /*  Example usage:
        console.log(getCurrentURL()); // Outputs the integral URL (e.g., 'http://propermut/index.php?route=register')
    */
}

/**
 * Get the query string of the current URL.
 * 
 * @param {void}
 * @returns {string} The query string of the current URL, without the leading '?'.
 */
function getQueryString() {
    return window.location.search.substring(1); // Removes the leading '?'
    
    /*  Example usage:
        console.log(getQueryString()); // Outputs the query string part of the URL (e.g., 'route=register')
    */
}

/**
 * Get the value of a specific query parameter from the current URL.
 * 
 * @param {string} key - The name of the query parameter to retrieve.
 * @returns {string|null} The value of the query parameter, or null if the parameter is not found.
 */
function getQueryParam(key) {
    const params = new URLSearchParams(window.location.search);
    return params.get(key);
    
    /*  Example usage:
        console.log(getQueryParam('route')); // Outputs the value of the 'route' parameter (e.g., 'register')
    */
}

/**
 * Get the path of the current URL.
 * 
 * @param {void}
 * @returns {string} The path of the current URL, without the query string or fragment.
 */
function getPath() {
    return window.location.pathname;
    /*  Example usage:
        console.log(getPath()); // Outputs the path part of the URL (e.g., '/index.php')
    */
}


document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            // Prevent the form from submitting by default
            event.preventDefault();

            // Reset error messages and styles
            const errorMessages = form.querySelectorAll('.error-message');
            errorMessages.forEach(function(msg) {
                msg.textContent = '';
            });

            let formValid = true;
            
            // Retrieve the new_password value before validation
            const newPasswordValue = form.querySelector("#new_password")?.value || '';

            // Check each field if present
            const fields = [
                { 
                    id              : "firstname", 
                    validate        : validateText, 
                    args            : [2, 60], 
                    errorMessage    : "Le prénom est requis.", 
                    invalidMessage  : "Le prénom doit contenir uniquement des lettres, entre 2 et 60 caractères."
                },
                { 
                    id              : "email", 
                    validate        : validateEmail, 
                    args            : [], 
                    errorMessage    : "L'email est requis.", 
                    invalidMessage  : "L'email n'est pas valide."
                },
                { 
                    id              : "token", 
                    validate        : value => value !== "", 
                    args            : [], 
                    errorMessage    : "Une erreur est survenue lors de l'envoi, actualisez le formulaire et soumettez votre demande une seconde fois !", 
                    invalidMessage  : ""
                },
                { 
                    id              : "content", 
                    validate        : validateText, 
                    args            : [20, 350], 
                    errorMessage    : "Ce champ est requis.", 
                    invalidMessage  : "Ce champ doit contenir entre 20 et 250 caractères."
                },
                { 
                    id              : "subject", 
                    validate        : validateText, 
                    args            : [3, 100], 
                    errorMessage    : "Ce champ est requis.", 
                    invalidMessage  : "Ce champ doit contenir entre 20 et 250 caractères."
                },
                { 
                    id              : "password", 
                    validate        : validatePassword, 
                    args            : [], 
                    errorMessage    : "Le mot de passe est requis.", 
                    invalidMessage  : "Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial."
                },
                { 
                    id              : "old_password", 
                    validate        : validatePassword, 
                    args            : [12], 
                    errorMessage    : "Le mot de passe provisoire est requis.", 
                    invalidMessage  : "Le mot de passe provisoire qui vous a été envoyé doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
                },
                { 
                    id              : "new_password", 
                    validate        : validatePassword, 
                    args            : [], 
                    errorMessage    : "Le mot de passe est requis.", 
                    invalidMessage  : "Votre nouveau mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
                },
                { 
                    id              : "confirm_new_password", 
                    validate        : validateConfirmPassword,
                    args            : [newPasswordValue],      // Pass the value of "new_password" as an argument
                    errorMessage    : "Le mot de passe est requis.", 
                    invalidMessage  : "La confirmation de votre nouveau mot de passe doit être identique."
                },
            ];

            fields.forEach(field => {
                const inputElement = form.querySelector(`#${field.id}`);
                const errorElement = form.querySelector(`#error-${field.id}`);

                if (inputElement && errorElement) {
                    const value = inputElement.value.trim();
                    if (value === "") {
                        formValid = false;
                        errorElement.textContent = field.errorMessage;
                    } else if (!field.validate(value, ...field.args)) {
                        formValid = false;
                        errorElement.textContent = field.invalidMessage;
                    }

                    inputElement.addEventListener("input", function() {
                        errorElement.textContent = "";
                        inputElement.classList.remove("error");
                    });
                }
            });

            // If the form is valid, submit it
            if (formValid) {
                form.submit();
            }
        });
    });
});