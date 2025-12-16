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