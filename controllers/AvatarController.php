<?php

class AvatarController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
    }



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

    // Rendu de la vue / Render view
    $this->render('modifAvatarAdmin.html.twig', [
        'avatars'         => $avatars,
        'error_message'   => $error,
        'success_message' => $success,
        'csrf_token'      => $token,
        'user' => $_SESSION['user'] ?? null,
        'avatar' => [$avatar],
        'session' => $_SESSION,
        'connected' => true,
        'isUser' => false,
        'isAdmin' => true,
        'elapsed_time' => $elapsedTime,
        'start_time' => $_SESSION['start_time'] ?? time(),
        'nbrMessages' => $nbrMessages,
        'titre' => 'Admin Modification Avatar'
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
    
        $scripts = $this->addScripts([
            'https://kit.fontawesome.com/3c515cc4da.js',
            'assets/js/formController.js',
            'assets/js/adminjs/ajaxOneUser.js',
            'assets/js/adminjs/ajaxSearchUsers.js',
            'assets/js/adminjs/coloringAdmin.js',
            'assets/js/adminjs/storyAdmin.js',
            'assets/js/adminjs/modifyAvatarAdmin.js',
        ]);
        
        // Rendu de la vue / Render view
        $this->render("modifAvatarAdmin.html.twig", [
            'avatars' => $avatars, 
            'error_message' => $error,
            'success_message' => $success,
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
            'titre' => 'Dashboard Admin'
        ], $scripts);
        
        exit();
    }
}