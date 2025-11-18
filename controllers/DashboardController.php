<?php

/*require_once("models/ErrorMessages.php");
require_once("models/Contacts.php");
require_once("models/TimesModels.php");
require_once("models/SendEmail.php");
require_once("models/Avatars.php");
require_once("services/Utils.php");
require_once("services/CSRFTokenManager.php");
require_once("managers/AvatarManager.php");
require_once("managers/AbstractManager.php");
require_once("managers/UserManager.php");
require_once("managers/MessageManager.php");
require_once("managers/StoryManager.php");
require_once("managers/ColoringManager.php");
require_once("managers/ContactsManager.php");
require_once("managers/StoryCategorieManager.php");
require_once("managers/ErrorManager.php");
require_once("services/CSRFTokenManager.php");*/




class DashboardController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
    }

 /*********************************PAGE ADMIN*****************************/   
    public function displayDashboard(): void {
        $avatar = new AvatarManager();
        $scripts = $this->addScripts(['public/assets/js/common.js', 'public/assets/js/global.js', 'public/assets/js/home.js','public/assets/js/formController.js']);
        $nbrMessages = (new ContactsManager())->getAllNotRead();    

        // Render the 'home' view with the 'layout' layout and pass the necessary data to the view
        $this->render('dashboard.html.twig', [
            'sesssion_user' =>$_SESSION['user'],
            
            'nbrMessages'   => $nbrMessages,
            
        ], $scripts);
    }

   
   


/*****************************GESTION DES MESSAGES DES UTILISATEURS***********/    
    

    //afficher les messages et afficher sur la vue messagerie
public function displayMessages() {
    $messages = (new ContactsManager())->getAll();
    $tm = new CSRFTokenManager();
    $token = $tm->generateCSRFToken();
    $_SESSION['csfr_token'] = $token;
    $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js']);
    
    // Vérifier si les messages existent dans la session avant de les utiliser
    $errorMessage = $_SESSION['error_message'] ?? null;
    $successMessage = $_SESSION['success_message'] ?? null;
    
    $this->render('messages.html.twig', [
        'messages' => $messages, 
        'error_message' => $errorMessage, 
        'success_message' => $successMessage, 
        'csrf_token' => $token
    ], $scripts);
    
    // Optionnel : supprimer les messages après affichage pour éviter qu'ils persistent
    unset($_SESSION['error_message']);
    unset($_SESSION['success_message']);
}
    
    // lire un message
    public function readMessage() {
        $tm = new CSRFTokenManager();
        $token = $tm->generateCSRFToken();
        $_SESSION['csfr_token']= $token;
        $contact = new Contacts();                                                
        $id = $contact->setId($_GET['id']);  
        //$com = new ContactsManager();
        //$message = $com->getOne($_GET['id']);
        
        $updateStatut = (new ContactsManager())->updateStatut($contact);
        $message = (new ContactsManager())->getOne($_GET['id']);

        //var_dump($message);
        
        
        $this->render('readMessage.html.twig', [
            'message'   => $message,   
            'csrf_token'     => $token,
            "session_tokenVerify"=> $token,
        ]);
        
        return;
    }
      //repondre au message  
    public function response() {  
        
        // Vérifier si le champ "reponse" est bien rempli / Token / captcha
        // Si ton tableau d'erreur contient une erreur
        // Afficher les messages d'erreur / Pas d'envoi
        // Si tout est ok, envoi avec sendMail()
        $func = new Utils();
        
         
       
        
        if (!$func->checkPostKeys(['response', 'firstname', 'receptedDate', 'content', 'subject', 'email', 'csrf_token'])) {   
            $_SESSION['error_message'] = "Les champs n'existent pas.";
            $this->render("readMessage.html.twig", ['error_message'=> $_SESSION['error_message'], 'csrf_token' => $token], [$scripts]);
            exit();
        } else {
            
            $data = [
                'response'=> $func->e($_POST['response']),
                'firstname'=> $func->e($_POST['firstname']),
                'receptedDate'=> $func->e($_POST['receptedDate']),
                'content'=> $func->e($_POST['content']),
                'subject'=> $func->e($_POST['subject']),
                'email' => $func->e(strtolower(trim($_POST['email']))),
                'csrf_token'=>$_POST['csrf_token'],
            ];
            
                
                
        
               $tm = new CSRFTokenManager();
                $tokenVerify= $tm->validateCSRFToken($data['csrf_token']);
                  
                if($_SESSION['csrf_token'] == $tokenVerify ) {
            //var_dump($_POST); die;
            
            
            
                $email = $data['email'];
                $firstname = $data['firstname'];
                $subjectReceived = $data['subject'];
                $contentReceived = $data['content'];
                $receptedDate =$data['receptedDate'];
                $response = $data['response'];
            
                 $sendEmail = new SendEmail();
                    $responseMail = $sendEmail->sendEmailResponse($email, $subjectReceived, $firstname, $contentReceived, $receptedDate, $response );
                    
                    $this->displayMessages(); 
                }
                else {
                    $_SESSION['error_çmessage']="Token Invalid";
                   $this->render("readMessage.html.twig", ['error_message'=> $_SESSION['error_message'], 'csrf_token' => $token], [$scripts]); 
                }
                
        }
    }
    


    
    // delete le message auquel on a repondu
    public function deleteMessage() {
        
        
        $func = new Utils(); 

        
        
        if (!$func->checkPostKeys(['id','csrf_token'])) {   
            $_SESSION['error_message'] = "Les champs n'existent pas.";
            $this->render("readMessage.html.twig", ['error_message'=> $_SESSION['error_message'], 'csrf_token' => $token], $scripts);
            exit();
        } else {
            
            $data = [
                'id' => $_POST['id'],
                'csrf_token'=>$_POST['csrf_token'],
            ];
            
                
                
        
                $tm = new CSRFTokenManager();
                $tokenVerify= $tm->validateCSRFToken($data['csrf_token']);
                  
                if($_SESSION['csrf_token'] == $tokenVerify ) {
            //var_dump($_POST); die;
                    (new ContactsManager())->deleteOne($data['id']);
            
                    $messages = (new ContactsManager())->getAll();
                    $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js']);
                    $this->render('messages.html.twig', [
                        'page'      => "Messagerie",
                        'messages'  => $messages
                 ], $scripts);
                } else { 
                    $tm = new CSRFTokenManager();
                    $token = $tm->generateCSRFToken();
                    $_SESSION['error_message']="token invalid";
                    $this->render("readMessage.html.twig", ['error_message'=> $_SESSION['error_message'], 'csrf_token' => $token], $scripts);
                }
        }
    }

/******************************************************************/

/**************************ACTUALITES*******************************/

    /**
     * Displays the homepage with the latest news
     *
     * This method is responsible for fetching all the news articles via the `ActualityManager` model,
     * then rendering the 'home' view using the 'layout' layout, passing the necessary data
     * to the view, including the integral configuration and the list of news articles.
     *
     * @param   void
     * @return  void
     */
    public function displayActuality() {
        $this->render('actuality.html.twig', 'layout', [
            'page'              => "Actualité"
        ]);
    }

    
/**********************************************************************/    
  
  /*  public function changeProfileByAdmin() {        
        $user = new Users(); 
        $user->setId($_GET['id']);  
        $user->setPassword($_GET['password']);

        $action = $_GET['action'];
        $id = $_GET['id'];
        $password=password_hash($_GET['password']);

        switch($action){
            case "avatar":
                $this->changeAvatar($id);
                break;
            case "password":                
                (new UserManager())->changePassword($user, $password);
                break;                
            case "bann":                
                (new UserManager())->changeStatut($user, 3);
                break; 
            case "role":
                (new UserManager())->changeStatut($user, 2);
            default:
            break;
        }

        $this->redirectTo("displayOneUser&id=".$id);
    }
        
    
    public function modifGames() {
        
    }
    
    public function modifColorings() {
        
    }*/
 
 
/**********************GESTION DES AVATARS ADMIN**************/
    /**
     * Affiche la page de modification des avatars
     * Display avatar modification page
     */
   public function modifAvatarAdmin() 
{
    // Récupération de tous les avatars / Get all avatars
    $avatars = (new AvatarManager())->findAllAvatars();

    // Génération du token CSRF / Generate CSRF token
    $tm = new CSRFTokenManager();
    if (!isset($_SESSION['csrf_token'])) {
        $token = $tm->generateCSRFToken();
    } else {
        $token = $_SESSION['csrf_token'];
    }

    // Récupération puis suppression des messages / Get then clear flash messages
    $error = $_SESSION['error_message'] ?? null;
    $success = $_SESSION['success_message'] ?? null;
    unset($_SESSION['error_message'], $_SESSION['success_message']);

    // Ajout des scripts nécessaires / Add required scripts
    $scripts = $this->addScripts([
        'public/assets/js/common.js',
        'public/assets/js/formController.js',
        'public/assets/js/modifyAvatarAdmin.js'
    ]);

    // Rendu de la vue / Render view
    $this->render('modifAvatarAdmin.html.twig', [
        'avatars'         => $avatars,
        'error_message'   => $error,
        'success_message' => $success,
        'csrf_token'      => $token
    ], $scripts);
}
    
    /**
     * Supprime un avatar
     * Delete an avatar
     */
    public function deleteAvatar()
{
    $func = new Utils();

    // Vérification des champs POST requis
    if (!$func->checkPostKeys(['id', 'csrf_token'])) {
        $_SESSION['error_message'] = "Les champs n'existent pas.";
        $this->redirectToModifAvatar();
        return;
    }

    // Préparation des données
    $data = [
        'id' => $_POST['id'],
        'csrf_token' => $_POST['csrf_token'],
    ];

    // Vérification du token CSRF
    $tm = new CSRFTokenManager();
    $tokenValid = $tm->validateCSRFToken($data['csrf_token']);

    if ($tokenValid) {
        if ($data['id'] == 4) {
            $_SESSION['error_message'] = "Impossible de supprimer l'avatar par défaut.";
            $this->redirectToModifAvatar();
            return;
        }

        $avatarManager = new AvatarManager();

        // Réaffectation des utilisateurs avant suppression
        $avatarManager->reassignUsersToDefaultAvatar($data['id'], 4);

        // Suppression de l'avatar
        $success = $avatarManager->delete($data['id']);

        if ($success) {
            $_SESSION['success_message'] = "Avatar supprimé avec succès. Les utilisateurs liés ont été réaffectés.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression de l'avatar.";
        }

        $this->redirectToModifAvatar();
    } else {
        $_SESSION['error_message'] = "Token invalide. Tentative de suppression refusée.";
        $this->redirectToModifAvatar();
    }
}
    
    /**
     * Ajoute un nouvel avatar
     * Add a new avatar
     */
    public function addAvatar() 
    {
        // Nettoyage des messages de session / Clear session messages
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        $func = new Utils();
        
        // Vérification des champs POST requis / Check required POST fields
        if (!$func->checkPostKeys(['name', 'url', 'description', 'caracteristique', 'qualite', 'csrf_token'])) {   
            $_SESSION['error_message'] = "Tous les champs sont requis.";
            $this->redirectToModifAvatar();
            return;
        }
        
        // Préparation et nettoyage des données / Prepare and sanitize data
        $data = [
            'name' => $func->e($_POST['name']),
            'url' => $func->e($_POST['url']),
            'description' => $func->e($_POST['description']),
            'caracteristique' => $func->e($_POST['caracteristique']),
            'qualite' => $func->e($_POST['qualite']),
            'csrf_token' => $_POST['csrf_token'],
        ];
        
        // Vérification du token CSRF / Verify CSRF token
        $tm = new CSRFTokenManager();
        $tokenVerify = $tm->validateCSRFToken($data['csrf_token']);
        
        if ($_SESSION['csrf_token'] == $tokenVerify) {
            // Token valide - Création de l'avatar / Valid token - Create avatar
            $avatar = new Avatars(
                null, 
                $data['name'], 
                $data['url'], 
                $data['description'], 
                $data['caracteristique'], 
                $data['qualite']
            );
            
            $avatarManager = new AvatarManager();

$existing = $avatarManager->getByName($data['name']);
$existing2 = $avatarManager->getByUrl($data['url']);

if ($existing !== null) {
    $_SESSION['error_message'] = "Un avatar avec ce nom existe déjà.";
    $this->redirectToModifAvatar();
    return;
}

if ($existing2 !== null) {
    $_SESSION['error_message'] = "Un avatar avec cette image existe déjà.";
    $this->redirectToModifAvatar();
    return;
}
            
            // Ajout en base de données / Add to database
            $success = (new AvatarManager())->addAvatar($avatar);
            
            if ($success) {
                $_SESSION['success_message'] = "Avatar ajouté avec succès.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'ajout de l'avatar.";
            }
            
            $this->redirectToModifAvatar();
        } else {
            // Token invalide / Invalid token
            $_SESSION['error_message'] = "Token invalide. Ajout refusé.";
            $_SESSION['success_message'] = "Avatar ajouté avec succès.";
            
            $this->redirectToModifAvatar();
        }
    }
    
    /**
     * Méthode utilitaire pour rediriger vers la page de modification
     * Utility method to redirect to modification page
     */
    private function redirectToModifAvatar() 
    {
        // Génération d'un nouveau token / Generate new token
        $tm = new CSRFTokenManager();
        $token = $tm->generateCSRFToken();
        $_SESSION['csrf_token'] = $token;
        
        // Récupération des avatars / Get avatars
        $avatars = (new AvatarManager())->findAllAvatars();
        
         // Récupération puis suppression des messages
         $errors = "";
    $error = $_SESSION['error_message'] ?? null;
    $success = $_SESSION['success_message'] ?? null;
    unset($_SESSION['error_message'], $_SESSION['success_message']);
    
        $scripts = $this->addScripts(['public/assets/js/common.js', 'public/assets/js/formController.js', 'public/assets/js/modifyAvatarAdmin.js']);
        
        // Rendu de la vue / Render view
        $this->render("modifAvatarAdmin.html.twig", [
            'avatars' => $avatars, 
            'error_message' => $error,
            'success_message' => $success,
            'csrf_token' => $token
        ], $scripts);
        
        exit();
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
        $scripts = $this->addScripts(['public/assets/js/ajaxSearchUsers.js']);
        $users     = (new UserManager())->getAllUsers() ;
        //var_dump($users);
      

        $this->render('users.html.twig', ['users' => $users ], $scripts);
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
        $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js','public/assets/js/ajaxOneUser.js']);
        
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
            'avatars'   => $avatars
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
                        'qualite' => $newAvatar->getQualite()
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

/*********************************************************************/
/*********************ADMIN GESTION DES StoryS*********************/ 
 
 
/**
     * Fonction principale pour gérer les Storys
     * Gère l'ajout d'éléments et calcule les combinaisons manquantes
     * @return array Données pour la vue
     */
    /**
     * Méthode principale pour la route StoriesAdmin
     * Gère l'affichage de la vue avec toutes les données nécessaires
     * @return void Affiche la vue storiesAdmin.html.twig
     */
public function ManageStories(): void {
    // Traitement des données POST avant l'affichage
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handlePostRequests();
        // Redirection après traitement pour éviter la re-soumission
        //header('Location: index.php?route=StoriesAdmin');
       // exit;
    }
    
    // Récupération des données pour la gestion des Storys
    $viewData = $this->manageStoriesLogic();
    $categorieManager = new StoryCategorieManager();
$categories = $categorieManager->getAllCategories(); 
    //$scripts = $this->addScripts(['public/assets/js/storyAdmin.js']);
    
    $csrfToken = new CSRFTokenManager();
    $token = $csrfToken->generateCSRFToken();
    
    // Fusion des données avec les éléments nécessaires pour le template
    $templateData = array_merge($viewData, [
        'messages' => '',
        'error_message' => $_SESSION['error_message'] ?? '',
        'success_message' => $_SESSION['success_message'] ?? '',
        'csrf_token' => $token,
        'categories' => $categories,
    ]);
    $scripts = $this->addScripts(['public/assets/js/formController.js', 'public/assets/js/common.js', 'public/assets/js/global.js', 'public/assets/js/home.js', 'public/assets/js/storyAdmin.js']);
    
    $this->render('storiesAdmin.html.twig', $templateData, $scripts);
    
    // Nettoyage des messages après affichage
    unset($_SESSION['error_message'], $_SESSION['success_message']);
}

/**
 * Gère toutes les requêtes POST (ajout d'éléments et d'Storys)
 */
private function handlePostRequests(): void {
    $csrfToken = new CSRFTokenManager();
    
    // Validation du token CSRF
    if (!$csrfToken->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Token de sécurité invalide";
        return;
    }
    
    // Traitement selon le type de soumission
    if (isset($_POST['submit_add'])) {
        $this->processAddElement();
    } elseif (isset($_POST['submit_stories'])) {
        $this->processAddStories();
    }
}

/**
 * Traite l'ajout d'un nouvel élément (item, location, character)
 */
private function processAddElement(): void {
    $result = $this->handleElementAddition();
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

/**
 * Traite l'ajout de nouvelles Storys
 */
private function processAddStories(): void {
    $result = $this->handleStoriesCreation();
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

/**
 * Logique métier pour la gestion des Storys
 * Séparée de l'affichage pour une meilleure testabilité
 * @return array Données pour la vue
 */
public function manageStoriesLogic(): array {
    // Récupération des statistiques actuelles
    $currentStats = $this->getCurrentStats();
    
    // Calcul des nouvelles combinaisons nécessaires
    $missingCombinations = $this->getMissingCombinations();
    $nbTextareasToDisplay = count($missingCombinations);
    
    // Récupération des catégories pour le formulaire
    $StoryCategorieManager = new StoryCategorieManager();
    $categories = $StoryCategorieManager->getAllCategories();
    
    return [
        // Statistiques actuelles (noms compatibles avec le template Twig)
        'O_actuel' => $currentStats['nb_items'],
        'L_actuel' => $currentStats['nb_locationx'], 
        'P_actuel' => $currentStats['nb_characters'],
        'H_existantes' => $currentStats['nb_Storys_existantes'],
        'total_combinaisons_possibles' => $currentStats['nb_combinaisons_possibles'],
        
        // Données pour les nouvelles combinaisons
        'nb_textareas_to_display' => $nbTextareasToDisplay,
        'new_combinations' => $missingCombinations,
        
        // Données supplémentaires pour la vue
        'categories' => $categories,
        
        // Informations sur la requête pour les conditions Twig
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'has_post_data' => $_SERVER['REQUEST_METHOD'] === 'POST'
    ];
}

/**
 * Récupère toutes les combinaisons manquantes avec les détails nécessaires
 * @return array Liste des combinaisons manquantes avec noms
 */
private function getMissingCombinations(): array {
    $itemManager = new itemManager();
    $locationManager = new locationManager();
    $characterManager = new characterManager();
    $StoryManager = new StoryManager();
    
    // Récupération de tous les éléments
    $items = $itemManager->getAllitems();
    $locationx = $locationManager->getAlllocationx();
    $characters = $characterManager->getAllcharacters();
    
    // Récupération des combinaisons existantes
    $existingCombinations = $StoryManager->getExistingCombinations();
    
    // Calcul des combinaisons manquantes
    $missingCombinations = [];
    
    foreach ($items as $item) {
        foreach ($locationx as $location) {
            foreach ($characters as $character) {
                // Créer une clé pour vérifier dans les combinaisons existantes
                $checkKey = $item->getId() . '-' . $location->getId() . '-' . $character->getId();
                
                // Vérifier si cette combinaison existe déjà
                if (!isset($existingCombinations[$checkKey])) {
                    $missingCombinations[] = [
                        'item_id' => $item->getId(),
                        'item_nom' => $item->getitemName(),
                        'location_id' => $location->getId(),
                        'location_nom' => $location->getlocationName(),
                        'character_id' => $character->getId(),
                        'character_nom' => $character->getPersoName()
                    ];
                }
            }
        }
    }
    
    return $missingCombinations;
}

/**
 * Récupère les statistiques actuelles (nombre d'items, locationx, characters, Storys)
 * @return array Statistiques actuelles
 */
private function getCurrentStats(): array {
    $itemManager = new itemManager();
    $nbitems = $itemManager->countitems();
    
    $locationManager = new locationManager();
    $nblocationx = $locationManager->countlocationx();
    
    $characterManager = new characterManager();
    $nbcharacters = $characterManager->countcharacters();
    
    $StoryManager = new StoryManager();
    $storyManager = $StoryManager->countStories();
        
    return [
        'nb_items' => $nbitems,
        'nb_locationx' => $nblocationx,
        'nb_characters' => $nbcharacters,
        'nb_Storys_existantes' => $storyManager,
        'nb_combinaisons_possibles' => $StoryManager->calculateTotalCombinations(
            $nbitems, $nblocationx, $nbcharacters
        )
    ];
}

/**
 * Gère l'ajout d'un nouvel élément (item, location ou character)
 * @return array Résultat de l'opération (success, message)
 */
public function handleElementAddition(): array {
    $typeAjout = $_POST['type_ajout'] ?? '';
    $nomNouvelElement = trim($_POST['nom_nouvel_element'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $alt = trim($_POST['alt'] ?? '');
    
    if (empty($nomNouvelElement)) {
        return ['success' => false, 'message' => "Veuillez entrer un nom pour le nouvel élément."];
    }
    
    try {
        $elementId = null;
        
        switch ($typeAjout) {
            case 'item':
                $itemManager = new itemManager();
                $elementId = $itemManager->additem([
                    'item_name' => $nomNouvelElement,
                    'item_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "item '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            case 'location':
                $locationManager = new locationManager();
                $elementId = $locationManager->addlocation([
                    'location_name' => $nomNouvelElement,
                    'location_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "location '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            case 'character':
                $characterManager = new characterManager();
                $elementId = $characterManager->addcharacter([
                    'perso_name' => $nomNouvelElement,
                    'perso_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "character '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            default:
                return ['success' => false, 'message' => "Type d'ajout invalide."];
        }
        
        return ['success' => true, 'message' => $message, 'element_id' => $elementId];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Gère la création d'Storys multiples
 * @return array Résultat de l'opération
 */
// Dans votre méthode principale qui traite les actions
/*public function handleAction() {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_single_story':
            return $this->handleSingleStoryCreation();
            
        case 'add_stories':
            return $this->handleStoriesCreation();
            
        // autres actions...
    }
}*/


/**
 * Gère la création d'une Story unique
 * @return array Résultat de l'opération
 */
private function handleSingleStoryCreation(): array {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_single_story':
            $result = $this->handleSingleStoryCreation();
            break;
            
        case 'add_multiple_stories':
            $result = $this->handleStoriesCreation();
            break;
      echo "<pre>";
var_dump($_POST);
echo "</pre>";      
    
    $story = $_POST['story'] ?? [];
    echo "📦 Story brut :";
var_dump($story);
    
    // Debug
    error_log("=== SINGLE STORY CREATION ===");
    error_log("Story data: " . print_r($story, true));
    
    if (!is_array($story)) {
        return ['success' => false, 'message' => 'Données invalides'];
    }
    
    // Vérifier le contenu
    $func = new Utils();

$content = trim($story['Story_content'] ?? '');
if (empty($content)) {
    return ['success' => false, 'message' => 'Contenu requis'];
}

// Nettoyer le HTML avant insertion
$sanitizedContent = $func->sanitizeHtml($content);

// Mettre à jour le tableau $story pour qu’il contienne la version filtrée
$story['Story_content'] = $sanitizedContent;
    
    // Vérifier les IDs obligatoires
    $characterId = !empty($story['character_id']) ? (int)$story['character_id'] : null;
    $itemId = !empty($story['item_id']) ? (int)$story['item_id'] : null;
    $locationId = !empty($story['location_id']) ? (int)$story['location_id'] : null;
    
    if ($locationId === null || $locationId === 0) {
        return ['success' => false, 'message' => 'location requis'];
    }
    if ($characterId === null || $characterId === 0) {
        return ['success' => false, 'message' => 'character requis'];
    }
    if ($itemId === null || $itemId === 0) {
        return ['success' => false, 'message' => 'item requis'];
    }
    
    try {
        $StoryManager = new StoryManager();
        $storyData = [
    'Story_titre' => !empty($story['titre']) ? trim($story['titre']) : "Story générée",
    'Story_categorie' => !empty($story['categorie']) ? $story['categorie'] : null,
    'character' => $characterId,
    'item' => $itemId,
    'location' => $locationId,
    'Story_content' => $sanitizedContent,
    'audio' => !empty($story['audio']) ? $story['audio'] : null,
    'url' => !empty($story['url']) ? $story['url'] : null
];
echo ($storyData);
exit;

        error_log("StoryData envoyé à addStory: " . print_r($storyData, true));
        $StoryManager->addStory($storyData);
        return ['success' => true, 'message' => 'Story créée avec succès !'];
        
    } catch (Exception $e) {
        error_log("Error creating single story: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        $_SESSION['success_message'] = 'Story créée avec succès !';
        $_SESSION['error_message'] = $result['message'];
    }
}
}
}


/**
 * Gère la création d'Storys multiples
 * @return array Résultat de l'opération
 */
public function handleStoriesCreation(): array {
    $stories = $_POST['stories'] ?? [];
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    // Debug : vérifier la structure des données reçues
    error_log("=== DEBUG POST DATA ===");
    error_log("Full POST: " . print_r($_POST, true));
    error_log("Stories array: " . print_r($stories, true));
    error_log("Stories count: " . count($stories));
    
    if (empty($stories)) {
        return ['success' => false, 'message' => 'Aucune Story à traiter'];
    }
    
    $StoryManager = new StoryManager();
    
    foreach ($stories as $index => $story) {
        // Vérifier que $story est bien un tableau
        if (!is_array($story)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : données invalides";
            continue;
        }
        
        // Debug détaillé pour chaque Story
        error_log("=== PROCESSING STORY #" . ($index + 1) . " ===");
        error_log("Story raw data: " . print_r($story, true));
        
        // Debug spécifique pour les IDs (noms corrects selon le Twig)
        error_log("character: '" . ($story['character'] ?? 'NOT SET') . "'");
        error_log("item: '" . ($story['item'] ?? 'NOT SET') . "'");
        error_log("location: '" . ($story['location'] ?? 'NOT SET') . "'");
        error_log("Story_content: '" . ($story['Story_content'] ?? 'NOT SET') . "'");
        
        // Vérifier la présence des IDs depuis la combinaison (noms corrects)
        $characterId = !empty($story['character']) ? (int)$story['character'] : null;
        $itemId = !empty($story['item']) ? (int)$story['item'] : null;
        $locationId = !empty($story['location']) ? (int)$story['location'] : null;
        
        error_log("After processing - character: $characterId, item: $itemId, location: $locationId");
        
        // Vérifier que les champs obligatoires sont remplis
        $content = trim($story['Story_content'] ?? '');
        if (empty($content)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : contenu requis";
            continue;
        }
        
        // Vérifier que les IDs obligatoires sont présents
        if ($locationId === null || $locationId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : location requis (ID manquant dans la combinaison)";
            continue;
        }
        
        if ($characterId === null || $characterId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : character requis (ID manquant dans la combinaison)";
            continue;
        }
        
        if ($itemId === null || $itemId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : item requis (ID manquant dans la combinaison)";
            continue;
        }
        if (empty($content)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : contenu requis";
            continue;
        }
        
        try {
            $storyData = [
                'Story_titre' => !empty($story['Story_titre']) ? trim($story['Story_titre']) : "Story #" . ($index + 1),
                'Story_categorie' => !empty($story['Story_categorie']) ? $story['Story_categorie'] : null,
                'character' => $characterId,
                'item' => $itemId,
                'location' => $locationId,
                'Story_content' => $content,
                'audio' => !empty($story['audio']) ? $story['audio'] : null,
                'url' => !empty($story['url']) ? $story['url'] : null
            ];
            
            // Debug : vérifier les données avant insertion
            error_log("Story data to insert: " . print_r($storyData, true));
            
            $StoryManager->addStory($storyData);
            $successCount++;
            
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : " . $e->getMessage();
            error_log("Error creating story #" . ($index + 1) . ": " . $e->getMessage());
        }
    }
    
    // Construction du message de retour
    $message = "";
    if ($successCount > 0) {
        $message = "{$successCount} Story(s) créée(s) avec succès.";
    }
    
    if ($errorCount > 0) {
        if (!empty($message)) {
            $message .= " ";
        }
        $message .= "{$errorCount} erreur(s) : " . implode(', ', $errors);
    }
    
    // Si aucune Story n'a été créée et qu'il n'y a pas d'erreurs spécifiques
    if ($successCount === 0 && $errorCount === 0) {
        $message = "Aucune Story n'a pu être traitée";
    }
    
    return [
        'success' => $successCount > 0, 
        'message' => $message,
        'successCount' => $successCount,
        'errorCount' => $errorCount
    ];
}


/**
 * Méthode alternative pour récupérer uniquement les données sans affichage
 * Utile pour les API ou tests
 * @return array Données du dashboard
 */
public function getDashboardData(): array {
    $itemManager = new itemManager();
    $locationManager = new locationManager();
    $characterManager = new characterManager();
    $StoryManager = new StoryManager();
    $StoryCategorieManager = new StoryCategorieManager();
    
    return [
        'items' => $itemManager->getAllitems(),
        'locationx' => $locationManager->getAlllocationx(),
        'characters' => $characterManager->getAllcharacters(),
        'categories' => $StoryCategorieManager->getAllCategories(),
        'Storys' => $StoryManager->getAllStories(),
        'stats' => $this->getCurrentStats()
    ];
}

/**
 * Génère un aperçu des combinaisons manquantes (limité)
 * @param int $limit Nombre maximum de combinaisons à afficher
 * @return array Liste limitée des combinaisons manquantes
 */
public function previewMissingCombinations(int $limit = 10): array {
    $missingCombinations = $this->getMissingCombinations();
    return array_slice($missingCombinations, 0, $limit);
}


/************************************************************************/
/********************************GESTION DES ColoringS******************/
    
    public function modifColorings(): void 
{
    
        // Récupération des catégories
        $ccm=new  ColoringCategoriesManager();
        $categories = $ccm->getAllCategoriesColorings();
      

        // Récupération des Colorings groupés par catégorie
        $ColoringsParCategorie = [];
         $cm= new ColoringManager();
        foreach ($categories as $categorie) {
            $ColoringsParCategorie[$categorie['id']] = $cm->getAllColoringsByCategorie($categorie['id']);
        }
        $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js','public/assets/js/formFunction.js', 'public/assets/js/ColoringAdmin.js']);
        // Affichage de la vue
        $this->render('ColoringsAdmin.html.twig', [
            'categories' => $categories,
            'ColoringsParCategorie' => $ColoringsParCategorie
        ], $scripts);
        
    
}
    
   public function addColoring(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $url = $_POST['url'] ?? '';
        $categorieId = (int) ($_POST['categorie_id'] ?? 0);

        if ($name && $url && $categorieId) {
            $cm = new ColoringManager();
            
            if (!$cm->existByUrl($url)) {
                $cm->addColoring([
                    'name' => $name,
                    'description' => $description,
                    'url' => $url,
                    'categorie_Coloring' => $categorieId
                ]);
            } else {
                // Tu pourrais afficher un message d'erreur ici
                // Ou même stocker une notification de type "URL déjà utilisée"
            }
        }

        header('Location: index.php?route=modifColoring');
        exit;
    }
}
    
    
   
    
    /**
     * Gère les catégories
     */
    public function gererCategories(): void 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleCategorieSubmission();
                return;
            }
            
            $categories = $this->ColoringManager->findAllCategories();
            $stats = $this->ColoringManager->getColoringsStats();
            
            $this->render('categoriesAdmin', [
                'categories' => $categories,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            $this->handleError("Erreur lors de la gestion des catégories : " . $e->getMessage());
        }
    }
    
    /**
     * Supprime une catégorie
     */
   public function deleteColoring(): void
{
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int) $_GET['id'];
        $cm = new ColoringManager();
        $cm->deleteColoringById($id);
    }

    header('Location: index.php?route=modifColoring');
    exit;
}
    
    /**
     * Gère la soumission du formulaire de Coloring
     */
    private function handleColoringSubmission(?int $id = null): void 
    {
        // Validation des données
        $errors = $this->validateColoringData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        // Préparation des données
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'categorie_Coloring' => (int)$_POST['categorie_Coloring'],
            'url' => ''
        ];
        
        // Gestion de l'upload de fichier
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['image']);
            
            if ($uploadResult['success']) {
                $data['url'] = $uploadResult['url'];
            } else {
                $this->setFlashMessage('error', $uploadResult['message']);
                return;
            }
        } elseif ($id === null) {
            // Pour un nouveau Coloring, l'image est obligatoire
            $this->setFlashMessage('error', 'L\'image est obligatoire');
            return;
        } else {
            // Pour une modification, garder l'ancienne URL si pas de nouvelle image
            $existingColoring = $this->ColoringManager->findColoringById($id);
            $data['url'] = $existingColoring['url'];
        }
        
        try {
            if ($id === null) {
                // Création
                $this->ColoringManager->createColoring($data);
                $this->redirect('modifColoring', 'Coloring ajouté avec succès', 'success');
            } else {
                // Modification
                $this->ColoringManager->updateColoring($id, $data);
                $this->redirect('modifColoring', 'Coloring modifié avec succès', 'success');
            }
        } catch (Exception $e) {
            $this->handleError("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }
    
    /**
     * Gère la soumission du formulaire de catégorie
     */
    private function handleCategorieSubmission(): void 
    {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'ajouter':
                $this->handleAjouterCategorie();
                break;
            case 'modifier':
                $this->handleModifierCategorie();
                break;
            default:
                $this->redirect('gererCategories', 'Action non reconnue');
        }
    }
    
    /**
     * Ajoute une nouvelle catégorie
     */
    private function handleAjouterCategorie(): void 
    {
        $errors = $this->validateCategorieData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        $data = [
            'categorie_name' => trim($_POST['categorie_name']),
            'categorie_description' => trim($_POST['categorie_description'])
        ];
        
        try {
            $this->ColoringManager->createCategorie($data);
            $this->redirect('gererCategories', 'Catégorie ajoutée avec succès', 'success');
        } catch (Exception $e) {
            $this->handleError("Erreur lors de l'ajout de la catégorie : " . $e->getMessage());
        }
    }
    
    /**
     * Modifie une catégorie existante
     */
    private function handleModifierCategorie(): void 
    {
        $id = (int)$_POST['id'];
        $errors = $this->validateCategorieData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        $data = [
            'categorie_name' => trim($_POST['categorie_name']),
            'categorie_description' => trim($_POST['categorie_description'])
        ];
        
        try {
            $this->ColoringManager->updateCategorie($id, $data);
            $this->redirect('gererCategories', 'Catégorie modifiée avec succès', 'success');
        } catch (Exception $e) {
            $this->handleError("Erreur lors de la modification de la catégorie : " . $e->getMessage());
        }
    }
    
    /**
     * Valide les données d'un Coloring
     */
    private function validateColoringData(): array 
    {
        $errors = [];
        
        if (empty(trim($_POST['name']))) {
            $errors[] = "Le nom est obligatoire";
        }
        
        if (empty(trim($_POST['description']))) {
            $errors[] = "La description est obligatoire";
        }
        
        if (empty($_POST['categorie_Coloring']) || !is_numeric($_POST['categorie_Coloring'])) {
            $errors[] = "La catégorie est obligatoire";
        }
        
        return $errors;
    }
    
    /**
     * Valide les données d'une catégorie
     */
    private function validateCategorieData(): array 
    {
        $errors = [];
        
        if (empty(trim($_POST['categorie_name']))) {
            $errors[] = "Le nom de la catégorie est obligatoire";
        }
        
        return $errors;
    }
    
    /**
     * Gère l'upload d'un fichier image
     */
    private function handleFileUpload(array $file): array 
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Vérifications
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)'];
        }
        
        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('Coloring_') . '.' . $extension;
        $uploadDir = 'uploads/Colorings/';
        
        // Création du répertoire si inexistant
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Upload du fichier
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'url' => '/' . $uploadPath];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
        }
    }
    
    /**
     * Gère les messages flash
     */
    private function handleFlashMessages(): void 
    {
        if (isset($_SESSION['flash_message'])) {
            // Les messages sont disponibles dans la vue via $_SESSION
            // Ils seront supprimés après affichage
        }
    }
    
    /**
     * Définit un message flash
     */
    private function setFlashMessage(string $type, string $message): void 
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    
    /**
     * Gère les erreurs
     */
    private function handleError(string $message): void 
    {
        error_log($message);
        $this->setFlashMessage('error', $message);
        $this->redirect('modifColoring');
    }
    
}
    

