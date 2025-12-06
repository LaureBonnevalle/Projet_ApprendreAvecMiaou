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
    public function contactUs() {
        
                $errors = [];
                
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $tm = new CSRFTokenManager();
                    $token = $tm->generateCSRFToken();
                    $_SESSION['csrf_token']= $token;
                    $scripts = $this->addScripts(['assets/js/formController.js']);
                    
                    $this->render('contact.html.twig', [
                        'user'  =>$_SESSION['user'] ?? null,
                        'page'      => "Contactez-nous",
                        'csrf_token'     => $token,
                        'errors'    => [],
                        'firstname'  => null,
                        'email'     => null,
                        'subject'   => null,
                        'content'     => null,
                    ], $scripts);
                    return;
                }

                $errors = []; //$valids = [];

                // Retrieve error and valid messages
                $errorMessages = (new ErrorMessages())->getMessages();
                //$validMessages = (new ValidMessages())->getMessages();
                $func = new Utils();
                //var_dump($_POST); die;
        

                if ($func->checkPostKeys(['firstname', 'email', 'subject', 'content', 'csrf_token'])) {
                        
                
                $data = [
                        'firstname'  => trim($_POST['firstname']),   // Removing unnecessary spaces and lowercaseing the first letter of the lastname, the rest in lowercase.           
                        'email'     => strtolower(trim($_POST['email'])), 
                        'subject'     => $func->e($_POST['subject']),// Removing unnecessary spaces and lowering the email
                        'content'     => $func->e($_POST['content']),
                        'csrf_token'  => $_POST['csrf_token']// Removing unnecessary spaces at the story
                    ];
                    
                    //var_dump($data);
            

                    // Verify the CSRF token
                    if($tm = (new CSRFTokenManager())->validateCSRFToken($data['csrf_token']) == false)
                    {
                        $errors[] = $errorMessages[0];                      // An error occurred while sending the form !
                        unset($_SESSION['csrf_token']);                    // Clear the token verification session data
                    }

                    // Validate 'firsname' field
                    //if (!$this->verifInputText($data['lastname'], [2, 60], 'string')) 
                    if (strlen($data['firstname']) < 2 || strlen($data['firstname']) > 60) {
                        $errors[] = $errorMessages[5];                      // Please enter your lastname !
                    }
                    // Validate 'email' field
                    if (!$func->validateEmail($data['email'])) {
                        $errors[] = $errorMessages[2];                      // Please provide a valid email !
                    }
                    // If there are no errors, proceed to insert the contact into the database
                    if (empty($errors)) {                
                            $scripts = $this->addScripts(['assets/js/formController.js']);
                        //try {
                            // Insert the new contact into the database                     
                            $newContact = new Contacts();
                            $newContact->setFirstname($data['firstname']);
                            $newContact->setEmail($data['email']);
                            $newContact->setSubject($data['subject']);
                            $newContact->setContent($data['content']);
                            $newContact->setReceptedDate((new TimesModels())->dateNow('Y-m-d H:i:s', 'Europe/Paris'));
                            $newContact->setStatut(0);
                            
                            //($newContact);

                            $addContact = new ContactManager();
                            $addContact->insert($newContact);

                            $_SESSION['success-message']="Votre message a bien été envoyé. Merci";

                            $this->redirectTo('homepage');
                            return; // Exit the method after rendering success message

                //} catch (\Exception $e) {
                    // Handle any errors that occur during user insertion
                  //  $errors[] = $errorMessages[0];              // An error occurred while sending the form !
                //}
            //}
                     }
                }

             $tm = new CSRFTokenManager();

            $this->render('contact.html.twig', [
                'user'          =>$_SESSION['user'] ?? null,
                'page'          => "Contactez-nous",
                'token'         => $tm->generateCSRFToken(),
                'firstname'     => $data['firstname']    ?? null,
                'email'         => $data['email']       ?? null,
                'subject'       => $data['subject']     ?? null,
                'content'       => $data['content']       ?? null
            ]);
    
    }

/*// Nouvelle méthode pour afficher le formulaire
    public function renderContactForm(): void {
        echo $this->twig->render('contactFormUser.html.twig', [
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }*/

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