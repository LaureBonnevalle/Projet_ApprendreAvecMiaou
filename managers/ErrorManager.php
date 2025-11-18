<?php

class ErrorManager {

    /**
     * Log an error message to a designated error log file with a timestamp.
     * @param   string  - $message The error message to be logged.
     * @return  void
    */
    public static function logError($message) {
        // Log the error message to a designated error log file with a timestamp
        error_log(date('Y-m-d H:i:s') . " Erreur: " . $message . PHP_EOL, 3, "documents/errors.log");
    }


    /**
     * Redirect the user to the error page (error404) and terminate the script.
     * @return  void
    */
    public static function redirectToErrorPage() {
        header('Location: index.php?route=error404');
        exit();
    }
}