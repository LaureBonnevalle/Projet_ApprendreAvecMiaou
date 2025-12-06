<?php


class DashboardController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
    }

 /*********************************PAGE ADMIN*****************************/   
    public function displayDashboard(): void {
        
    
    try {
        error_log("=== DÉBUT displayDashboard ===");
        error_log("1. Vérification admin");
        $func = new Utils();
        if (!$func->isAdmin()) {
            throw new Exception("Accès refusé - Administrateur requis");
        }

        error_log("2. Récupération avatar");
        $avatarManager = new AvatarManager();
        $avatar = $avatarManager->getById($_SESSION['user']['avatar']);
        error_log("Avatar récupéré: " . print_r($avatar, true));

        error_log("3. Récupération messages");
        $contactsManager = new ContactManager();
        $nbrMessages = $contactsManager->getAllNotRead();
        error_log("Messages: $nbrMessages");

        error_log("4. Récupération temps");
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        


        error_log("5. Scripts");
        $scripts = $this->addScripts([
            'https://kit.fontawesome.com/3c515cc4da.js', 
            'assets/js/formController.js',
            'assets/js/adminjs/ajaxOneUser.js',
            'assets/js/adminjs/ajaxSearchUsers.js',
            'assets/js/adminjs/coloringAdmin.js',
            'assets/js/adminjs/storyAdmin.js',
            'assets/js/adminjs/modifyAvatarAdmin.js',
            
            
        ]);

        error_log("6. Nettoyage session");
        $func->clearSessionMessages();

        error_log("7. Préparation données render");
        $data = [
            'user' => $_SESSION['user'] ?? null,
            //'avatar' => [$avatar],
            'session' => $_SESSION,
            'connected' => true,
            'isUser' => false,
            'isAdmin' => true,
            'elapsed_time' => $elapsedTime,
            'start_time' => $_SESSION['start_time'] ?? time(),
            'nbrMessages' => $nbrMessages,
            'titre' => 'Dashboard Admin'
        ];
        error_log("Données: " . print_r($data, true));

        error_log("8. Appel render");
        $this->render('dashboard.html.twig', $data, $scripts);
        
        error_log("=== FIN displayDashboard (succès) ===");
        
    } catch (Exception $e) {
        error_log("❌ ERREUR displayDashboard: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error'] = "Impossible de charger le tableau de bord";
        $this->redirectTo("homepage");
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
    

