<?php

class AuthController extends AbstractController {
    public function __construct()
    {
        parent::__construct();
    }

    public function login() : void
    {  
         $scripts = $this->addScripts(['assets/js/formController.js',]);
        
        //($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $am = new AvatarManager();
        $token= $tm->generateCSRFToken();
        
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        
        $_SESSION['error_message'] = "";      
        $this->render("login.html.twig", [ 
            'titre' => "Connexion",
            "token" => $token, 
            "avatar" => $am->getById(4), 
            'elapsed_time' =>$elapsedTime] , $scripts);
        return;
        // $template = "register";
        // require "templates/layout.phtml";
    }
    
    public function checkLogin() : void
{   
    
    $func = new Utils();
    $um = new UserManager();
    $am = new AvatarManager();
    
    // Vérifier les clés POST
    if (!$func->checkPostKeys(['email', 'password', 'csrf_token'])) {   
        $_SESSION['error_message'] = "Les champs n'existent pas.";
        $this->redirectTo('login');
        return;
    }
    
    $data = [
        'email' => $func->e(strtolower(trim($_POST['email']))),
        'password' => $_POST['password'],
        'csrf_token' => $_POST['csrf_token'],
    ];
    
    // Vérifier que tous les champs sont remplis
    if (empty($data['email']) || empty($data['password']) || empty($data['csrf_token'])) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires";
        $this->redirectTo('login');
        return;
    }
    
    // Vérifier le token CSRF
    $tm = new CSRFTokenManager();
    if (!$tm->validateCSRFToken($data['csrf_token']) || $_SESSION['csrf_token'] != $data['csrf_token']) {
        $_SESSION['error_message'] = "Token CSRF invalide";
        $this->redirectTo('login');
        return;
    }
    
    // Chercher l'utilisateur par email
    $result = $um->findByEmail($data['email']);
    
    if (!$result) {
        $_SESSION['error_message'] = "Aucun compte trouvé avec cet email";
        $this->redirectTo('register');
        return;
    }
    
    // Vérifier le mot de passe
    if (!password_verify($data['password'], $result['password'])) {
        $_SESSION['error_message'] = "Mot de passe incorrect";
        $this->redirectTo('login');
        return;
    }
    
    // Créer/Mettre à jour la session utilisateur
    $_SESSION["user"] = [
        "id"        => $result['id'],
        "firstname" => $result['firstname'],
        "role"      => $result['role'],
        "statut"    => $result['statut'],
        "email"     => $result['email'],
        "avatar"    => $result['avatar'],
        "age"       => $result['age'],
        "newsletter"=> $result['newsletter'],
    ];
    
    // Nettoyer les variables de session
    unset($_SESSION["csrf_token"]);
    
    // Gérer selon le statut
    if ($result['statut'] == 0) {
        // Utilisateur non validé - rediriger vers modification du mot de passe
        // Créer un tableau qui conserve toutes les informations
$_SESSION["account_status"] = [
    'connected' => false,
    'error_message' => 'Vous devez modifier votre mot de passe pour activer votre compte'
];
        
        // AJOUT IMPORTANT : Nettoyer les autres messages
        unset($_SESSION["error_message"]); // Ancien format
        unset($_SESSION["success_message"]);
        
        $this->redirectTo("displayModify");
        return;
    }
    
    if ($result['statut'] == 1) {
        // Utilisateur validé - connexion réussie
       // Créer un tableau qui conserve toutes les informations

      $_SESSION['start_time']= time();

        $_SESSION["login_data"] = [
            'connected' => 1,
            'start_time' => time(),
            'success_message' => "Connexion réussie ! Bienvenue.",
        ];
        
        // Nettoyer les messages d'erreur
        unset($_SESSION["error_message"]);
        unset($_SESSION["error-message"]);
        
        $timesModels = new TimesModels();
        $func = new Utils();
        $elapsedTime = $timesModels->getElapsedTime();
        $avatar = $am->getById($result['avatar']);
        //$avatar->setUrlMini($func->asset($avatar->getUrlMini()));



        if ($result['role'] == 1 || $result['role'] == 2) {
            $scripts= $this->getDefaultScripts();
            $scripts=$this->addScripts(['assets/js/mess.js'], $scripts);
             $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
            //$avatar->setUrlMini($func->asset($avatar->getUrlMini()));
           // var_dump($avatar);
//exit();
            $this->render("homepageUser.html.twig", [
                'user' =>$_SESSION['user'] ?? null,
                'elapsed_time' => 0, // reset 
                'session' => $_SESSION,
                'start_time' => $_SESSION['start_time'],
                'success_message' => $_SESSION["login_data"]['success_message'] ?? null, 
                "avatar" => $avatar,
                "isValidateUser" => $func->isValidateUser(),
                "isUser" => true
            ], $scripts);
        } else {
            $adminAvatar = $am->getById(7);
            $scripts=$this->addScripts([], $scripts);
            $this->render("dashboard.html.twig", [
                'elapsed_time' => 0, // reset
                'session' => $_SESSION,
                'start_time'=> $_SESSION['start_time'],
                'success_message' => $_SESSION['success_message'], 
                "avatar" => $adminAvatar,
                "isUser" => true
            ], $scripts);
        }
        return;
    }
    
    // Compte banni
    $_SESSION["error_message"] = 'Votre compte a été banni';
$this->redirectTo('homepage');
}



    

    public function register() : void
    {
        //TODO : récupération des données concernant les avatars (appel à AvatarManager)
        
        $am = new AvatarManager();
        $avatars = $am->findAllAvatars();
        $func = new Utils();

        foreach ($avatars as $avatar) {
        $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
        }

    

        //$scripts = $this->addScripts(['assets/js/formController.js','assets/js/formFunction.js']);
         $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        //($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $scripts= $this->addScripts([
            'assets/js/formController.js',
            ]);
        
        $_SESSION['error_mesage'] = "";      
        $this->render("register.html.twig", [
            "elapsed_time" => $elapsedTime, 
            "avatars" => $avatars, 
            "token" => $tm-> generateCSRFToken()],$scripts);
        // $template = "register";
        // require "templates/layout.phtml";
    }

   public function checkRegister() : void {
    
    if(isset($_SESSION['error_message'])) {
        unset($_SESSION['error_message']);
    }

    if(isset($_SESSION['success_message'])) {
        unset($_SESSION['success_message']);
    }
    
    $_SESSION['error_message']= 0; 
    
    $func = new Utils();
    if ($func->checkPostKeys(['email', 'firstname', 'age', 'avatar', 'csrf_token'])) {
    
        if (!isset($_POST['newsletter']) || empty($_POST['newsletter'])) {
        
             $data = [
                          
                'email'     => $func->e(strtolower(trim($_POST['email']))),       // Removing unnecessary spaces and lowering the email
                'firstname' => $func->e(ucfirst(trim($_POST['firstname']))),      // Removing unnecessary spaces and lowercaseing the first letter of the firstname, the rest in lowercase. 
                'age'       => $func->e(trim($_POST['age'])),               // Removing unnecessary spaces the matricule
                'avatar'    => $_POST['avatar'],
                'csrf_token'=> $func->e(trim($_POST['csrf_token'])),
                
             ];
        }   
        else {
        
             $data = [
                      
                'email'     =>  $func->e(strtolower(trim($_POST['email']))),       // Removing unnecessary spaces and lowering the email
                'firstname' =>  $func->e(ucfirst(trim($_POST['firstname']))),      // Removing unnecessary spaces and lowercaseing the first letter of the firstname, the rest in lowercase. 
                'age'       =>  $func->e(trim($_POST['age'])),  // Removing unnecessary spaces the matricule
                'avatar'    => $_POST['avatar'],
                'newsletter'=> $_POST['newsletter'],
                'csrf_token'=>  $func->e(trim($_POST['csrf_token'])),
            ];
        }
        
        // vérifier que tous les champs du formulaire sont là
        if(isset($data['email']) && isset($data['firstname']) && isset($data['age']) && isset($data['avatar']) && isset($data['csrf_token'])) {
              
            $tm = new CSRFTokenManager();
            $tokenVerify= $tm->validateCSRFToken($_SESSION['csrf_token']);
         
            if($tokenVerify == $data['csrf_token'] ) {
                
                $um = new UserManager();
                $result = $um->findByEmail($data['email']);
                
                // CORRECTION ICI : Changement de === false à === null
                if($result === null) {
                    // L'utilisateur n'existe pas, on peut le créer
                    
                    // Generate a random password for the user
                    $func= new Utils();
                    $passwordGenerated = $func->generateRandomPassword(12);
                    $passwordHash = password_hash($passwordGenerated, PASSWORD_BCRYPT);
                    $passwordView = $passwordGenerated;
                    
                    $createdAt = (new TimesModels())->dateNow('Y-m-d H:i:s');
                    
                    $newUser = new Users();
        
                    $newUser->setEmail($data['email']);
                    $newUser->setPassword($passwordHash);
                    $newUser->setFirstname($data['firstname']);
                    $newUser->setAge($data['age']);
                    $newUser->setAvatar($data['avatar']); 
                    $newUser->setRole(1);
                    $newUser->setCreatedAt($createdAt);
                    
                    if (isset($data['newsletter'])) {
                        $newUser->setNewsletter(1);
                    } else {
                        $newUser->setNewsletter(0);
                    }
                    
                   $user = $um->createUser($newUser);
                   
                    // Send confirmation email to the user
                    $sendEmail = new SendEmail();
                    $sendEmail->sendEmailConfirme($data['firstname'], $data['email'], $passwordView);
                    $timesModels = new TimesModels();
                    $elapsedTime = $timesModels->getElapsedTime();

                    $_SESSION['success_message'] = "Un email de validation vient de vous être envoyé";
                    
                    $scripts= $this->addScripts(['assets/js/formController.js', 'assets/js/mess.js']);

                    $this->render("homepage.html.twig", ['elapsed_time' => $elapsedTime, 'success_message'=> $_SESSION['success_message']], $scripts);
                    exit();
                }
                else {
                    // L'utilisateur existe déjà
                    $_SESSION['error_message'] = "Un compte existe déjà avec cette adresse.";
                    $this->redirectTo('login');
                    exit();
                }
            }
            else {
                $_SESSION['error_message'] = "Le jeton CSRF est invalide.";
                $timesModels = new TimesModels();
                $elapsedTime = $timesModels->getElapsedTime();
                $scripts= $this->addScripts(['public/assets/js/formController.js',]);
                $this->render("homepage.html.twig", ['elapsed_time' => $elapsedTime, 'error_message'=> $_SESSION['error_message']], $scripts);
                exit();
            }
        }
        else {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
            $this->redirectTo('register');
            exit();
        }
    }
    else {
       $_SESSION['error_message'] = "Les champs n'existent pas";
       $this->redirectTo('homepage');
       exit(); 
    }    
}


    // Méhtode pour afficher le formulaire de modification du mot de passe
    public function displayModify() : void
    {
        
        $am = new AvatarManager();
        //TODO : récupération des données concernant les avatars (appel à AvatarManager)
        if(isset($_SESSION['connected'])) {
            $avatar = $am->getById($_SESSION['user']['avatar']);
        }
        else {
            $avatar = $am->getById(4);
        }
        
        
        $scripts = $this->addScripts(['assets/js/formController.js']);
        $timesModels = new TimesModels();
                                $elapsedTime = $timesModels->getElapsedTime();
        //($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $token = $tm->generateCSRFToken();
        $_SESSION['csrf_token'] = $token;
        
        $_SESSION['error_message'] = "Vous devez Modifier votre mot de passe";      
        $this->render("modifypassword.html.twig", ["elapsed_time" => $elapsedTime, "token" => $token, "error_message"=> $_SESSION['error_message']], $scripts);
        // $template = "register";
        // require "templates/layout.phtml";
    }


    // Méhtode pour soumettre et traiter le formulaire de modification du mot de passe
    public function modifyPassword() { 
    unset($_SESSION['error_message'], $_SESSION['success_message']);
    
    $func = new Utils();
    $errorMessages = new ErrorMessages();
    $tm = new CSRFTokenManager();
    $errors = [];

    if ($func->checkPostKeys(['email', 'old_password', 'new_password', 'confirm_new_password', 'csrf_token'])) {
        $data = [
            'email'                 => strtolower(trim($_POST['email'])),
            'old_password'          => trim($_POST['old_password']),
            'new_password'          => trim($_POST['new_password']),               
            'confirm_new_password'  => trim($_POST['confirm_new_password']),
            'csrf_token'            => trim($_POST['csrf_token']),
        ];

        if (empty($data['email']) || !$func->validateEmail($data['email'])) {
            $errors[] = $errorMessages->getMessage(2);
        }
        if (!$func->validatePassword($data['old_password'])) {
            $errors[] = $errorMessages->getMessage(1);
        }
        if (!$func->validatePassword($data['new_password'])) {
            $errors[] = $errorMessages->getMessage(1);
        }
        if ($data['new_password'] == $data['old_password']) {
            $errors[] = $errorMessages->getMessage(24);
        }
        if ($data['new_password'] !== $data['confirm_new_password']) {
            $errors[] = $errorMessages->getMessage(23);
        }
        if (!$tm->validateCSRFToken($_SESSION['csrf_token'])) {
            $errors[] = $errorMessages->getMessage(43);
        }

        if (count($errors) === 0) {
            $um = new UserManager();
            $search = $um->findByEmail($data['email']);

            if ($search === null) {
                $_SESSION['error_message'] = $errorMessages->getMessage(44);
                $this->redirectTo('displayModify');
            } else {
                $user = new Users();  
                $user->setId($_SESSION['user']['id']);
                $user->setPassword(password_hash($data['new_password'], PASSWORD_DEFAULT));                                     
                $um->changePasswordAndStatut($user);

                $_SESSION['user']["statut"] = 1;
                $_SESSION['connected'] = true;
                $_SESSION['start_time'] = time();       
                $_SESSION["success_message"] = "Tu es connecté, tu peux maintenant accéder aux jeux et activités";
                $_SESSION['isUser'] =true;

                $this->redirectTo("homepageUser");
                exit();
            }
        } else {
            $_SESSION['error_message'] = implode('<br>', $errors); // concatène toutes les erreurs
            $this->redirectTo('displayModify');
        }
    } else {
        $_SESSION['error_message'] = $errorMessages->getMessage(0);
        $this->redirectTo('homepage');
    }
}


public function displayForgottenPassword()
{
    $func = new Utils();

    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $func->checkPostKeys(['email', 'csrf_token'])) {
        $data = [
            'email'      => trim($_POST['email']),
            'csrf_token' => $_POST['csrf_token']
        ];

        // Vérification CSRF
        $tm = new CSRFTokenManager();
        $tokenVerify = $tm->validateCSRFToken($_SESSION['csrf_token']);

        if ($tokenVerify == $data['csrf_token']) {
            $um = new UserManager();
            $result = $um->findByEmail($data['email']);

            if ($result === null) {
                // Email inexistant
                $_SESSION['error_message'] = "Adresse email inconnue.";
            } else {
                // Génération du nouveau mot de passe
                $passwordGenerated = $func->generateRandomPassword(12);
                $passwordHash      = password_hash($passwordGenerated, PASSWORD_BCRYPT);
                $passwordView      = $passwordGenerated; // mot de passe en clair pour l'email

                // Mise à jour en BDD
                $resetOk = $um->resetOneUserPasswordAndStatus($result['id'], $passwordHash);

                if ($resetOk) {
                    // Envoi du mail
                    $sendEmail = new SendEmail();
                    $sendEmail->sendPasswordResetEmail($result['firstname'], $data['email'], $passwordView);

                    $_SESSION['success_message'] = "Un email de réinitialisation vient de vous être envoyé.";
                    $this->redirectTo("homepage");
                } else {
                    $_SESSION['error_message'] = "Erreur lors de la réinitialisation du mot de passe.";
                }
            }
        } else {
            $_SESSION['error_message'] = "Token CSRF invalide.";
        }
    }

    // Affichage du formulaire (toujours exécuté)
    $scripts = $this->addScripts(['assets/js/formController.js', 'assets/js/mess.js']);
    $this->render("forgottenPassword.html.twig", [
        'error_message'   => $_SESSION['error_message'] ?? null,
        'success_message' => $_SESSION['success_message'] ?? null,
        'csrf_token'      => $_SESSION['csrf_token'] ?? null
    ], $scripts);
}


// Dans UserController.php

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
        $passwordGenerated = $func->generateRandomPassword(12);
        $passwordHash = password_hash($passwordGenerated, PASSWORD_BCRYPT);

        // Mettre à jour en BDD (mot de passe + statut à 0)
        $resetOk = $um->resetOneUserPasswordAndStatus($userId, $passwordHash);

        if (!$resetOk) {
            throw new Exception('Échec de la mise à jour en base de données');
        }

        // Envoyer l'email
        $sendEmail = new SendEmail();
        $sendEmail->sendPasswordResetEmail(
            $user->getFirstname(),
            $user->getEmail(),
            $passwordGenerated
        );

        // Réponse de succès
        header('Content-Type: application/json'); // ✅ AJOUTÉ
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès. Un email a été envoyé.',
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




    public function logout() : void
    {
        session_destroy();
        $this->render("homepage.html.twig",[]);
    }
}
