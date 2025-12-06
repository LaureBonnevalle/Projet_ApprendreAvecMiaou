<?php

class UserController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
    }

    public function displayProfile() : void
{
    $am = new AvatarManager();
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();
    $scripts = $this->addScripts([
        'assets/js/formController.js',
        'assets/js/profile.js',
        'assets/js/mess.js',
    ]);
    $func = new Utils();
    
    // Récupérer l'avatar actuel de l'utilisateur
    $avatar = $am->getById($_SESSION['user']['avatar']);
     $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
    
    // Récupérer TOUS les avatars disponibles pour le formulaire
    $avatarItems = $am->findAllAvatars();;
    foreach ($avatarItems as $avatarItem) {
        $avatarItem->setUrlMini($func->asset($avatarItem->getUrlMini()));
        }

    // Récupération des infos utilisateur
    $user = $_SESSION['user'];

    $this->render("profile.html.twig", [
        'titre'           => 'Profil',
        'user'            => $user,
        'avatar'          => $avatar,      // ✅ Avatar actuel (objet)
        'avatarItems'     => $avatarItems,     // ✅ Tous les avatars (array)
        'elapsed_time'    => $elapsedTime,
        'session'         => $_SESSION,
        'connected'       => true,
        'isUser'          => true,
        'start_time'      => $_SESSION['start_time'],
        'error_message'   => $_SESSION['error_message'] ?? null,
        'success_message' => $_SESSION['success_message'] ?? null,
        'csrf_token'      => (new CSRFTokenManager())->generateCSRFToken()
    ], $scripts);
    
    // Nettoyer les messages après affichage
    unset($_SESSION['error_message'], $_SESSION['success_message']);
}

public function updateProfile() : void
    {
       

        $func = new Utils();
        $tm   = new CSRFTokenManager();

        // Vérification des champs attendus
        if (!$func->checkPostKeys(['firstname', 'age', 'email', 'avatar', 'csrf_token'])) {
           // Écriture dans un fichier texte (append)
            header('Content-Type: application/json');
file_put_contents(__DIR__ . '/test.txt', json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
            echo json_encode([
                'success' => false,
                'message' => 'Champs manquants ou invalides.'
            ]);
            return;
        }

        $firstname = trim($_POST['firstname']);
        $age       = (int) $_POST['age'];
        $email     = strtolower(trim($_POST['email']));
        $avatar  = (int) $_POST['avatar'];
        $csrf      = $_POST['csrf_token'];

        // Validation CSRF
        if (!$tm->validateCSRFToken($csrf)) {
             header('Content-Type: application/json');
           file_put_contents(__DIR__ . '/test2.txt', json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'message' => 'Token CSRF invalide.'
            ]);
            return;
        }

        // Validation basique
        if (empty($firstname) || empty($email) || $age <= 0) {
             header('Content-Type: application/json');
            file_put_contents(__DIR__ . 'documents/test3.txt', json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'message' => 'Veuillez remplir correctement tous les champs.'
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             header('Content-Type: application/json');
            file_put_contents(__DIR__ . '/test4.txt', json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'message' => 'Adresse email invalide.'
            ]);
            return;
        }

        try {
            $um = new UserManager();
            $updated = $um->updateUserProfile($_SESSION['user']['id'], $firstname, $age, $email, $avatarId);

            if ($updated) {
                // Mettre à jour la session
                $_SESSION['user']['firstname'] = $firstname;
                $_SESSION['user']['age']       = $age;
                $_SESSION['user']['email']     = $email;
                $_SESSION['user']['avatar']    = $avatarId;

                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès.'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du profil.'
                ]);
            }
        } catch (Exception $e) {
            error_log("Erreur updateProfile: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue.'
            ]);
        }
    }

public function toggleNewsletter() : void
{
    $tm = new CSRFTokenManager();
    if (!$tm->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Token CSRF invalide.";
        $this->redirectTo('profile');
        return;
    }

    $um = new UserManager();
    $current = $_SESSION['user']['newsletter'] ?? 0;
    $newValue = $current == 1 ? 0 : 1;

    if ($um->changeNewsletter($_SESSION['user']['id'], $newValue)) {
        $_SESSION['user']['newsletter'] = $newValue;
        $_SESSION['success_message'] = $newValue == 1 
            ? "Vous êtes abonné à la newsletter." 
            : "Vous êtes désabonné de la newsletter.";
    } else {
        $_SESSION['error_message'] = "Impossible de mettre à jour la newsletter.";
    }

    $this->redirectTo('profile');
}



    /********************************************************************/ 
/*************ADMIN ALL USERS****************************************/


 /**
     * Fetches all users from the database and renders the users page.
     *
     * @param  void
     * @return void
     */
    public function allUsers() {
        $scripts = $this->addScripts([
          'https://kit.fontawesome.com/3c515cc4da.js',
            'assets/js/common.js', 
            'assets/js/formController.js',
            'assets/js/formFunction.js',
            'assets/js/adminjs/ajaxOneUser.js',
            'assets/js/adminjs/ajaxSearchUsers.js',
            'assets/js/adminjs/coloringAdmin.js',
            'assets/js/adminjs/storyAdmin.js',
            'assets/js/adminjs/modifyAvatarAdmin.js',
        ]);
        $users     = (new UserManager())->getAllUsers() ;
        //var_dump($users);
      

        $this->render('users.html.twig', [
            'users' => $users ,
            'user' => $_SESSION['user'] ?? null,
            'avatar' => [$avatar],
            'session' => $_SESSION,
            'connected' => true,
            'isUser' => false,
            'isAdmin' => true,
            'elapsed_time' => $elapsedTime,
            'start_time' => $_SESSION['start_time'] ?? time(),
            'nbrMessages' => $nbrMessages,
            'titre' => 'Dashboard Admin'
        ], $scripts);
    }

    /**
     * Retrieves all permutations, triangulations, and quadripartites and renders the 'combinations' view.
     *
     * @param void
     * @return void
     */
    
    public function ajaxSearchUsers() {

        ob_clean(); // Clean the output buffer

        $content = file_get_contents("php://input");
        $data = json_decode($content, true);

        $ref = "%".$data['ref']."%";

        $um = new UserManager();
        $users = $um->getAllUsersByLike($ref);

        header('Content-Type: application/json');
        $jsonAlls = json_encode($users);
        file_put_contents('result.txt', $jsonAlls);
        echo $jsonAlls;        

        exit;
    }
    
    public function readOneUser() {
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        // Rediriger vers une page d'erreur
        return;
        }
        // Get the user's profil informations
        $user = new Users();
        $user->setId($_GET['id']);
        $userNow = (new UserManager())->readOneUser($user->getId());
            if (!$userNow) {
            // Utilisateur non trouvé
            return;
            }
            
            //var_dump($userNow);
            
         
            
         $Utils = new Utils();
         $avatars= (new AvatarManager())->findAllAvatars();
         // Préparer les données de l'avatar
        $avatar= (new AvatarManager())->getById($userNow['avatar']);
        $scripts = $this->addScripts([
             'https://kit.fontawesome.com/3c515cc4da.js',
            'assets/js/common.js', 
            'assets/js/formController.js',
            'assets/js/formFunction.js',
            'assets/js/adminjs/ajaxOneUser.js',
            'assets/js/adminjs/ajaxSearchUsers.js',
            'assets/js/adminjs/coloringAdmin.js',
            'assets/js/adminjs/storyAdmin.js',
            'assets/js/adminjs/modifyAvatarAdmin.js',
        ]);
        
        //var_dump($avatar, $avatars);
    
        $this->render('displayOneUser.html.twig', [
            'id'        => $userNow['id'] ?? null, 
            'email'     => $Utils->e($userNow['email']) ?? null,
            'password'  => isset($userNow['password']) ?  : null,
            'firstname' => $Utils->e($userNow['firstname']) ?? null,
            'age'       => isset($userNow['age']) ? (int)$userNow['age'] : null,
            'avatar'    => $avatar,
            'newsletter'=> isset($userNow['newsletter']) ? (bool)$userNow['newsletter'] : false,
            'role'      => isset($userNow['role']) ? (int)$userNow['role'] : null,
            'statut'    => isset($userNow['statut']) ? (int)$userNow['statut'] : null,
            'createdAt' => $userNow['createdAt'] ?? null,
            'avatars'   => $avatars,
            'user' => $_SESSION['user'] ?? null,
            'avatar' => [$avatar],
            'session' => $_SESSION,
            'connected' => true,
            'isUser' => false,
            'isAdmin' => true,
            'elapsed_time' => $elapsedTime,
            'start_time' => $_SESSION['start_time'] ?? time(),
            'nbrMessages' => $nbrMessages,
            'titre' => 'Dashboard Admin'
        ],$scripts);
    }
    


 
 
 
 
 
/****************ADMIN READ ONE USER*********************************/ 

   /**
 * Update user avatar
 * Met à jour l'avatar d'un utilisateur
 * 
 * @return void Outputs JSON response
 */
    public function updateUserAvatar() {
        // DEBUGGING: Log que la méthode est appelée
        error_log("=== updateUserAvatar() appelée ===");
        
        // Forcer l'affichage des erreurs pour debug
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Garder à 0 pour éviter de polluer le JSON
        
        // Set JSON response header AVANT tout autre output
        header('Content-Type: application/json');
        
        // DEBUGGING: Vérifier la méthode HTTP
        error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);
        
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Erreur: Méthode non POST");
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        // Get JSON data from request body
        $rawInput = file_get_contents('php://input');
        error_log("Raw input reçu: " . $rawInput);
        
        // Check if data is received
        if (empty($rawInput)) {
            error_log("Erreur: Aucune donnée reçue");
            echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue']);
            return;
        }
        
        // Decode JSON data
        $input = json_decode($rawInput, true);
        error_log("Input décodé: " . print_r($input, true));
        
        // Validate JSON format
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur JSON: " . json_last_error_msg());
            echo json_encode(['success' => false, 'message' => 'JSON invalide: ' . json_last_error_msg()]);
            return;
        }
        
        // Extract user ID and avatar ID
        $userId = $input['user_id'] ?? $input['userId'] ?? null;
        $avatarId = $input['avatar_id'] ?? $input['avatarId'] ?? $input['avatar'] ?? null;
        
        error_log("userId: $userId, avatarId: $avatarId");
        
        // Validate required data
        if (!$userId || !$avatarId) {
            error_log("Erreur: Données manquantes");
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            return;
        }
        
        // Convert to integers
        $userId = (int)$userId;
        $avatarId = (int)$avatarId;
        
        // Validate IDs are positive integers
        if ($userId <= 0 || $avatarId <= 0) {
            error_log("Erreur: IDs invalides");
            echo json_encode(['success' => false, 'message' => 'IDs invalides']);
            return;
        }
        
        try {
            error_log("Tentative de mise à jour: userId=$userId, avatarId=$avatarId");
            
            // Vérifier que les classes existent
            if (!class_exists('UserManager')) {
                throw new Exception('UserManager class not found');
            }
            if (!class_exists('AvatarManager')) {
                throw new Exception('AvatarManager class not found');
            }
            
            // Update user avatar
            $userManager = new UserManager();
            error_log("UserManager créé");
            
            $result = $userManager->updateAvatar($userId, $avatarId);
            error_log("Résultat updateAvatar: " . ($result ? 'true' : 'false'));
            
            if ($result) {
                // Get new avatar info for response
                $avatarManager = new AvatarManager();
                error_log("AvatarManager créé");
                
                $newAvatar = $avatarManager->getById($avatarId);
                error_log("Avatar récupéré: " . ($newAvatar ? 'trouvé' : 'non trouvé'));
                
                if ($newAvatar) {
                    $response = [
                        'success' => true, 
                        'message' => 'Avatar mis à jour avec succès',
                        'avatar' => [
                            'id' => $newAvatar->getId(),
                            'name' => $newAvatar->getName(),
                            'url' => $newAvatar->getUrl(),
                            'description' => $newAvatar->getDescription(),
                            'caracteristique' => $newAvatar->getCaracteristique(),
                            'qualite' => $newAvatar->getQualite(),
                        ]
                    ];
                    error_log("Réponse de succès: " . json_encode($response));
                    echo json_encode($response);
                } else {
                    error_log("Avatar non trouvé après mise à jour");
                    echo json_encode(['success' => false, 'message' => 'Avatar non trouvé après mise à jour']);
                }
            } else {
                error_log("Échec de la mise à jour en base de données");
                echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour en base de données']);
            }
            
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        
        error_log("=== Fin updateUserAvatar() ===");
    }

/**
 * Update user newsletter subscription status
 * Met à jour le statut d'abonnement newsletter d'un utilisateur
 * 
 * @return void Outputs JSON response
 */
    public function resetNewsletter() {
        // Check if request method is POST / Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            return;
        }

        // Validate Content-Type header / Valider l'en-tête Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Content-Type invalide']);
            return;
        }

        try {
            // Read and decode JSON data / Lire et décoder les données JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // Validate required parameters / Valider les paramètres requis
            if (!$data || !isset($data['user_id']) || !isset($data['newsletter'])) {
                throw new Exception('Données manquantes (user_id ou newsletter)');
            }

            // Cast parameters to integers / Convertir les paramètres en entiers
            $userId = (int) $data['user_id'];
            $newsletter = (int) $data['newsletter'];

            // Validate user ID / Valider l'ID utilisateur
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }

            // Validate newsletter status (0 = unsubscribed, 1 = subscribed)
            // Valider le statut newsletter (0 = désabonné, 1 = abonné)
            if (!in_array($newsletter, [0, 1])) {
                throw new Exception('Statut newsletter invalide. Doit être 0 (non abonné) ou 1 (abonné)');
            }

            // Check if user exists / Vérifier que l'utilisateur existe
            $userManager = new UserManager();
            $user = $userManager->readOneUser($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            // Create user object and update newsletter status
            // Créer l'item utilisateur et mettre à jour le statut newsletter
            $userObj = new Users();
            $userObj->setId($userId);
            $success = $userManager->updateNewsletter($userId, $newsletter);

            // Return success response / Retourner la réponse de succès
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Newsletter mise à jour avec succès',
                    'user_id' => $userId,
                    'new_newsletter' => $newsletter,
                    'newsletter_text' => $newsletter == 1 ? 'Abonné' : 'Non abonné'
                ]);
            } else {
                throw new Exception('Échec de la mise à jour de la newsletter en base de données');
            }
        } catch (Exception $e) {
            // Handle errors and return error response / Gérer les erreurs et retourner la réponse d'erreur
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
 
/**
 * Réinitialise le mot de passe d'un utilisateur
 * Reset user password
 */
    public function resetPassword() {
        // Nettoyer tout buffer de sortie existant / Clean any existing output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Désactiver l'affichage des erreurs pour éviter la sortie HTML / Disable error display to avoid HTML output
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);
        
        // Définir le header JSON dès le début / Set JSON header from the start
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Vérifier que c'est une requête POST / Check if it's a POST request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
                exit;
            }
            
            // Vérifier le Content-Type / Check Content-Type
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') === false) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Content-Type invalide']);
                exit;
            }
            
            // Lire les données JSON / Read JSON data
            $json = file_get_contents('php://input');
            if ($json === false) {
                throw new Exception('Impossible de lire les données JSON');
            }
            
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON invalide: ' . json_last_error_msg());
            }
            
            // Vérifier que les données sont valides / Validate data
            if (!$data || !isset($data['user_id'])) {
                throw new Exception('Données manquantes (user_id)');
            }
            
            $userId = (int) $data['user_id'];
        
            // Vérifier que l'ID utilisateur est valide / Check if user ID is valid
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            // Vérifier que l'utilisateur existe / Check if user exists
            $userManager = new UserManager();
            $user = $userManager->readOneUser($userId);
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            // Vérifier le format des données utilisateur / Check user data format
            if (!is_array($user)) {
                throw new Exception('Format de données utilisateur inattendu');
            }
            
            $userEmail = $user['email'] ?? '';
            $userFirstname = $user['firstname'] ?? '';
            
            if (empty($userEmail)) {
                throw new Exception('Email utilisateur non trouvé');
            }
            
            // Générer un nouveau mot de passe / Generate new password
            $func = new Utils();
            $passwordGenerated = $func->generateRandomPassword(12);
            $passwordHash = password_hash($passwordGenerated, PASSWORD_BCRYPT);
            
            // Réinitialiser le mot de passe / Reset password
            $success = $userManager->resetOneUserPasswordAndStatus($userId, $passwordHash);
            
            if ($success) {
                // Envoyer l'email / Send email
                $emailSent = false;
                try {
                    $sendEmail = new SendEmail();
                    $sendEmail->sendPasswordResetEmail($userFirstname, $userEmail, $passwordGenerated);
                    $emailSent = true;
                } catch (Exception $emailError) {
                    // Email non envoyé mais le mot de passe a été réinitialisé / Email not sent but password was reset
                    $emailSent = false;
                }
                
                // Réponse JSON / JSON response
                $response = [
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès',
                    'user_id' => $userId,
                    'new_status' => 0,
                    'status_text' => 'Inactif',
                    'email_sent' => $emailSent
                ];
                
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                throw new Exception('Échec de la réinitialisation du mot de passe en base de données');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            
            $errorResponse = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
        }
        
        // S'assurer qu'aucune sortie supplémentaire n'est envoyée / Ensure no additional output is sent
        exit;
    }

/**
 * Update user role
 * Met à jour le rôle d'un utilisateur
 * 
 * @return void Outputs JSON response
 */
    public function resetRole() {
        // Check if request method is POST / Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            return;
        }
        
        // Validate Content-Type header / Valider l'en-tête Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Content-Type invalide']);
            return;
        }
        
        try {
            // Read and decode JSON data / Lire et décoder les données JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // Validate required parameters / Valider les paramètres requis
            if (!$data || !isset($data['user_id']) || !isset($data['role'])) {
                throw new Exception('Données manquantes (user_id ou role)');
            }
            
            // Cast parameters to integers / Convertir les paramètres en entiers
            $userId = (int) $data['user_id'];
            $role = (int) $data['role'];
            
            // Validate user ID / Valider l'ID utilisateur
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            // Validate role (1 = user, 2 = admin) / Valider le rôle (1 = utilisateur, 2 = admin)
            if (!in_array($role, [1, 2])) {
                throw new Exception('Rôle invalide. Doit être 1 (utilisateur) ou 2 (administrateur)');
            }
            
            // Check if user exists / Vérifier que l'utilisateur existe
            $userManager = new UserManager();
            $user = $userManager->readOneUser($userId);
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            // Update user role / Mettre à jour le rôle
            $success = $userManager->updateRole($userId, $role);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Rôle mis à jour avec succès',
                    'user_id' => $userId,
                    'new_role' => $role
                ]);
            } else {
                throw new Exception('Échec de la mise à jour en base de données');
            }
            
        } catch (Exception $e) {
            // Handle errors and return error response / Gérer les erreurs et retourner la réponse d'erreur
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

/**
 * Update user status (active/inactive)
 * Met à jour le statut d'un utilisateur (actif/inactif)
 * 
 * @return void Outputs JSON response
 */
    public function resetStatus() {
        // Check if request method is POST / Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            return;
        }
        
        // Validate Content-Type header / Valider l'en-tête Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Content-Type invalide']);
            return;
        }
        
        try {
            // Read and decode JSON data / Lire et décoder les données JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // Validate required parameters / Valider les paramètres requis
            if (!$data || !isset($data['user_id']) || !isset($data['status'])) {
                throw new Exception('Données manquantes (user_id ou status)');
            }
            
            // Cast parameters to integers / Convertir les paramètres en entiers
            $userId = (int) $data['user_id'];
            $status = (int) $data['status'];
            
            // Validate user ID / Valider l'ID utilisateur
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            // Validate status (0 = inactive, 1 = active) / Valider le statut (0 = inactif, 1 = actif)
            if (!in_array($status, [0, 1])) {
                throw new Exception('Statut invalide. Doit être 0 (inactif) ou 1 (actif)');
            }
            
            // Check if user exists / Vérifier que l'utilisateur existe
            $userManager = new UserManager();
            $user = $userManager->readOneUser($userId);
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            // Create user object and update status / Créer l'item utilisateur et mettre à jour le statut
            $userObj = new Users();
            $userObj->setId($userId);
            
            $success = $userManager->updateStatus($userId, $status);
            
            if ($success) {
                // Return success response with status text / Retourner la réponse de succès avec le texte du statut
                echo json_encode([
                    'success' => true,
                    'message' => 'Statut mis à jour avec succès',
                    'user_id' => $userId,
                    'new_status' => $status,
                    'status_text' => $status == 1 ? 'Actif' : 'Inactif'
                ]);
            } else {
                throw new Exception('Échec de la mise à jour du statut en base de données');
            }
            
        } catch (Exception $e) {
            // Handle errors and return error response / Gérer les erreurs et retourner la réponse d'erreur
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}