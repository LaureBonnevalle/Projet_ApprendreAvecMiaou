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


    public function validateEmail($email): bool {
        // Returns true if the email is valid, otherwise false.
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePassword($password, $nbr = 8) {
        // The preg_match() function searches for a match between the regex pattern and the password.
        // The regex pattern is '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
        // This pattern requires:
        // - At least one uppercase letter (?=.*?[A-Z])
        // - At least one lowercase letter (?=.*?[a-z])
        // - At least one digit (?=.*?[0-9])
        // - At least one special character among #?!@$%^&*- (?=.*?[#?!@$%^&*-])
        // - A minimum length of 8 characters .{'.$nbr.',}
        // The function returns 1 if a match is found, otherwise 0.
        return preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/', $password);
    }

    public function clearSessionMessages(): void
    {
        $messageKeys = ['messages', 'error', 'success', 'warning', 'info', 'flash', 'error_message', 'success_message', 'error_message', 'success_message'];
        foreach ($messageKeys as $key) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }
    }

    function createThumbnailFromPDF(string $pdfFilePath, int $coloringId): ?string {
    try {
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($pdfFilePath . '[0]');
        $imagick->setImageFormat('png');
        $imagick->thumbnailImage(300, 0);

        // Générer le chemin du thumbnail basé sur l'ID
        $thumbnailDir = __DIR__ . '/../public/assets/img/coloringSheets/thumbnails/';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0777, true);
        }

        $thumbnailPath = $thumbnailDir . 'coloring_' . $coloringId . '.png';
        $imagick->writeImage($thumbnailPath);

        $imagick->clear();
        $imagick->destroy();

        // Retourner le chemin relatif utilisable côté front
        return 'assets/img/coloringSheets/thumbnails/coloring_' . $coloringId . '.png';

        } catch (Exception $e) {
        error_log("Erreur génération thumbnail: " . $e->getMessage());
        return null;
        }
    }
            
        


 };