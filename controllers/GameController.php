<?php

class GameController extends AbstractController {
    
     public function __construct()
    {
        parent::__construct();

    } 


    private function getUserIdFromSession(): ?int {
        // Récupération de l'ID utilisateur comme spécifié
        return $_SESSION['user']['id'] ?? null;
    }

    public function displayGame() : void
    {
    $am = new AvatarManager();
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();
    $func= new Utils();

    $avatar = $am->getById($_SESSION['user']['avatar']);
 
    $avatar->setUrlMini($func->asset($avatar->getUrlMini()));

    // Scripts communs (footer + burger)
    $scripts = $this->getDefaultScripts();
    $scripts = $this->addScripts(['assets/js/mess.js'
    ], $scripts);

            
            $this->render("homepageGame.html.twig", [
                'titre'           => 'Activités',
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
 * ==================== JEU CLICK SOURIS ====================
 */

/**
 * Récupère les meilleurs scores par userId pour tous les niveaux
 */
private function getBestScoresForAllLevels(int $userId): array
{
    $levels = ['facile', 'intermediaire', 'difficile'];
    $bestScores = [];
    $gm = new GameManager();
    
    foreach ($levels as $level) {
        $data = $gm->getBestScoreByUserId($userId, $level);
        $bestScores[$level] = $data['score'] ?? 0;
    }
    
    // Debug
    error_log("Click Souris - Best Scores pour user $userId: " . print_r($bestScores, true));
    
    return $bestScores;
}

/**
 * Route GET : Affiche la page du jeu Click Souris
 */
public function displayClick(): void
{
    if (!isset($_SESSION['user']['id'])) {
        $this->render('error/404.html.twig');
        return;
    }

    $userId = $_SESSION['user']['id'];
    $am = new AvatarManager();
    $timesModels = new TimesModels();
    $func = new Utils();

    $elapsedTime = $timesModels->getElapsedTime();
    $avatar = $am->getById($_SESSION['user']['avatar']);
    $avatar->setUrlMini($func->asset($avatar->getUrlMini()));

    // ✅ Récupérer les meilleurs scores pour tous les niveaux
    $userBestScores = $this->getBestScoresForAllLevels($userId);

    // Scripts
    $scripts = $this->getDefaultScripts();
    $scripts = $this->addScripts([
        'assets/js/mess.js',
        'assets/js/clickSouris.js'
    ], $scripts);

    $this->render('click.html.twig', [
        'titre'           => 'Jeu Click Souris',
        'user'            => $_SESSION['user'],
        'elapsed_time'    => $elapsedTime,
        'session'         => $_SESSION,
        'connected'       => true,
        'avatar'          => $avatar,
        'isUser'          => true,
        'start_time'      => $_SESSION['start_time'],
        'userBestScores'  => $userBestScores
    ], $scripts);
}

/**
 * Route POST : Sauvegarde du score via AJAX
 */
public function saveClickScore(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    $userId = $_SESSION['user']['id'] ?? null;
    if ($userId === null) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non connecté.']);
        exit;
    }

    // Récupération des données POST JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $newScore = $input['score'] ?? null;
    $level = $input['level'] ?? null;

    // Validation
    if (!is_int($newScore) || $newScore < 0 || !in_array($level, ['facile', 'intermediaire', 'difficile'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données de score ou niveau invalides.']);
        exit;
    }

    try {
        $gm = new GameManager();
        
        // Sauvegarder le score et déterminer si c'est un record
        $result = $gm->saveOrUpdateBestScore($userId, $newScore, $level);

        // Debug
        error_log("Click Souris - Score sauvegardé: " . print_r($result, true));

        // Envoyer le résultat au client
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'],
            'newBestScore' => $result['newBestScore'],
            'level' => $level
        ]);

    } catch (Exception $e) {
        error_log("Erreur saveClickScore: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement'
        ]);
    }

    exit;
}
    /**********************************************MEMO****************************************************** */

  // Dans GameController.php - Méthode memo()

public function displayMemo(): void
{   
    $am = new AvatarManager();
    $timesModels = new TimesModels();
    $elapsedTime = $timesModels->getElapsedTime();
    $func = new Utils();
    $gm = new GameManager();

    $avatar = $am->getById($_SESSION['user']['avatar']);
    $avatar->setUrlMini($func->asset($avatar->getUrlMini()));

    // Scripts
    $scripts = $this->getDefaultScripts();
    $scripts = $this->addScripts([
        'assets/js/mess.js',
        'assets/js/memory.js'
    ], $scripts);

    // ✅ Récupérer les meilleurs scores PAR UTILISATEUR
    $userId = $_SESSION['user']['id'];
    
    
    // DEBUG - Afficher l'ID utilisateur
    error_log("Memory - User ID: " . $userId);
    
    $bestScores = [
        'easy' => $gm->getBestScoresByUserAndLevel($userId, 'easy'),
        'intermediate' => $gm->getBestScoresByUserAndLevel($userId, 'intermediate'),
        'hard' => $gm->getBestScoresByUserAndLevel($userId, 'hard'),
    ];

   
    
    // DEBUG - Afficher les scores récupérés
    error_log("Memory - Best Scores: " . print_r($bestScores, true));

    $this->render('memo.html.twig', [
        'titre' => 'Jeu Memo',
        'user' => $_SESSION['user'],
        'elapsed_time' => $elapsedTime,
        'session' => $_SESSION,
        'connected' => true,
        'success_message' => $_SESSION['success_message'] ?? null,
        'avatar' => $avatar,
        'isUser' => true,
        'start_time' => $_SESSION['start_time'],
        'bestScores' => $bestScores,
    ], $scripts);
}

// ✅ Méthode saveMemoryScore() modifiée
public function saveMemoryScore(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $moves = (int)($data['moves'] ?? 0);
    $time = (int)($data['time'] ?? 0);
    $level = $data['level'] ?? '';

    if (!in_array($level, ['easy', 'intermediate', 'hard']) || $moves <= 0 || $time <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }

    try {
        $gm = new GameManager();
        
        // ✅ Sauvegarder uniquement si c'est un meilleur score
        $isNewRecord = $gm->saveOrUpdateMemoryScore($userId, $moves, $level, $time);

        // Récupérer les meilleurs scores mis à jour
        $bestScores = $gm->getBestScoresByUserAndLevel($userId, $level);

        echo json_encode([
            'success' => true,
            'isNewRecord' => $isNewRecord,
            'message' => $isNewRecord ? 'Nouveau record !' : 'Score enregistré',
            'bestScores' => $bestScores
        ]);

    } catch (Exception $e) {
        error_log("Erreur saveMemoryScore: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement'
        ]);
    }

    exit;
}

/*******************************Pixel Art********************************* */
   

    public function displayPixelArt($level = 'easy') {
    $am          = new AvatarManager();
    $timesModels = new TimesModels();
    $func        = new Utils();

    $elapsedTime = $timesModels->getElapsedTime();

    $avatar = $am->getById($_SESSION['user']['avatar']);
    $avatar->setUrlMini($func->asset($avatar->getUrlMini()));

    // Scripts communs + PixelArt
    $scripts = $this->getDefaultScripts();
    $scripts = $this->addScripts([
        'assets/js/mess.js',
        'assets/js/pixelart.js'
    ], $scripts);

    // Config des niveaux
    $levels = [
        'easy' => [
            'gridSize' => 8,
            'colors'   => ['#000000','#FFFFFF','#FF5733','#33FF57','#3357FF','#FFFF33']
        ],
        'intermediate' => [
            'gridSize' => 12,
            'colors'   => ['#000000','#FFFFFF','#3357FF','#FFFF33','#FF33FF','#33FFFF',
                           '#FF8800','#8800FF','#0088FF','#00FF88','#FF0088','#888888']
        ],
        'hard' => [
            'gridSize' => 16,
            'colors'   => ['#FF5733','#33FF57','#3357FF','#FFFF33','#FF33FF','#33FFFF',
                           '#FF8800','#8800FF','#0088FF','#00FF88','#FF0088','#888888',
                           '#000000','#FFFFFF','#AA5533','#55AA33']
        ]
    ];

    $config = $levels[$level] ?? $levels['easy'];

    $this->render("pixelart.html.twig", [
        'titre'        => 'Pixel Art',
        'user'         => $_SESSION['user'],
        'elapsed_time' => $elapsedTime,
        'session'      => $_SESSION,
        'connected'    => true,
        'avatar'       => $avatar,
        'isUser'       => true,
        'start_time'   => $_SESSION['start_time'],
        'colors'       => $config['colors'],
        'gridSize'     => $config['gridSize'],
        'level'        => $level,
        'audio_bg'     => $func->asset('assets/sounds/game/pixelart/backgroundMusicMemory.wav'),
        'audio_success'=> $func->asset('assets/sounds/game/pixelart/success.mp3')
    ], $scripts);
}
}