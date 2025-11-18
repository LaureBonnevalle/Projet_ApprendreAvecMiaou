<?php

/*require_once("services/Utils.php");
require_once("models/TimesModels.php");*/

class ContactController extends AbstractController {

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
            
            var_dump($data);
            

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
                    
                    var_dump($newContact);

                    $addContact = new ContactsManager();
                    $addContact->insert($newContact);

                    $_SESSION['success-message']="Votre message a bien été envoyé. Merci";

                    $this->render('homepage.html.twig', [
                        'page'          => "Contactez-nous",
                        'error_message' => $_SESSION['success_message'],
                        'errors'        => [],
                        'email'         => null,
                        'subject'       => null,
                        'story'         => null,
                    ], $scripts);
                    return; // Exit the method after rendering success message

                //} catch (\Exception $e) {
                    // Handle any errors that occur during user insertion
                  //  $errors[] = $errorMessages[0];              // An error occurred while sending the form !
                //}
            //}
            
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
    }
}
}