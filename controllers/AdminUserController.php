<?php

class AdminUserController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
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
            $tm = new CSRFTokenManager();
             $csrf_token= $tm->generateCSRFToken();
         
            
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
            'csrf_token'=> $csrf_token,
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
 * Réinitialise le mot de passe d'un utilisateur (Admin uniquement)
 * Envoie la réponse en JSON pour traitement AJAX
 */
public function resetPassword(): void
{
    // ✅ CRITICAL : Empêcher tout output avant le JSON
    ob_clean(); // Nettoyer le buffer de sortie
    
    // Vérifier que c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json'); // ✅ AJOUTÉ
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }

    // Vérifier que c'est du JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    try {
        $func = new Utils();
        
        // Lire les données POST (application/x-www-form-urlencoded)
        if (!$func->checkPostKeys(['id', 'csrf_token'])) {
            throw new Exception('Données manquantes (id ou csrf_token)');
        }

        $userId = (int) $_POST['id'];
        $csrfToken = $_POST['csrf_token'];

        // Validation de l'ID
        if ($userId <= 0) {
            throw new Exception('ID utilisateur invalide');
        }

        // Vérification CSRF
        $tm = new CSRFTokenManager();
        if (!$tm->validateCSRFToken($csrfToken)) {
            throw new Exception('Token CSRF invalide');
        }

        // Récupérer l'utilisateur
        $um = new UserManager();
        $user = $um->readOneUser($userId);
        
        if (!$user) {
            throw new Exception('Utilisateur non trouvé');
        }

        // Générer le nouveau mot de passe
        $newPassword = $func->generateRandomPassword(12);
        $passwordHash = password_hash($passwordGenerated, PASSWORD_BCRYPT);

        // Mettre à jour en BDD (mot de passe + statut à 0)
        $resetOk = $um->resetOneUserPasswordAndStatus($userId, $passwordHash);

        if (!$resetOk) {
            throw new Exception('Échec de la mise à jour en base de données');
        }
        
         $firstname = $user['firstname'];
          $email = $user['email'];
        // Envoyer l'email
        $sendEmail = new SendEmail();
        $sendEmail->sendPasswordResetEmail(
            $firstname,
            $email,
            $newPassword
        );

        // Réponse de succès
        header('Content-Type: application/json'); // ✅ AJOUTÉ
        echo json_encode([
            'success' => true,
            //'message' => 'Mot de passe réinitialisé avec succès. Un email a été envoyé.',
            'user_id' => $userId
        ]);

    } catch (Exception $e) {
        error_log("Erreur resetPassword: " . $e->getMessage());
        http_response_code(400);
        header('Content-Type: application/json'); // ✅ AJOUTÉ
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
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