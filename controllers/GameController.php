<?php

class GameController extends AbstractController {
    
     public function __construct()
    {
        parent::__construct();
    } 
    
    public function displayGame() {
        $scripts = $this->addScripts(['public/assets/js/' ]);
       $this->render("game.html.twig", [
           'user' => $_SESSION['user'] ?? null
           ],$scripts); 
    }
    
    public function displayPixelArt() {
    
         $scripts = $this->addScripts(['public/assets/js/' ]);
    
        $this->render("pixelArt.html.twig", [
            'user' => $_SESSION['user'] ?? null
            ],$scripts);  
        
    }
    
    public function displayMemo(){
    
     $scripts = $this->addScripts(['public/assets/js/memory.js']);
      $this->render("memo.html.twig", [
          'user' => $_SESSION['user'] ?? null
          ],$scripts);
    }

   private function getUserIdFromSession(): ?int
    {
        // Récupération de l'ID utilisateur comme spécifié
        return $_SESSION['user']['id'] ?? null;
    }


    /** ***********jeu Click Souris***************************** */

    /**
     * Récupère le meilleur score actuel de l'utilisateur pour l'affichage initial.
     * Nous récupérons tous les meilleurs scores pour tous les niveaux.
     */
    private function getBestScoresForAllLevels(int $userId): array
    {
        $levels = ['facile', 'intermediaire', 'difficile'];
        $bestScores = [];
        foreach ($levels as $level) {
            $data = $this->gameManager->getBestScoreByUserId($userId, $level);
            $bestScores[$level] = $data['score'] ?? 0;
        }
        return $bestScores;
    }

    /**
     * Route GET : Affiche la page du jeu de clic (/jeu/souris).
     */
    public function displayClick(): string
    {
        $userId = $this->getUserIdFromSession();
        $userId = $_SESSION['user']['id'];
         $am = new AvatarManager();
            $timesModels = new TimesModels();
            $elapsedTime = $timesModels->getElapsedTime();
            $func = new Utils();

            
            $scripts = $this->addScripts(['assets/js/mess.js', 'assets/js/clickSouris.js'
            ], $scripts);
             $avatar = $am->getById($_SESSION['user']['avatar']);
            $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
       
        // Vérification de la connexion
        if ($userId === null) {
            // Gérer la redirection si l'utilisateur n'est pas connecté
            // return $this->redirect('/login');
            return $this->twig->render('error/404.html.twig');
        }

        // Récupérer le meilleur score pour l'affichage initial pour tous les niveaux
        $userBestScores = $this->getBestScoresForAllLevels($userId);

        // Définition des scripts JS à charger uniquement sur cette vue
        $scripts = ['clickSouris.js'];

        return $this->twig->render('click.html.twig', [
            'userBestScores' => $userBestScores, // Tableau des meilleurs scores par niveau
            'titre'           => 'Jeu ClickSouris',
                'user'            => $_SESSION['user'],
                'elapsed_time'    => $elapsedTime,
                'session'         => $_SESSION,
                'connected'       => true,
                'success_message' => $_SESSION['success_message'] ?? null,
                'avatar'          => $avatar,
                'isUser'          => true,
                'start_time'      => $_SESSION['start_time']
        ], $scripts);
    }

    /**
     * Route POST : Sauvegarde du score via AJAX (/api/score/save).
     */
    public function saveClickScore(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        $userId = $this->getUserIdFromSession();
        if ($userId === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non connecté.']);
            return;
        }

        // Récupération des données POST JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $newScore = $input['score'] ?? null;
        $level = $input['level'] ?? null; // Récupération du niveau envoyé par JS

        if (!is_int($newScore) || $newScore < 0 || !in_array($level, ['facile', 'intermediaire', 'difficile'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données de score ou niveau invalides.']);
            return;
        }

        // Utilisation de la méthode du GameManager pour gérer l'insertion/mise à jour par niveau
        $result = $this->gameManager->saveOrUpdateBestScore($userId, $newScore, $level);

        // Envoyer le résultat au client
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'],
            'newBestScore' => $result['newBestScore'],
            'level' => $level // Renvoyer le niveau pour la mise à jour côté client
        ]);
    }


    
    
    
    
    
}