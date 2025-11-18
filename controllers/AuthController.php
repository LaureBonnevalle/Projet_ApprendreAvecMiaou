<?php


/*require_once("models/ErrorMessages.php");
require_once("models/TimesModels.php");
require_once("models/SendEmail.php");
require_once("core/Utils.php");
require_once("core/CSRFTokenManager.php");
require_once("managers/AvatarManager.php");
require_once("managers/AbstractManager.php");*/


class AuthController extends AbstractController {
    public function __construct()
    {
        parent::__construct();
    }

    public function login() : void
    {  
         $scripts = $this->addScripts(['public/assets/js/formController.js', 'public/assets/js/common.js', 'public/assets/js/global.js', 'public/assets/js/home.js']);
        
        //var_dump($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $am = new AvatarManager();
        $token= $tm->generateCSRFToken();
        
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        
        $_SESSION['error_message'] = "";      
        $this->render("login.html.twig", [ "token" => $token, "avatar" => $am->getById(4), 'elapsed_time' =>$elapsedTime] , $scripts);
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
$_SESSION["login_data"] = [
    'connected' => 1,
    'start_time' => time(),
    'success_message' => "Connexion réussie ! Bienvenue."
];
        
        // Nettoyer les messages d'erreur
        unset($_SESSION["error_message"]);
        unset($_SESSION["error-message"]);
        
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        $avatar = $am->getById($result['avatar']);
        
        if ($result['role'] == 1 || $result['role'] == 2) {
            
            $this->render("homepageUser.html.twig", [
                'user' =>$_SESSION['user'] ?? null,
                'elapsed_time' => $elapsedTime, 
                'session' => $_SESSION,
                'success_message' => $_SESSION["login_data"]['success_message'] ?? null, 
                "avatar" => $avatar
            ], [$this->getDefaultScripts()]);
        } /*else {
            $adminAvatar = $am->getById(7);
            $this->render("dashboard.html.twig", [
                'elapsed_time' => $elapsedTime, 
                'session' => $_SESSION,
                'success_message' => $_SESSION['success_message'], 
                "avatar" => $adminAvatar
            ], [$this->getDefaultScripts()]);
        }*/
        return;
    }
    
    // Compte banni
    $_SESSION["error_message"] = 'Votre compte a été banni';
$this->redirectTo('homepage');
}

/*// 3. VÉRIFIEZ VOTRE MÉTHODE displayModify() :
public function displayModify() : void
{
    $am = new AvatarManager(); // Corrigez "avatarManager" en "AvatarManager"
    
    // Récupération de l'avatar
    if(isset($_SESSION['user']['avatar'])) {
        $avatar = $am->getById($_SESSION['user']['avatar']);
    } else {
        $avatar = $am->getById(4);
    }
    
    $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js','public/assets/js/formFunction.js']);
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();
    
    // Générer le token CSRF
    $tm = new CSRFTokenManager();
    $token = $tm->generateCSRFToken();
    $_SESSION['csrf_token'] = $token;
    
    // Le message d'erreur devrait déjà être défini par checkLogin()
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = "Vous devez modifier votre mot de passe";
    }
    
    $this->render("modifypassword.html.twig", [
        "elapsed_time" => $elapsedTime, 
        "token" => $token, 
        "error_message" => $_SESSION['error_message'],
        "avatar" => $avatar
    ], $scripts);
}*/

    

    public function register() : void
    {
        //TODO : récupération des données concernant les avatars (appel à AvatarManager)
        
        $am = new AvatarManager();
        $avatars = $am->findAllAvatars();
        $scripts = $this->addScripts(['public/assets/js/formController.js','public/assets/js/formFunction.js', 'public/assets/js/formController.js']);
         $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        //var_dump($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $scripts= $this->addScripts([
            'public/assets/js/formController.js',
            ]);
        
        $_SESSION['error_mesage'] = "";      
        $this->render("register.html.twig", ["elapsed_time" => $elapsedTime, "avatars" => $avatars, "token" => $tm-> generateCSRFToken()],$scripts);
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
                    
                    $scripts= $this->addScripts(['public/assets/js/formController.js',]);

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
        
        
        $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js','public/assets/js/formFunction.js']);
        $timesModels = new TimesModels();
                                $elapsedTime = $timesModels->getElapsedTime();
        //var_dump($avatars);
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
        
        if(isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }

        if(isset($_SESSION['success_message'])) {
            unset($_SESSION['success_message']);
        }
        
        $errors = [];
        
        //var_dump($_POST);
        
        $func = new Utils();
        $errorMessages = new ErrorMessages();
        $errorMessages->getMessages();
        $tm = new CSRFTokenManager();
        if ($func->checkPostKeys(['email', 'old_password', 'new_password', 'confirm_new_password', 'csrf_token'])) {
                        
            $data = [
                'email'                 => strtolower(trim($_POST['email'])),
                'old_password'          => trim($_POST['old_password']),
                'new_password'          => trim($_POST['new_password']),               
                'confirm_new_password'  => trim($_POST['confirm_new_password']),
                'csrf_token'            => trim($_POST['csrf_token']),
            ];
            
                if(empty($data['email']) || !$func->validateEmail($data['email'])) {
                // Message d'erreur que l'email ne peut pas être vide
                $errors[] = $errorMessages[2];
                }
                
                // old_password est il vide ? A t il bien 8 caractères, ...
                if(!$func->validatePassword($data['old_password'])) {
                    $errors[] = $errorMessages[1]; // Le mot de passe doit faire au moins 8 caractères ...... !
                }
                // new_password est il vide ? A t il bien 8 caractères, ...
                if(!$func->validatePassword($data['new_password'])) {
                    $errors[] = $errorMessages[1]; // Le mot de passe doit faire au moins 8 caractères ...... !
                }
                if($data['new_password'] == $data['old_password']) {
                    $errors[] = $errorMessages[24]; // Le mot de passe old doit etre different du new ...... !  
                }
                if($data['new_password'] !== $data['confirm_new_password']) {
                    $errors[] = $errorMessages[23]; // Le mot de passe new doit etre egal au confirm new ...... !  
                }
                
                if ( !($tokenVerify= $tm->validateCSRFToken($_SESSION['csrf_token']))) {
                    $errors[] = $errorMessages[43]; // token invalide ...... !  
                }
                //var_dump($_POST, $errors); die;
                
                if(count($errors) == 0) {
                    // Je continu le traitement 
                    $um = new UserManager();
                    //recupere un user par son mail
                    $search = $um->findByEmail($_SESSION['user']['email']);
            
                    
                    
                    // Vérifier si l'ancien MDP renseigné dans le forumlaire est bien = à l'ancien de la BDD ($search['password']
                    if ($search == null) {
                        $errors[] = $errorMessages[44]; // Le mot de passe doit faire au moins 8 caractères ...... !
                    }
                    else {
                        $user = new Users();  
                        $user->setId($_SESSION['user']['id']);
                        $user->setPassword(password_hash($data['new_password'], PASSWORD_DEFAULT));                                     
                  
                               
                        $um = new UserManager();         
                        $um->changePasswordAndStatut($user); // Vérifier la suite
                        
                        
                        // Modification de la session
                        $_SESSION['user']["statut"] = 1;
                        $_SESSION['connected'] = true;
                        
                        unset($_SESSION["error-message"]);
                        unset($_SESSION["success-message"]);
                        $_SESSION['start_time'] = time();       
                        
                        $_SESSION["success_message"] = "Tu es connectés tu peux maintenant accéder aux jeux et activités";
                        //$dc = new DefaultController();
                        //$dc->HomepageUser();
                        //recupere l'avatar de l'utilisateur par l'id de l'avatar de $result
                        //$am = new AvatarManager();
                        //$avatar = $am->getById($search['avatar']);
                        header("Location: ?route=homepageUser");
                        exit();
                        
                        //$this->render("homepageUser.html.twig", ['error_message'=> $_SESSION['error_message']],[$this->getDefaultScript()]); // on redirige vers la page d'acceuil
                                                   
                    
                    }    
                } else {
                    $errors[] = $errorMessages[38]; // Il y a des erreur sur le formulaire.....
                    //$this->render("ModifyPassword.html.twig", ['error_message'=> $_SESSION['error_message']],$data); // on redirige
                    $this->redirectTo('displayModify');
                }  
        } else {
           $errors[] = $errorMessages->getMessages()[0] ?? 'Erreur inconnue';
            
            //$this->render("homepage.html.twig", ['error_message'=> $_SESSION['error_message']],[$this->getDefaultScript()]); // on redirige
            $this->redirectTo('homepage');
        }
            
           
            
    }
    
    
public function displayProfile() {
     $am = new AvatarManager();
        $avatars = $am->findAllAvatars();
        $scripts = $this->addScripts(['public/assets/js/formController.js','public/assets/js/formFunction.js', 'public/assets/js/formController.js']);
         $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        //var_dump($avatars);
        // Générer le token pour le mettre dans le vue, dans l'input de type hidden
        $tm = new CSRFTokenManager();
        $scripts= $this->addScripts([
            'public/assets/js/formController.js',
            ]);
        
        $_SESSION['error_mesage'] = "";      
        $this->render("profile.html.twig", [
            "user" => $_SESSION['user'] ?? null,
            "elapsed_time" => $elapsedTime, 
            "avatars" => $avatars, 
            "token" => $tm-> generateCSRFToken(),
            'session' => $_SESSION,
            'connected' => $_SESSION['user'],
            $scripts]);
            
        // $template = "register";
        // require "templates/layout.phtml";





}




        
       


        
       /* public function validateMail($email,$cle ) : void 
        {
            $um = new UserManager();
            
            $data = $this->um->findByEmail($email);
         
            $clebdd = $data['cle'];    // Récupération de la clé
            $actif = $data['actif']; // $actif contiendra alors 0 ou 1
        
         
         
             // On teste la valeur de la variable $actif récupérée dans la BDD
            if($actif == '1') // Si le compte est déjà actif on prévient
              { //return $active="Votre compte est déjà actif !";
                 $this->render("login.html.twig", ['active'=>"Votre compte est déjà actif !"]);
              }
            else // Si ce n'est pas le cas on passe aux comparaisons
            {
                    if($cle == $clebdd) // On compare nos deux clés    
                    { 
                      
             
                      // La requête qui va passer notre champ actif de 0 à 1
                      $query = $this->db->prepare("UPDATE membres SET actif = 1 WHERE email like :email ");
                      $query->bindParam(':email', $email);
                      $query->execute();
                      
                      // Si elles correspondent on active le compte !    
                      //return $activate= "Votre compte a bien été activé !";
                      $this->render("login.html.twig", ['success_message'=>"Votre compte a bien été activé vous pouvez vous connectez !"]);
                    }
                    else // Si les deux clés sont différentes on provoque une erreur...
                    {
                      //return $errorCle = "Erreur ! Votre compte ne peut être activé...";
                      $this->render("homepage.html.twig", ['error_message'=> "Erreur ! Votre compte ne peut être activé..."]);
                    }
             }
        }*/
    
    public function logout() : void
    {
        session_destroy();
        $this->render("homepage.html.twig",[]);
    }
}
