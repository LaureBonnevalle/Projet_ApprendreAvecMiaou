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





 };