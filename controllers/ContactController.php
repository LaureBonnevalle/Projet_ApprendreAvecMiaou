<?php



class ContactController extends AbstractController {

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Process contact registration.
     * If the request method is not POST, displays the contact form.
     * If POSTed data is valid, inserts the contact into the database and displays success message.
     * If POSTed data is invalid, redisplays the form with error messages and retains entered data.
     */
    /**
     * Affiche et traite le formulaire de contact PUBLIC
     */
    public function contactUs(): void
    {
        $func = new Utils();
        $am = new AvatarManager();
        $tm = new CSRFTokenManager();

        // Gérer l'avatar pour l'affichage
        if (isset($_SESSION['user'])) {
            $avatar = $am->getById($_SESSION['user']['avatar']);
            $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
        } else {
            $avatar = $am->getById(4);
            $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
        }

        $errors = [];

        // ✅ SI PAS POST → Afficher le formulaire
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $token = $tm->generateCSRFToken();
            $_SESSION['csrf_token'] = $token;
            
            $scripts = $this->addScripts(['assets/js/formController.js']);
            
            $this->render('contact.html.twig', [
                'avatar' => $avatar,
                'user' => $_SESSION['user'] ?? null,
                'page' => "Contactez-nous",
                'csrf_token' => $token,
                'errors' => [],
                'firstname' => null,
                'email' => null,
                'subject' => null,
                'content' => null,
            ], $scripts);
            return;
        }

        // ✅ SI POST → Traiter le formulaire
        $errorMessages = (new ErrorMessages())->getMessages();

        if (!$func->checkPostKeys(['firstname', 'email', 'subject', 'content', 'csrf_token'])) {
            $errors[] = 'Tous les champs sont requis';
        } else {
            $data = [
                'firstname' => trim($_POST['firstname']),
                'email' => strtolower(trim($_POST['email'])),
                'subject' => $func->e(trim($_POST['subject'])),
                'content' => $func->e(trim($_POST['content'])),
                'csrf_token' => $_POST['csrf_token']
            ];

            // Vérifier CSRF
            if (!$tm->validateCSRFToken($data['csrf_token'])) {
                $errors[] = $errorMessages[0] ?? 'Token CSRF invalide';
                unset($_SESSION['csrf_token']);
            }

            // Valider firstname
            if (strlen($data['firstname']) < 2 || strlen($data['firstname']) > 60) {
                $errors[] = $errorMessages[5] ?? 'Le prénom doit contenir entre 2 et 60 caractères';
            }

            // Valider email
            if (!$func->validateEmail($data['email'])) {
                $errors[] = $errorMessages[2] ?? 'Veuillez fournir un email valide';
            }

            // Valider subject
            if (strlen($data['subject']) < 3 || strlen($data['subject']) > 100) {
                $errors[] = 'Le sujet doit contenir entre 3 et 100 caractères';
            }

            // Valider content
            if (strlen($data['content']) < 10) {
                $errors[] = 'Le message doit contenir au moins 10 caractères';
            }

            // ✅ Si pas d'erreurs, insérer le message
            if (empty($errors)) {
                try {
                    $newContact = new Contacts();
                    $newContact->setFirstname($data['firstname']);
                    $newContact->setEmail($data['email']);
                    $newContact->setSubject($data['subject']);
                    $newContact->setContent($data['content']);
                    $newContact->setReceptedDate((new TimesModels())->dateNow('Y-m-d H:i:s', 'Europe/Paris'));
                    $newContact->setStatut(0);

                    $contactManager = new ContactManager();
                    $contactManager->insert($newContact);

                    // ✅ Redirection vers homepage PUBLIC
                    $_SESSION['success_message'] = "Votre message a bien été envoyé. Merci !";
                    $this->redirectTo('homepage');
                    return;

                } catch (\Exception $e) {
                    error_log("Erreur contact: " . $e->getMessage());
                    $errors[] = $errorMessages[0] ?? 'Une erreur est survenue lors de l\'envoi du message';
                }
            }
        }

        // ✅ Réafficher le formulaire avec erreurs
        $scripts = $this->addScripts(['assets/js/formController.js']);
        $token = $tm->generateCSRFToken();

        $this->render('contact.html.twig', [
            'avatar' => $avatar,
            'user' => $_SESSION['user'] ?? null,
            'page' => "Contactez-nous",
            'csrf_token' => $token,
            'errors' => $errors,
            'firstname' => $data['firstname'] ?? null,
            'email' => $data['email'] ?? null,
            'subject' => $data['subject'] ?? null,
            'content' => $data['content'] ?? null,
        ], $scripts);
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Traite le formulaire de contact depuis le PROFIL (AJAX)
     */
    public function contactFromProfile(): void
    {
        // Nettoyer le buffer
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        // Vérifier POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Vous devez être connecté']);
            exit;
        }

        try {
            $func = new Utils();
            $tm = new CSRFTokenManager();

            // Vérifier les champs
            if (!$func->checkPostKeys(['firstname', 'email', 'subject', 'content', 'csrf_token'])) {
                throw new Exception('Tous les champs sont requis');
            }

            $data = [
                'firstname' => trim($_POST['firstname']),
                'email' => strtolower(trim($_POST['email'])),
                'subject' => $func->e(trim($_POST['subject'])),
                'content' => $func->e(trim($_POST['content'])),
                'csrf_token' => $_POST['csrf_token']
            ];

            // Vérifier CSRF
            if (!$tm->validateCSRFToken($data['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Validations
            if (strlen($data['firstname']) < 2 || strlen($data['firstname']) > 60) {
                throw new Exception('Le prénom doit contenir entre 2 et 60 caractères');
            }

            if (!$func->validateEmail($data['email'])) {
                throw new Exception('Email invalide');
            }

            if (strlen($data['subject']) < 3 || strlen($data['subject']) > 100) {
                throw new Exception('Le sujet doit contenir entre 3 et 100 caractères');
            }

            if (strlen($data['content']) < 10) {
                throw new Exception('Le message doit contenir au moins 10 caractères');
            }

            // Insérer le message
            $newContact = new Contacts();
            $newContact->setFirstname($data['firstname']);
            $newContact->setEmail($data['email']);
            $newContact->setSubject($data['subject']);
            $newContact->setContent($data['content']);
            $newContact->setReceptedDate((new TimesModels())->dateNow('Y-m-d H:i:s', 'Europe/Paris'));
            $newContact->setStatut(0);

            $contactManager = new ContactManager();
            $contactManager->insert($newContact);

            // Réponse de succès
            echo json_encode([
                'success' => true,
                'message' => 'Votre message a bien été envoyé. Merci !'
            ]);

        } catch (Exception $e) {
            error_log("Erreur contactFromProfile: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit;
    }


/*****************************GESTION DES MESSAGES DES UTILISATEURS PAR ADMIN***********/    
    

    //afficher les messages et afficher sur la vue messagerie
public function displayMessages() {

    $messages = (new ContactManager())->getAll();
    $tm = new CSRFTokenManager();
    $token = $tm->generateCSRFToken();
    $_SESSION['csfr_token'] = $token;
    $scripts = $this->addScripts([
            'https://kit.fontawesome.com/3c515cc4da.js',
            'assets/js/formController.js',
            'assets/js/adminjs/ajaxOneUser.js',
            'assets/js/adminjs/ajaxSearchUsers.js',
            'assets/js/adminjs/coloringAdmin.js',
            'assets/js/adminjs/storyAdmin.js',
            'assets/js/adminjs/modifyAvatarAdmin.js',
            'assets/js/mess.js'
    ]);
    
    // Vérifier si les messages existent dans la session avant de les utiliser
    $errorMessage = $_SESSION['error_message'] ?? null;
    $successMessage = $_SESSION['success_message'] ?? null;
    
    $this->render('messages.html.twig', [
        'messages' => $messages, 
        'error_message' => $errorMessage, 
        'success_message' => $successMessage, 
        'csrf_token' => $token,
        'user' => $_SESSION['user'] ?? null,
        'avatar' => $avatar,
        'session' => $_SESSION,
        'connected' => true,
        'isUser' => false,
        'isAdmin' => true,
        'elapsed_time' => $elapsedTime,
        'start_time' => $_SESSION['start_time'] ?? time(),
        'nbrMessages' => $nbrMessages,
        'titre' => 'Liste messages'
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
        //$com = new ContactManager();
        //$message = $com->getOne($_GET['id']);
        
        $updateStatut = (new ContactManager())->updateStatut($contact);
        $message = (new ContactManager())->getOne($_GET['id']);

        //var_dump($message);
        
        
        $this->render('readMessage.html.twig', [
            'message'   => $message,   
            'csrf_token'     => $token,
            "session_tokenVerify"=> $token,
            'user' => $_SESSION['user'] ?? null,
            'avatar' => [$avatar],
            'session' => $_SESSION,
            'connected' => true,
            'isUser' => false,
            'isAdmin' => true,
            'elapsed_time' => $elapsedTime,
            'start_time' => $_SESSION['start_time'] ?? time(),
            'nbrMessages' => $nbrMessages,
            'titre' => 'lecture message'
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
                   $this->render("readMessage.html.twig", [
                    'error_message'=> $_SESSION['error_message'], 
                    'csrf_token' => $token,
                    'user' => $_SESSION['user'] ?? null,
                    'avatar' => [$avatar],
                    'session' => $_SESSION,
                    'connected' => true,
                    'isUser' => false,
                    'isAdmin' => true,
                    'elapsed_time' => $elapsedTime,
                    'start_time' => $_SESSION['start_time'] ?? time(),
                    'nbrMessages' => $nbrMessages,
                    'titre' => 'Repondre au message'
                ], [$scripts]); 
                }
                
        }
    }

     // delete le message auquel on a repondu
    public function deleteMessage() {
        
        
        $func = new Utils(); 

        
        
        if (!$func->checkPostKeys(['id','csrf_token'])) {   
            $_SESSION['error_message'] = "Les champs n'existent pas.";
            $this->render("readMessage.html.twig", [
                'error_message'=> $_SESSION['error_message'],
                'csrf_token' => $token,
                'user' => $_SESSION['user'] ?? null,
                'avatar' => [$avatar],
                'session' => $_SESSION,
                'connected' => true,
                'isUser' => false,
                'isAdmin' => true,
                'elapsed_time' => $elapsedTime,
                'start_time' => $_SESSION['start_time'] ?? time(),
                'nbrMessages' => $nbrMessages,
                'titre' => 'Lire message'
                ],
                  $scripts);
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
                    (new ContactManager())->deleteOne($data['id']);
            
                    $messages = (new ContactManager())->getAll();
                    $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js']);
                    $this->render('messages.html.twig', [
                        'page'      => "Messagerie",
                        'messages'  => $messages,
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
                } else { 
                    $tm = new CSRFTokenManager();
                    $token = $tm->generateCSRFToken();
                    $_SESSION['error_message']="token invalid";
                    $this->render("readMessage.html.twig", ['error_message'=> $_SESSION['error_message'], 'csrf_token' => $token], $scripts);
                }
        }
    }

}