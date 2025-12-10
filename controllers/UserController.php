<?php

class UserController extends AbstractController {
    
    public function __construct() {
        parent::__construct(); // ← cette ligne est indispensable !
    }

/**
 * Affiche la page de profil utilisateur
 */
public function displayProfile(): void
{
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['user']['id'])) {
        $_SESSION['error_message'] = "Vous devez être connecté.";
        $this->redirectTo('login');
        return;
    }

    $userId = $_SESSION['user']['id'];
    $um = new UserManager();
    $am = new AvatarManager();
    $tm = new CSRFTokenManager();
    $func = new Utils();
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();
    
    // Récupérer l'utilisateur depuis la BDD
    $user = $um->getOneUserById($userId);
    
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        $this->redirectTo('homepage');
        return;
    }
    
    // Récupérer l'avatar actuel
    $avatar = $am->getById($_SESSION['user']['avatar']);
     $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
    
    
    // Récupérer tous les avatars pour le formulaire
   $avatarItems = $am->findAllAvatars();
    foreach ($avatarItems as $avatarItem) {
    $avatarItem->setUrlMini($func->asset($avatarItem->getUrlMini()));
    $avatarItem->setUrl($func->asset($avatarItem->getUrl()));
    }
    // Générer token CSRF
    $csrf_token = $tm->generateCSRFToken();
    
    // Scripts JS nécessaires
    $scripts = $this->addScripts([
        'assets/js/formController.js',
        'assets/js/profile.js'
    ]);

    // Convertir l'objet User en array pour Twig
    $userData = [
        'id' => $user->getId(),
        'firstname' => $user->getFirstname(),
        'age' => $user->getAge(),
        'email' => $user->getEmail(),
        'avatar' => $user->getAvatar(),
        'newsletter' => $user->getNewsletter(),
        'role' => $user->getRole(),
        'statut' => $user->getStatut()
    ];
    
     
    unset($_SESSION['success_message'], $_SESSION['error_message']);

    $this->render('profile.html.twig', [
         'titre'           => 'Profil',
        'user' => $userData,
         'elapsed_time'    => $elapsedTime,
        'avatar'          => $avatar,
        'avatarItems' => $avatarItems,
        'csrf_token' => $csrf_token,
         'connected'       => true,
        'isUser'          => true,
        'start_time'      => $_SESSION['start_time'],
        'session' => $_SESSION,
        'success_message' => $_SESSION['success_message'] ?? null,
        'error_message' => $_SESSION['error_message'] ?? null
    ], $scripts);
    
    // Nettoyer les messages
    unset($_SESSION['success_message'], $_SESSION['error_message']);
}

/**
 * Met à jour le profil utilisateur (AJAX)
 */
public function updateProfile(): void
{

    // Nettoyer le buffer
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    
    // Vérifier POST - IMPORTANT : Ne pas vérifier REQUEST_METHOD avant de lire les données
    try {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            throw new Exception('Vous devez être connecté');
        }
        
        $func = new Utils();
        $tm = new CSRFTokenManager();
        
        // Log pour debug
        error_log("=== UPDATE PROFILE === Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("=== UPDATE PROFILE === POST data: " . json_encode($_POST));
        
        // Vérifier les champs
        if (!$func->checkPostKeys(['firstname', 'age', 'email', 'avatar', 'csrf_token'])) {
            throw new Exception('Tous les champs sont requis');
        }
        
        // Récupérer les données
        $data = [
            'firstname' => $func->e(trim($_POST['firstname'])),
            'age' => (int) $_POST['age'],
            'email' => strtolower(trim($_POST['email'])),
            'avatar' => (int) $_POST['avatar'],
            'csrf_token' => $_POST['csrf_token']
        ];
        
        error_log("=== UPDATE PROFILE === Data parsed: " . json_encode($data));
        
        // Vérifier CSRF
        if (!$tm->validateCSRFToken($data['csrf_token'])) {
            throw new Exception('Token CSRF invalide');
        }
        
        // Validations
        if (strlen($data['firstname']) < 2 || strlen($data['firstname']) > 60) {
            throw new Exception('Le prénom doit contenir entre 2 et 60 caractères');
        }
        
        if ($data['age'] < 1 || $data['age'] > 120) {
            throw new Exception('Âge invalide');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email invalide');
        }
        
        // Vérifier que l'email n'est pas déjà utilisé par un autre user
        $um = new UserManager();
        $existingUser = $um->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $_SESSION['user']['id']) {
            throw new Exception('Cet email est déjà utilisé');
        }
        // Scripts JS nécessaires
        $scripts = $this->addScripts([
        'assets/js/formController.js',
        'assets/js/profile.js',
        'assets/js/mess.js',
    ]);
        
        // Mettre à jour en BDD - Utiliser updateProfile au lieu de update
        $updated = $um->updateProfile(
            $_SESSION['user']['id'],
            $data['firstname'],
            $data['age'],
            $data['email'],
            $data['avatar']
        );
        
       if (!$updated) {
            throw new Exception('Échec de la mise à jour');
        }

        // Mettre à jour la session
        $_SESSION['user']['firstname'] = $data['firstname'];
        $_SESSION['user']['age']       = $data['age'];
        $_SESSION['user']['email']     = $data['email'];
        $_SESSION['user']['avatar']    = $data['avatar'];

        // Message flash
        $_SESSION['success_message'] = "Profil mis à jour avec succès !";

        // ✅ Redirection classique vers profil
        $this->redirectTo('profile');
        return;

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        $this->redirectTo('profile');
        return;
    }
    exit;
}


/**
 * Toggle newsletter (abonnement/désabonnement)
 */
public function toggleNewsletter(): void
{
    // Vérifier POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = "Méthode non autorisée";
        $this->redirectTo('profile');
        return;
    }
    
    try {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            throw new Exception('Vous devez être connecté');
        }
        
        $tm = new CSRFTokenManager();
        
        // Vérifier CSRF
        if (!isset($_POST['csrf_token']) || !$tm->validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Token CSRF invalide');
        }
        
        $userId = $_SESSION['user']['id'];
        $currentNewsletter = $_SESSION['user']['newsletter'] ?? 0;
        
        // Inverser le statut
        $newNewsletter = $currentNewsletter == 1 ? 0 : 1;
        
        // Mettre à jour en BDD
        $um = new UserManager();
        $updated = $um->updateNewsletter($userId, $newNewsletter);
        
        if (!$updated) {
            throw new Exception('Échec de la mise à jour');
        }
        
        // Mettre à jour la session
        $_SESSION['user']['newsletter'] = $newNewsletter;
        
        /*$message = $newNewsletter == 1 
            ? "Vous êtes maintenant abonné à la newsletter !" 
            : "Vous êtes maintenant désabonné de la newsletter.";*/
        
        $_SESSION['success_message'] = $message;
        
    } catch (Exception $e) {
        error_log("Erreur toggleNewsletter: " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    $this->redirectTo('profile');
}

/**
 * Réinitialisation de mot de passe depuis le profil
 */
public function resetPasswordFromProfile(): void
{
    // ✅ Nettoyer le buffer de sortie
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Vérifier POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }
    
    try {
        $func = new Utils();
        
        // Vérifier les données POST
        if (!$func->checkPostKeys(['id', 'csrf_token'])) {
            throw new Exception('Données manquantes (id ou csrf_token)');
        }

        $userId = (int) $_POST['id'];
        $csrfToken = $_POST['csrf_token'];

        // Validation de l'ID
        if ($userId <= 0) {
            throw new Exception('ID utilisateur invalide');
        }
        
        // Vérifier que c'est bien l'utilisateur connecté
        if (!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] != $userId) {
            throw new Exception('Action non autorisée');
        }

        // Vérification CSRF
        $tm = new CSRFTokenManager();
        if (!$tm->validateCSRFToken($csrfToken)) {
            throw new Exception('Token CSRF invalide');
        }

        // Récupérer l'utilisateur
        $um = new UserManager();
        $user = $um->getOneUserById($userId);
        
        if (!$user) {
            throw new Exception('Utilisateur non trouvé');
        }

        // Générer le nouveau mot de passe
        $newPassword = $func->generateRandomPassword(12);
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

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
            $newPassword
        );
        
        // Préparer le message pour après le logout
        $_SESSION['success_message'] = 'Mot de passe réinitialisé ! Consultez votre email.';

        // Réponse de succès
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès. Un email a été envoyé.',
            'user_id' => $userId
        ]);

    } catch (Exception $e) {
        error_log("Erreur resetPasswordFromProfile: " . $e->getMessage());
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}



   
}