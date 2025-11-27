<?php

class DefaultController extends AbstractController {
    
    public function __construct()
    {
        parent::__construct();
    }

  
    public function homepage() : void
{
    $am = new AvatarManager();
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();

    // Scripts communs (footer + burger)
    $scripts = $this->getDefaultScripts();
    $scripts = $this->addScripts(['assets/js/mess.js'
    ], $scripts);

    // Cas 1 : pas d'utilisateur connecté → page publique
    if (!isset($_SESSION['user'])) {
        unset($_SESSION['start_time']);
      //$_SESSION['start_time'] = time();

        $avatar = $am->getByName("Miaou");

        $this->render("homepage.html.twig", [
            'titre'        => 'Accueil',
            'elapsed_time' => 0,
            'avatar'       => $avatar,
            'start_time'   => null,
            'isUser'       => false
        ], $scripts);
        return;
    }

    // Cas 2 : utilisateur connecté → switch sur le rôle
    $avatar = $am->getById($_SESSION['user']['avatar']);
    $role   = $_SESSION['user']['role'];
    if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
}

    switch ($role) {
        case 1: // utilisateur standard
            
            $this->render("homepageUser.html.twig", [
                'titre'           => 'Activités',
                'user'            => $_SESSION['user'],
                'elapsed_time'    => $elapsedTime,
                'session'         => $_SESSION,
                'connected'       => true,
                'success_message' => $_SESSION['success_message'] ?? null,
                'avatar'          => [$avatar],
                'isUser'          => true,
                'start_time'      => $_SESSION['start_time']
            ], $scripts);
            break;

        case 2: // administrateur
            $this->render("homepageUser.html.twig", [
                'titre'           => 'Activités',
                'user'            => $_SESSION['user'],
                'role'            => $role,
                'elapsed_time'    => $elapsedTime,
                'session'         => $_SESSION,
                'connected'       => true,
                'success_message' => $_SESSION['success_message'] ?? null,
                'avatar'          => [$avatar],
                'isUser'          => true,
                'start_time'      => $_SESSION['start_time']
            ], $scripts);
            break;

        default:
            // rôle inconnu → page publique
            $avatar = $am->getByName("Miaou");
            $this->redirectTo("homepage");
            break;
        }
    }
   

    public function logout() : void
{
    unset($_SESSION['start_time']);
    session_destroy();
    session_start();
    $_SESSION['error_message'] = "Déconnexion effectuée !";

   // ⚡ Redirection HTTP → recharge complet
    $this->redirectTo('homepage');
    exit;
}
        
        
    

    public function _404() : void
    {
        $this->render("page404.html.twig", []);
    }



    public function pedagogie() {

            $am = new AvatarManager();
            $timesModels = new TimesModels();
            $elapsedTime = $timesModels->getElapsedTime();

            // Scripts communs (footer + burger)
            $scripts = $this->getDefaultScripts();
            $scripts = $this->addScripts(['assets/js/mess.js'
            ], $scripts);

        if (!isset($_SESSION['user'])) {
        
            $avatar = $am->getByName("Miaou");
            $this->render("pedagogie.html.twig", [
                'titre'   => 'Notre Philosophie',
                'avatar'  => [$avatar]], $scripts);
        }

        else if (isset($_SESSION['user'])) {

            

            $avatar = $am->getById($_SESSION['user']['avatar']);
            $_SESSION['start_time'] = time();
            
            $this->render("pedagogie.html.twig", [
                'titre'           => 'Notre Philosophie',
                'user'            => $_SESSION['user'],
                'elapsed_time'    => $elapsedTime,
                'session'         => $_SESSION,
                'connected'       => true,
                'success_message' => $_SESSION['success_message'] ?? null,
                'avatar'          => [$avatar],
                'isUser'          => true,
                'start_time'      => $_SESSION['start_time']
            ], $scripts);
            
            
        }
    }
}


