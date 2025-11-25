<?php

/*require_once("controllers/DefaultController.php");
require_once("controllers/AuthController.php");
date_default_timezone_set('Europe/Paris');
require_once("services/Functionality.php");
require_once("controllers/DashboardController.php");
require_once("controllers/GameController.php");
require_once("controllers/ColoringController.php");
require_once("controllers/StoryController.php");
require_once("controllers/UserController.php");
require_once("controllers/ContactController.php");*/

class Router {
    public function __construct()
    {

    }

    public function handleRequest(array $get)
    {
       // ===== DEBUG COMPLET - À RETIRER APRÈS =====
    /*echo "<div style='background: #f0f0f0; padding: 20px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h2>🔍 DEBUG ROUTER</h2>";
    
    // 1. Informations de base
    echo "<h3>1. Informations de base</h3>";
    echo "Route demandée: <strong>" . ($get["route"] ?? "homepage") . "</strong><br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session status: " . session_status() . " (2 = active)<br>";
    
    // 2. Contenu de la session
    echo "<h3>2. Session complète</h3>";
    if (empty($_SESSION)) {
        echo "<strong style='color: red;'>❌ SESSION VIDE</strong><br>";
    } else {
        echo "<pre style='background: white; padding: 10px;'>" . print_r($_SESSION, true) . "</pre>";
    }
    
    // 3. Utilisateur connecté
    echo "<h3>3. Utilisateur connecté</h3>";
    if (!isset($_SESSION['user'])) {
        echo "<strong style='color: red;'>❌ Aucun utilisateur en session</strong><br>";
    } else {
        echo "✅ Utilisateur trouvé:<br>";
        echo "- ID: " . ($_SESSION['user']['id'] ?? 'N/A') . "<br>";
        echo "- Nom: " . ($_SESSION['user']['firstname'] ?? 'N/A') . " " . ($_SESSION['user']['lastname'] ?? 'N/A') . "<br>";
        echo "- Role: " . ($_SESSION['user']['role'] ?? 'N/A') . "<br>";
        echo "- Statut: " . ($_SESSION['user']['statut'] ?? 'N/A') . "<br>";
    }
    
    // 4. Tests des méthodes Functionality
    $func = new Functionality();
    echo "<h3>4. Tests Functionality</h3>";
    $isAuth = $func->isAuthentified();
    $isValid = $func->isValidateUser();
    $isUser = $func->isUser();
    $isAdmin = $func->isAdmin();
    
    echo "- isAuthentified(): " . ($isAuth ? '✅ OUI' : '❌ NON') . "<br>";
    echo "- isValidateUser(): " . ($isValid ? '✅ OUI' : '❌ NON') . "<br>";
    echo "- isUser(): " . ($isUser ? '✅ OUI' : '❌ NON') . "<br>";
    echo "- isAdmin(): " . ($isAdmin ? '✅ OUI' : '❌ NON') . "<br>";
    
    // 5. Condition finale
    echo "<h3>5. Condition pour userRoutes</h3>";
    $finalCondition = $isAuth && $isValid && ($isUser || $isAdmin);
    echo "Condition (isAuth && isValid && (isUser || isAdmin)): " . ($finalCondition ? '✅ PASSE' : '❌ ÉCHOUE') . "<br>";
    
    echo "</div>";
    
    // Arrêt temporaire pour voir le debug
    if (($get["route"] ?? "homepage") === "profile") {
        echo "<p><strong>DEBUG ARRÊTÉ POUR ROUTE 'profile' - Cliquez sur Actualiser pour continuer</strong></p>";
        die();
    }*/
    // ===== FIN DEBUG =====  
        
        
        $func = new Utils();
        $dc = new DefaultController();
        $ac = new AuthController();
        $dash = new DashboardController();
        $gm = new GameController();
        $cc = new ColoringController();
        $sc = new StoryController();
        $uc = new UserController();
        $ctc = new ContactController();

        $route = isset($get["route"]) ? $get["route"] : "homepage";

        // Routes accessibles à tous (non connectés)
        $publicRoutes = [
            "homepage", "contact", "login", "check-login", 
            "register", "check-register", "displayModify", "modifyPassword", "logout"
        ];

        // Routes pour utilisateurs connectés et validés (role 1 ou 2)
        $userRoutes = [
            "homepageUser", "profile", "games", "pixelArt", 
            "memo", "colorings", "coloringsListe", "stories", "getImage", 
            "getStory", "displayGame", "displayPixelArt" 
        ];

        // Routes pour administrateurs uniquement (role 2)
        $adminRoutes = [
            "dashboard", "ajaxSearchUsers", "allUsers", "readOneUser", 
            "resetPassword", "updateStatus", "updateRole", "updateUserAvatar", 
            "updateNewsletter", "response", "messagerie", "deleteMessage", 
            "readMessage", "modifAvatar", "deleteAvatar", "addAvatar", 
            "addstoryStoriesAd", "StoriesAdmin", "modifColoring", "addColoring", "deleteColoring", "addCategorie", "modifGame"
        ];

        // Gestion des routes publiques
        if (in_array($route, $publicRoutes)) {
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
            }
        }
        // Gestion des routes pour utilisateurs connectés et validés
        elseif (in_array($route, $userRoutes)) {
            // Vérification : utilisateur connecté ET validé ET (role user OU admin)
            /*echo "isAuthentified: " . ($func->isAuthentified() ? 'OUI' : 'NON') . "<br>";
echo "isValidateUser: " . ($func->isValidateUser() ? 'OUI' : 'NON') . "<br>";
echo "isUser: " . ($func->isUser() ? 'OUI' : 'NON') . "<br>";
echo "isAdmin: " . ($func->isAdmin() ? 'OUI' : 'NON') . "<br>";
echo "Route demandée: " . $route . "<br>";
die(); // Arrêt pour voir les valeurs*/
            
            if ($func->isAuthentified() && $func->isValidateUser() && ($func->isUser() || $func->isAdmin())) {
                switch ($route) {
                    
                    case "homepageUser":
                        $dc->homepage();
                    break; 
                    case "profile":
                        $ac->displayProfile();
                    break;
                    //case "games":
                    case "displayGame":
                        $gm->displayGame();
                        break;
                    case "pixelArt":
                    case "displayPixelArt":
                        $gm->displayPixelArt();
                        break;
                    case "memo":
                        $gm->displayMemo();
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
                    case "logout":
                        $ac->logout();
                        break;
                }
            } else {
                // Redirection intelligente selon le statut de l'utilisateur
                if ($func->isAuthentified() && $func->isValidateUser()) {
                    // Utilisateur validé mais pas admin -> redirection vers homepageUser
                    $dc->redirectTo("homepager");
                } else {
                    // Utilisateur non connecté ou non validé -> redirection vers homepage
                    $dc->redirectTo("homepage");
                }
            }
        }
        // Gestion des routes administrateur
        elseif (in_array($route, $adminRoutes)) {
            // Vérification : utilisateur connecté ET admin
            if ($func->isAdmin()) {
                switch ($route) {
                    
                    case "homepageAdmin":
                        $dash->homepageAdmin();
                    break;
                    case "dashboard":
                        $dash->displayDashboard();
                        break;
                    case "ajaxSearchUsers":
                        $dash->ajaxSearchUsers();
                        break;
                    case "allUsers":
                        $dash->allUsers();
                        break;
                    case "readOneUser":
                        $dash->readOneUser();
                        break;
                    case "resetPassword":
                        $dash->resetPassword();
                        break;
                    case "updateStatus":
                        $dash->resetStatus();
                        break;
                    case "updateRole":
                        $dash->resetRole();
                        break;
                    case "updateUserAvatar":
                        $dash->updateUserAvatar();
                        break;
                    case "updateNewsletter":
                        $dash->resetNewsletter();
                        break;
                    case "response":
                        $dash->response();
                        break;
                    case "messagerie":
                        $dash->displayMessages();
                        break;
                    case "deleteMessage":
                        $dash->deleteMessage();
                        break;
                    case "readMessage":
                        $dash->readMessage();
                        break;
                    case "modifAvatar":
                        $dash->modifAvatarAdmin();
                        break;
                    case "deleteAvatar":
                        $dash->deleteAvatar();
                        break;
                    case "addAvatar":
                        $dash->addAvatar();
                        break;
                    case "addstoryStoriesAd":
                        $dash->addStory();
                        break;
                    case "StoriesAdmin":
                        $dash->ManageStories();
                        break;
                    case "modifColoring":
                        $dash->modifColorings();
                        break;
                    case "addColoring":
                        $dash->addColoring();
                        break;
                    case "deleteColoring":
                        $dash->deleteColoring();
                        break;
                    case "addCategorie":
                        $dash->addCategorie();
                        break;
                    case "modifGame":
                        $dash->modifGames();
                        break;
                        case "logout":
                        $ac->logout();
                        break;
                    
                }
            } else {
                // Redirection intelligente selon le statut de l'utilisateur
                if ($func->isAuthentified() && $func->isValidateUser()) {
                    // Utilisateur validé mais pas admin -> redirection vers homepageUser
                    $dc->redirectTo("homepage");
                } else {
                    // Utilisateur non connecté ou non validé -> redirection vers homepage
                    $dc->redirectTo("homepage");
                }
            }
        }
        // Route par défaut (404)
        else {
            $dc->_404();
        }
    }

    /**
     * Méthode pour rediriger vers une autre route
     */
    private function redirectTo($route) {
        header("Location: index.php?route=" . $route);
        exit();
    }
}