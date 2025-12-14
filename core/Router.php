<?php

class Router {
    private $errorLog = [];
    
    public function __construct()
    {
        // Configuration des erreurs
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Ne pas afficher les erreurs à l'utilisateur
        ini_set('log_errors', 1);
    }

    public function handleRequest(array $get)
    {
        try {
            $func = new Utils();
            $route = isset($get["route"]) ? $get["route"] : "homepage";
            
            error_log("=== ROUTER === Route demandée: $route");

            // Vérifier que la session est active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                throw new Exception("Session non active");
            }

            // Routes accessibles à tous (non connectés)
            $publicRoutes = [
                "homepage", "contact", "login", "check-login", 
                "register", "check-register", "displayModify", "modifyPassword", "logout",
                "pedagogie", "forgottenPassword","newsletterSubscribe","newsletterUnsubscribe",
            ];

            // Routes pour utilisateurs connectés et validés (role 1 ou 2)
            $userRoutes = [
                "homepageUser", "profile", "games", "pixelArt", 
                "memo", "colorings", "coloringsListe", "stories", "getImage", 
                "getStory", "displayGame", "displayPixelArt" ,"forgottenPassword", "updateProfile", "toggleNewsletter", "contact", 
                "contactUsForm", 'resetPasswordFromProfile', "displayClick","saveClickScore", 'contactFromProfile', "saveMemoryScore",
            ];

            // Routes pour administrateurs uniquement (role 2)
            $adminRoutes = [
                "dashboard", "ajaxSearchUsers", "allUsers", "readOneUser", 
                "resetPassword", "updateStatus", "updateRole", "updateUserAvatar", 
                "updateNewsletter", "response", "messagerie", "deleteMessage", 
                "readMessage", "modifAvatar", "deleteAvatar", "addAvatar", 
                "addstoryStoriesAd", "StoriesAdmin", "modifColoring", "addColoring", 
                "deleteColoring", "addCategorie", "modifGame", "modifypedagogie",
                "forgottenPassword", "sendNewsletterForm", "processNewsletterSend", "resetPassword",
                
            ];

            // ====================================
            // GESTION DES ROUTES PUBLIQUES
            // ====================================
            if (in_array($route, $publicRoutes)) {
                $this->handlePublicRoute($route);
            }
            // ====================================
            // GESTION DES ROUTES UTILISATEURS
            // ====================================
            elseif (in_array($route, $userRoutes)) {
                $this->handleUserRoute($route, $func);
            }
            // ====================================
            // GESTION DES ROUTES ADMIN
            // ====================================
            elseif (in_array($route, $adminRoutes)) {
                $this->handleAdminRoute($route, $func);
            }
            // ====================================
            // ROUTE PAR DÉFAUT (404)
            // ====================================
            else {
                throw new Exception("Route inconnue: $route");
            }

        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Gère les routes publiques
     */
    private function handlePublicRoute(string $route): void
    {
        try {
            $dc = new DefaultController();
            $ac = new AuthController();
            $ctc = new ContactController();
            $nc = new NewsletterController();

            switch ($route) {
                case "homepage":
                    $dc->homepage();
                    break;
                    
                case "contact":
                    $ctc->contactUs();
                    break;
                    
                case "login":
                    $ac->login();
                    break;
                    
                case "check-login":
                    $ac->checkLogin();
                    break;
                    
                case "register":
                    $ac->register();
                    break;
                    
                case "check-register":
                    $ac->checkRegister();
                    break;
                    
                case "displayModify":
                    $ac->displayModify();
                    break;
                    
                case "modifyPassword":
                    $ac->modifyPassword();
                    break;
                    
                case "logout":
                    $dc->logout();
                    break;
                    
                case "pedagogie":
                    $dc->pedagogie();
                    break;

                case "forgottenPassword":
                    $ac->displayForgottenPassword();
                    break;

                case "newsletterSubscribe":
                    $nc->newsletterSubscribe();
                    break;

                case "newsletterUnsubscribe":
                    $nc->unsubscribe();
                    break;
                    
                default:
                    throw new Exception("Route publique non gérée: $route");
            }
        } catch (Exception $e) {
            error_log("❌ Erreur route publique ($route): " . $e->getMessage());
            throw new Exception("Erreur lors du chargement de la page", 0, $e);
        }
    }

    /**
     * Gère les routes utilisateurs
     */
    private function handleUserRoute(string $route, Utils $func): void
    {
        try {
            // Vérification des permissions
            if (!$func->isAuthentified()) {
                throw new Exception("Utilisateur non authentifié");
            }

            if (!$func->isValidateUser()) {
                throw new Exception("Compte utilisateur non validé");
            }

            if (!$func->isUser() && !$func->isAdmin()) {
                throw new Exception("Permissions insuffisantes");
            }

            // Si toutes les vérifications passent, router vers le bon controller
            $dc = new DefaultController();
            $ac = new AuthController();
            $gc = new GameController();
            $cc = new ColoringController();
            $sc = new StoryController();
            $uc = new UserController();
            $ctc= new ContactController();
            $nc = new NewsletterController();


            switch ($route) {
                case "homepageUser":
                    $dc->homepage();
                    break;
                    
                case "profile":
                    $uc->displayProfile();
                    break;

                case "updateProfile":
                    $uc->updateProfile();
                    break;

                case "toggleNewsletter":
                    $uc->toggleNewsletter();
                    break;
                
                case 'contact':
                    $ctc->contactUs();
                    break;

                case 'contactFromProfile':
                    $ctc->contactFromProfile();
                    break;  

                case 'resetPasswordFromProfile':
                     $uc->resetPasswordFromProfile();
                     break;

                /*case 'contactUsForm': 
                    $ctc->renderContactForm();
                    break;*/
                                    
                case "displayGame":
                    $gc->displayGame();
                    break;

                case "displayClick":
                    $gc->displayClick();
                    break;

                case "saveClickScore":
                    $gc->saveClickScore(); // ← Nouvelle route pour sauvegarder le score
                    break;
                    
                case "displayPixelArt":
                    $gc->displayPixelArt();
                    break;
                    
                case "memo":
                    $gc->displayMemo();
                    break;

                case "saveMemoryScore":
                    $gc->saveMemoryScore(); // ← Nouvelle route pour sauvegarder le score
                    break;
                    
                case "colorings":
                    $cc->displayDraw();
                    break;
                    
                case "coloringsListe":
                    $cc->getColoringsByCategorieJson();
                    break;
                    
                case "stories":
                    $sc->displayStories();
                    break;
                    
                case "getImage":
                    $sc->getImage();
                    break;
                    
                case "getStory":
                    $sc->getStory();
                    break;
                
                 case "forgottenPassword":
                    $ac->displayForgottenPassword();
                    break;
                
                    
                case "logout":
                    $ac->logout();
                    break;
                    
                default:
                    throw new Exception("Route utilisateur non gérée: $route");
            }

        } catch (Exception $e) {
            error_log("❌ Erreur route utilisateur ($route): " . $e->getMessage());
            
            // Redirection selon le type d'erreur
            if (strpos($e->getMessage(), "non authentifié") !== false) {
                $this->redirectTo("login");
            } elseif (strpos($e->getMessage(), "non validé") !== false) {
                $_SESSION['error'] = "Votre compte n'est pas encore validé. Veuillez vérifier vos emails.";
                $this->redirectTo("homepage");
            } else {
                throw $e;
            }
        }
    }

    /**
     * Gère les routes admin
     */
    private function handleAdminRoute(string $route, Utils $func): void
    {
        try {
            // Vérification des permissions admin
            if (!$func->isAuthentified()) {
                throw new Exception("Utilisateur non authentifié");
            }

            if (!$func->isAdmin()) {
                throw new Exception("Accès administrateur requis");
            }

            $dash = new DashboardController();
            $ac = new AuthController();
            $auc = new AdminUserController();
            $avc= new AvatarController();
            $sc = new StoryController();
            $gc = new GameController();
            $cc = new ColoringController();
            $ctc= new ContactController();
            $nc = new NewsletterController();


            switch ($route) {
                case "homepageAdmin":
                    $dash->homepage();
                    break;
                    
                case "dashboard":
                    $dash->displayDashboard();
                    break;
                    
                case "ajaxSearchUsers":
                    $auc->ajaxSearchUsers();
                    break;
                    
                case "allUsers":
                    $auc->allUsers();
                    break;
                    
                case "readOneUser":
                    $auc->readOneUser();
                    break;
                    
                case "resetPassword":
                    $auc->resetPassword();
                    break;
                    
                case "updateStatus":
                    $auc->resetStatus();
                    break;
                    
                case "updateRole":
                    $auc->resetRole();
                    break;
                    
                case "updateUserAvatar":
                    $auc->updateUserAvatar();
                    break;

                case "newsletterUnsubscribe":
                    $nc->unsubscribe();
                    break;
                    
                case "updateNewsletter":
                    $auc->resetNewsletter();
                    break;
                    
                case "response":
                    $ctc->response();
                    break;
                    
                case "messagerie":
                    $ctc->displayMessages();
                    break;
                    
                case "deleteMessage":
                    $ctc->deleteMessage();
                    break;
                    
                case "readMessage":
                    $ctc->readMessage();
                    break;
                    
                case "modifAvatar":
                    $avc->modifAvatarAdmin();
                    break;
                    
                case "deleteAvatar":
                    $avc->deleteAvatar();
                    break;
                    
                case "addAvatar":
                    $avc->addAvatar();
                    break;
                    
                case "addstoryStoriesAd":
                    $sc->addStory();
                    break;
                    
                case "StoriesAdmin":
                    $sc->ManageStories();
                    break;
                    
                case "modifColoring":
                    $cc->modifColorings();
                    break;
                    
                case "addColoring":
                    $cc->addColoring();
                    break;
                    
                case "deleteColoring":
                    $cc->deleteColoring();
                    break;
                    
                case "addCategorie":
                    $cc->addCategorie();
                    break;
                    
                case "modifGame":
                    $gc->modifGames();
                    break;

                case "forgottenPassword":
                    $ac->displayForgottenPassword();
                    break;

                case "adminNewsletter":
                    $nm->sendNewsletter();
                    break;


                /*case "resetPassword":
                    $ac->resetPassword();
                    break;*/
                    
                case "logout":
                    $ac->logout();
                    break;
                    
                default:
                    throw new Exception("Route admin non gérée: $route");
            }

        } catch (Exception $e) {
            error_log("❌ Erreur route admin ($route): " . $e->getMessage());
            
            // Redirection selon le type d'erreur
            if (strpos($e->getMessage(), "Accès administrateur requis") !== false) {
                $_SESSION['error'] = "Accès refusé. Vous devez être administrateur.";
                $this->redirectTo("homepage");
            } elseif (strpos($e->getMessage(), "non authentifié") !== false) {
                $this->redirectTo("login");
            } else {
                throw $e;
            }
        }
    }

    /**
     * Gère les erreurs globales
     */
    private function handleError(Exception $e): void
    {
        // Log de l'erreur
        error_log("========================================");
        error_log("ERREUR ROUTER");
        error_log("Message: " . $e->getMessage());
        error_log("Fichier: " . $e->getFile() . " (ligne " . $e->getLine() . ")");
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("========================================");

        // Affichage d'une page d'erreur user-friendly
        try {
            $dc = new DefaultController();
            
            // Stocker le message d'erreur en session
            $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer.";
            
            // Si c'est une route inconnue, afficher 404
            if (strpos($e->getMessage(), "Route inconnue") !== false) {
                $dc->_404();
            } else {
                // Sinon rediriger vers homepage
                $this->redirectTo("homepage");
            }
        } catch (Exception $fallbackException) {
            // Si même l'affichage de l'erreur échoue, montrer une erreur basique
            http_response_code(500);
            echo "<!DOCTYPE html>
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Erreur - Miaou</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: #f5f5f5;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .error-container {
                        background: white;
                        padding: 40px;
                        border-radius: 20px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                        text-align: center;
                        max-width: 500px;
                    }
                    h1 { color: #ff6b6b; margin-bottom: 20px; }
                    p { color: #666; margin-bottom: 30px; }
                    a {
                        display: inline-block;
                        padding: 12px 30px;
                        background: #47b972;
                        color: white;
                        text-decoration: none;
                        border-radius: 25px;
                        transition: all 0.3s;
                    }
                    a:hover {
                        background: #3a9d5e;
                        transform: translateY(-2px);
                    }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h1>😿 Oups !</h1>
                    <p>Une erreur inattendue s'est produite. Nos équipes ont été notifiées.</p>
                    <a href='?route=homepage'>Retour à l'accueil</a>
                </div>
            </body>
            </html>";
            exit;
        }
    }

    /**
     * Redirection vers une route
     */
    private function redirectTo(string $route): void
    {
        header("Location: index.php?route=" . $route);
        exit();
    }
}