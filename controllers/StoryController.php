<?php

class StoryController extends AbstractController {

    public function getImageUrl() {
        $characterId = $_POST['character_id'];
        $locationId = $_POST['location_id'];
        $itemId = $_POST['item_id'];

        $storyManager = new storyManager();
        $imageUrl = $storyManager->getUrl($characterId, $locationId, $itemId);

        header('Content-Type: application/json');
        echo json_encode(['url' => $imageUrl]);
        exit;
    }
    
    public function displayStories() {
        $pm = new CharacterManager();
        $lm = new LocationManager();
        $om = new ItemManager();
        $am = new AvatarManager();
        $avatar = $am->getById($_SESSION['user']['avatar']);
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        $func = new Utils();
        
        
    $scripts = $this->addScripts(['assets/js/ajaxStory.js']);

        $characters = $pm->getAllCharacters();
        $locations = $lm->getAllLocations();
        $items = $om->getAllItems();

        $avatar->setUrlMini($func->asset($avatar->getUrlMini()));
        
        if (isset($_SESSION['error'])) {
        unset($_SESSION['error']);
        }
            if (isset($_SESSION['success_message'])) {
        unset($_SESSION['success_message']);
        }


        
        //var_dump ($characters, $items, $locations, $_SESSION);
        //exit;

        return $this->render('story.html.twig', [
            'titre' => 'Histoires',
            'user' => $_SESSION['user'] ?? null,
            //'isConnecte' => true,
            'session'         => $_SESSION,
            'connected'       => true,
            'elapsed_time' => $elapsedTime,
            'characters' => $characters,
            'locations' => $locations,
            'items' => $items,
            'avatar' => $avatar,
            'isUser'          => true,
            'start_time'      => $_SESSION['start_time']
        ], $scripts);
    }

    
    
    public function getImage() {
    $entity = $_GET['entity'] ?? '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        switch ($entity) {
            case 'character':
                $manager = new CharacterManager();
                break;
            case 'item':
                $manager = new ItemManager();
                break;
            case 'location':
                $manager = new LocationManager();
                break;
            default:
                http_response_code(400);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['error' => 'Invalid entity']);
                exit;
        }

        $data = $manager->getById($id);

        if (!$data) {
            http_response_code(404);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Not found']);
            exit;
        }

        // Nettoyer les buffers pour éviter tout output parasite
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'url' => $data->getUrl(),
            'alt' => $data->getAlt()
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    
    public function getStory() {
        $characterId = $_GET['perso'];
        $itemId = $_GET['item'];
        $locationId  = $_GET['location'];
        
        
        $storyManager = new StoryManager();
        $story = $storyManager->getStoryByCriteria($characterId, $itemId, $locationId);
                
        header('Content-Type: application/json');
        
        echo json_encode($story);
        exit;
    }

    

/*********************************************************************/
/*********************ADMIN GESTION DES StoryS*********************/ 
 
 
/**
     * Fonction principale pour gérer les Storys
     * Gère l'ajout d'éléments et calcule les combinaisons manquantes
     * @return array Données pour la vue
     */
    /**
     * Méthode principale pour la route StoriesAdmin
     * Gère l'affichage de la vue avec toutes les données nécessaires
     * @return void Affiche la vue storiesAdmin.html.twig
     */
public function ManageStories(): void {
    // Traitement des données POST avant l'affichage
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handlePostRequests();
        // Redirection après traitement pour éviter la re-soumission
        //header('Location: index.php?route=StoriesAdmin');
       // exit;
    }
    
    // Récupération des données pour la gestion des Storys
    $viewData = $this->manageStoriesLogic();
    $categorieManager = new StoryCategorieManager();
$categories = $categorieManager->getAllCategories(); 
    //$scripts = $this->addScripts(['public/assets/js/storyAdmin.js']);
    
    $csrfToken = new CSRFTokenManager();
    $token = $csrfToken->generateCSRFToken();
    
    // Fusion des données avec les éléments nécessaires pour le template
    $templateData = array_merge($viewData, [
        'messages' => '',
        'error_message' => $_SESSION['error_message'] ?? '',
        'success_message' => $_SESSION['success_message'] ?? '',
        'csrf_token' => $token,
        'categories' => $categories,
    ]);
    $scripts = $this->addScripts(['public/assets/js/formController.js', 'public/assets/js/common.js', 'public/assets/js/global.js', 'public/assets/js/home.js', 'public/assets/js/storyAdmin.js']);
    
    $this->render('storiesAdmin.html.twig', $templateData, $scripts);
    
    // Nettoyage des messages après affichage
    unset($_SESSION['error_message'], $_SESSION['success_message']);
}

/**
 * Gère toutes les requêtes POST (ajout d'éléments et d'Storys)
 */
private function handlePostRequests(): void {
    $csrfToken = new CSRFTokenManager();
    
    // Validation du token CSRF
    if (!$csrfToken->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Token de sécurité invalide";
        return;
    }
    
    // Traitement selon le type de soumission
    if (isset($_POST['submit_add'])) {
        $this->processAddElement();
    } elseif (isset($_POST['submit_stories'])) {
        $this->processAddStories();
    }
}

/**
 * Traite l'ajout d'un nouvel élément (item, location, character)
 */
private function processAddElement(): void {
    $result = $this->handleElementAddition();
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

/**
 * Traite l'ajout de nouvelles Storys
 */
private function processAddStories(): void {
    $result = $this->handleStoriesCreation();
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

/**
 * Logique métier pour la gestion des Storys
 * Séparée de l'affichage pour une meilleure testabilité
 * @return array Données pour la vue
 */
public function manageStoriesLogic(): array {
    // Récupération des statistiques actuelles
    $currentStats = $this->getCurrentStats();
    
    // Calcul des nouvelles combinaisons nécessaires
    $missingCombinations = $this->getMissingCombinations();
    $nbTextareasToDisplay = count($missingCombinations);
    
    // Récupération des catégories pour le formulaire
    $StoryCategorieManager = new StoryCategorieManager();
    $categories = $StoryCategorieManager->getAllCategories();
    
    return [
        // Statistiques actuelles (noms compatibles avec le template Twig)
        'O_actuel' => $currentStats['nb_items'],
        'L_actuel' => $currentStats['nb_locationx'], 
        'P_actuel' => $currentStats['nb_characters'],
        'H_existantes' => $currentStats['nb_Storys_existantes'],
        'total_combinaisons_possibles' => $currentStats['nb_combinaisons_possibles'],
        
        // Données pour les nouvelles combinaisons
        'nb_textareas_to_display' => $nbTextareasToDisplay,
        'new_combinations' => $missingCombinations,
        
        // Données supplémentaires pour la vue
        'categories' => $categories,
        
        // Informations sur la requête pour les conditions Twig
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'has_post_data' => $_SERVER['REQUEST_METHOD'] === 'POST'
    ];
}

/**
 * Récupère toutes les combinaisons manquantes avec les détails nécessaires
 * @return array Liste des combinaisons manquantes avec noms
 */
private function getMissingCombinations(): array {
    $itemManager = new itemManager();
    $locationManager = new locationManager();
    $characterManager = new characterManager();
    $StoryManager = new StoryManager();
    
    // Récupération de tous les éléments
    $items = $itemManager->getAllitems();
    $locationx = $locationManager->getAlllocationx();
    $characters = $characterManager->getAllcharacters();
    
    // Récupération des combinaisons existantes
    $existingCombinations = $StoryManager->getExistingCombinations();
    
    // Calcul des combinaisons manquantes
    $missingCombinations = [];
    
    foreach ($items as $item) {
        foreach ($locationx as $location) {
            foreach ($characters as $character) {
                // Créer une clé pour vérifier dans les combinaisons existantes
                $checkKey = $item->getId() . '-' . $location->getId() . '-' . $character->getId();
                
                // Vérifier si cette combinaison existe déjà
                if (!isset($existingCombinations[$checkKey])) {
                    $missingCombinations[] = [
                        'item_id' => $item->getId(),
                        'item_nom' => $item->getitemName(),
                        'location_id' => $location->getId(),
                        'location_nom' => $location->getlocationName(),
                        'character_id' => $character->getId(),
                        'character_nom' => $character->getPersoName()
                    ];
                }
            }
        }
    }
    
    return $missingCombinations;
}

/**
 * Récupère les statistiques actuelles (nombre d'items, locationx, characters, Storys)
 * @return array Statistiques actuelles
 */
private function getCurrentStats(): array {
    $itemManager = new itemManager();
    $nbitems = $itemManager->countitems();
    
    $locationManager = new locationManager();
    $nblocationx = $locationManager->countlocationx();
    
    $characterManager = new characterManager();
    $nbcharacters = $characterManager->countcharacters();
    
    $StoryManager = new StoryManager();
    $storyManager = $StoryManager->countStories();
        
    return [
        'nb_items' => $nbitems,
        'nb_locationx' => $nblocationx,
        'nb_characters' => $nbcharacters,
        'nb_Storys_existantes' => $storyManager,
        'nb_combinaisons_possibles' => $StoryManager->calculateTotalCombinations(
            $nbitems, $nblocationx, $nbcharacters
        )
    ];
}

/**
 * Gère l'ajout d'un nouvel élément (item, location ou character)
 * @return array Résultat de l'opération (success, message)
 */
public function handleElementAddition(): array {
    $typeAjout = $_POST['type_ajout'] ?? '';
    $nomNouvelElement = trim($_POST['nom_nouvel_element'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $alt = trim($_POST['alt'] ?? '');
    
    if (empty($nomNouvelElement)) {
        return ['success' => false, 'message' => "Veuillez entrer un nom pour le nouvel élément."];
    }
    
    try {
        $elementId = null;
        
        switch ($typeAjout) {
            case 'item':
                $itemManager = new itemManager();
                $elementId = $itemManager->additem([
                    'item_name' => $nomNouvelElement,
                    'item_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "item '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            case 'location':
                $locationManager = new locationManager();
                $elementId = $locationManager->addlocation([
                    'location_name' => $nomNouvelElement,
                    'location_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "location '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            case 'character':
                $characterManager = new characterManager();
                $elementId = $characterManager->addcharacter([
                    'perso_name' => $nomNouvelElement,
                    'perso_description' => $description,
                    'url' => $url,
                    'alt' => $alt
                ]);
                $message = "character '" . htmlspecialchars($nomNouvelElement) . "' ajouté avec succès.";
                break;
                
            default:
                return ['success' => false, 'message' => "Type d'ajout invalide."];
        }
        
        return ['success' => true, 'message' => $message, 'element_id' => $elementId];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Gère la création d'Storys multiples
 * @return array Résultat de l'opération
 */
// Dans votre méthode principale qui traite les actions
/*public function handleAction() {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_single_story':
            return $this->handleSingleStoryCreation();
            
        case 'add_stories':
            return $this->handleStoriesCreation();
            
        // autres actions...
    }
}*/


/**
 * Gère la création d'une Story unique
 * @return array Résultat de l'opération
 */
private function handleSingleStoryCreation(): array {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_single_story':
            $result = $this->handleSingleStoryCreation();
            break;
            
        case 'add_multiple_stories':
            $result = $this->handleStoriesCreation();
            break;
      echo "<pre>";
var_dump($_POST);
echo "</pre>";      
    
    $story = $_POST['story'] ?? [];
    echo "📦 Story brut :";
var_dump($story);
    
    // Debug
    error_log("=== SINGLE STORY CREATION ===");
    error_log("Story data: " . print_r($story, true));
    
    if (!is_array($story)) {
        return ['success' => false, 'message' => 'Données invalides'];
    }
    
    // Vérifier le contenu
    $func = new Utils();

$content = trim($story['Story_content'] ?? '');
if (empty($content)) {
    return ['success' => false, 'message' => 'Contenu requis'];
}

// Nettoyer le HTML avant insertion
$sanitizedContent = $func->sanitizeHtml($content);

// Mettre à jour le tableau $story pour qu’il contienne la version filtrée
$story['Story_content'] = $sanitizedContent;
    
    // Vérifier les IDs obligatoires
    $characterId = !empty($story['character_id']) ? (int)$story['character_id'] : null;
    $itemId = !empty($story['item_id']) ? (int)$story['item_id'] : null;
    $locationId = !empty($story['location_id']) ? (int)$story['location_id'] : null;
    
    if ($locationId === null || $locationId === 0) {
        return ['success' => false, 'message' => 'location requis'];
    }
    if ($characterId === null || $characterId === 0) {
        return ['success' => false, 'message' => 'character requis'];
    }
    if ($itemId === null || $itemId === 0) {
        return ['success' => false, 'message' => 'item requis'];
    }
    
    try {
        $StoryManager = new StoryManager();
        $storyData = [
    'Story_titre' => !empty($story['titre']) ? trim($story['titre']) : "Story générée",
    'Story_categorie' => !empty($story['categorie']) ? $story['categorie'] : null,
    'character' => $characterId,
    'item' => $itemId,
    'location' => $locationId,
    'Story_content' => $sanitizedContent,
    'audio' => !empty($story['audio']) ? $story['audio'] : null,
    'url' => !empty($story['url']) ? $story['url'] : null
];
echo ($storyData);
exit;

        error_log("StoryData envoyé à addStory: " . print_r($storyData, true));
        $StoryManager->addStory($storyData);
        return ['success' => true, 'message' => 'Story créée avec succès !'];
        
    } catch (Exception $e) {
        error_log("Error creating single story: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        $_SESSION['success_message'] = 'Story créée avec succès !';
        $_SESSION['error_message'] = $result['message'];
    }
}
}
}


/**
 * Gère la création d'Storys multiples
 * @return array Résultat de l'opération
 */
public function handleStoriesCreation(): array {
    $stories = $_POST['stories'] ?? [];
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    // Debug : vérifier la structure des données reçues
    error_log("=== DEBUG POST DATA ===");
    error_log("Full POST: " . print_r($_POST, true));
    error_log("Stories array: " . print_r($stories, true));
    error_log("Stories count: " . count($stories));
    
    if (empty($stories)) {
        return ['success' => false, 'message' => 'Aucune Story à traiter'];
    }
    
    $StoryManager = new StoryManager();
    
    foreach ($stories as $index => $story) {
        // Vérifier que $story est bien un tableau
        if (!is_array($story)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : données invalides";
            continue;
        }
        
        // Debug détaillé pour chaque Story
        error_log("=== PROCESSING STORY #" . ($index + 1) . " ===");
        error_log("Story raw data: " . print_r($story, true));
        
        // Debug spécifique pour les IDs (noms corrects selon le Twig)
        error_log("character: '" . ($story['character'] ?? 'NOT SET') . "'");
        error_log("item: '" . ($story['item'] ?? 'NOT SET') . "'");
        error_log("location: '" . ($story['location'] ?? 'NOT SET') . "'");
        error_log("Story_content: '" . ($story['Story_content'] ?? 'NOT SET') . "'");
        
        // Vérifier la présence des IDs depuis la combinaison (noms corrects)
        $characterId = !empty($story['character']) ? (int)$story['character'] : null;
        $itemId = !empty($story['item']) ? (int)$story['item'] : null;
        $locationId = !empty($story['location']) ? (int)$story['location'] : null;
        
        error_log("After processing - character: $characterId, item: $itemId, location: $locationId");
        
        // Vérifier que les champs obligatoires sont remplis
        $content = trim($story['Story_content'] ?? '');
        if (empty($content)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : contenu requis";
            continue;
        }
        
        // Vérifier que les IDs obligatoires sont présents
        if ($locationId === null || $locationId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : location requis (ID manquant dans la combinaison)";
            continue;
        }
        
        if ($characterId === null || $characterId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : character requis (ID manquant dans la combinaison)";
            continue;
        }
        
        if ($itemId === null || $itemId === 0) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : item requis (ID manquant dans la combinaison)";
            continue;
        }
        if (empty($content)) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : contenu requis";
            continue;
        }
        
        try {
            $storyData = [
                'Story_titre' => !empty($story['Story_titre']) ? trim($story['Story_titre']) : "Story #" . ($index + 1),
                'Story_categorie' => !empty($story['Story_categorie']) ? $story['Story_categorie'] : null,
                'character' => $characterId,
                'item' => $itemId,
                'location' => $locationId,
                'Story_content' => $content,
                'audio' => !empty($story['audio']) ? $story['audio'] : null,
                'url' => !empty($story['url']) ? $story['url'] : null
            ];
            
            // Debug : vérifier les données avant insertion
            error_log("Story data to insert: " . print_r($storyData, true));
            
            $StoryManager->addStory($storyData);
            $successCount++;
            
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "Story #" . ($index + 1) . " : " . $e->getMessage();
            error_log("Error creating story #" . ($index + 1) . ": " . $e->getMessage());
        }
    }
    
    // Construction du message de retour
    $message = "";
    if ($successCount > 0) {
        $message = "{$successCount} Story(s) créée(s) avec succès.";
    }
    
    if ($errorCount > 0) {
        if (!empty($message)) {
            $message .= " ";
        }
        $message .= "{$errorCount} erreur(s) : " . implode(', ', $errors);
    }
    
    // Si aucune Story n'a été créée et qu'il n'y a pas d'erreurs spécifiques
    if ($successCount === 0 && $errorCount === 0) {
        $message = "Aucune Story n'a pu être traitée";
    }
    
    return [
        'success' => $successCount > 0, 
        'message' => $message,
        'successCount' => $successCount,
        'errorCount' => $errorCount
    ];
}


/**
 * Méthode alternative pour récupérer uniquement les données sans affichage
 * Utile pour les API ou tests
 * @return array Données du dashboard
 */
public function getDashboardData(): array {
    $itemManager = new itemManager();
    $locationManager = new locationManager();
    $characterManager = new characterManager();
    $StoryManager = new StoryManager();
    $StoryCategorieManager = new StoryCategorieManager();
    
    return [
        'items' => $itemManager->getAllitems(),
        'locationx' => $locationManager->getAlllocationx(),
        'characters' => $characterManager->getAllcharacters(),
        'categories' => $StoryCategorieManager->getAllCategories(),
        'Storys' => $StoryManager->getAllStories(),
        'stats' => $this->getCurrentStats()
    ];
}

/**
 * Génère un aperçu des combinaisons manquantes (limité)
 * @param int $limit Nombre maximum de combinaisons à afficher
 * @return array Liste limitée des combinaisons manquantes
 */
public function previewMissingCombinations(int $limit = 10): array {
    $missingCombinations = $this->getMissingCombinations();
    return array_slice($missingCombinations, 0, $limit);
}

}
