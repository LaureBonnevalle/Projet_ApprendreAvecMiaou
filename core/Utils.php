 <?php

class Utils {


    public function e($string) {
        if($string != NULL)
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        else
            return $string;        
    }

    // Méthode pour échapper les données dans un tableau associatif
    public function escapeData(array $data) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->e($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->escapeData($value); // Appel récursif si le champ est un tableau
            }
        }
        return $data;
    }

    function asset($path) {
        return '/ProjetMiaou/New/' . ltrim($path, '/');
    }

    /**
     * Method to check if all keys are present in $_POST.
     *
     * @param   array   - $keys The array containing keys to be checked.
     * @return  bool    - Returns true if all keys are present in $_POST, otherwise false.
     */
    public function checkPostKeys($keys):bool {
        // Iterate over each key in the $keys array.
        foreach ($keys as $key) {
            // Check if the current key is not present in $_POST.
            if (!isset($_POST[$key])) {
                // If a key is missing, return false.
                return false;
            }
        }
        // If all keys are present, return true.
        return true;
    }

    /**
     * Generates a random password of a specified length.
     * The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.
     *
     * @param   int     $length     - The length of the random password to generate. Must be between 8 and 23 characters. Default value 12.
     * @return  string              - The generated random password.
     * @throws  Exception if the length is not between 8 and 23 characters.
     */
    public function generateRandomPassword($length = 12) {
        if ($length < 8 || $length > 23) {
            throw new Exception("La longueur du mot de passe doit être entre 8 et 23 caractères.");
        }

        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        //$specialChars = '!@#$%^&*()-_=+[{]}|;:,.?';
        $specialChars = '#?!@$%^&*-';

        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $specialChars;

        for ($i = 0; $i < $length - 4; $password .= $allChars[rand(0, strlen($allChars) - 1)], $i++);

        return str_shuffle($password);
    }


    public function isAuthentified() {
    if (isset($_SESSION['user']['role'])) {
        return true;   
    }
    return false; // ← AJOUT ESSENTIEL

    }

    public function isAdmin() {
    if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 2){
        return true;   
    }
    return false; // ← AJOUT ESSENTIEL
    }

    public function isUser() {
        if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 1) {
            return true;
        }
        return false; // ← AJOUT ESSENTIEL
    }

    public function isValidateUser() {
        if (isset($_SESSION['user']['statut']) && $_SESSION['user']['statut'] === 1){
            return true;
        }
        return false; // ← AJOUT ESSENTIEL
    }

    public function isWaitValidateUser() {
        if (isset($_SESSION['user']['statut']) && $_SESSION['user']['statut'] === 0){
            return true;
        }
        return false; // ← AJOUT ESSENTIEL
    }

    public function isBanned() {
        if (isset($_SESSION['user']['statut']) && $_SESSION['user']['statut'] === 3) {
            return true;
        }
        return false; // ← AJOUT ESSENTIEL
    }

    public function isSimpleUser() {
        if (isset($_SESSION['login_data']['connected']) && $_SESSION['login_data']['connected'] === 0) {
            return true;
        }
        return false; // ← AJOUT ESSENTIEL
    }
            
        


 };