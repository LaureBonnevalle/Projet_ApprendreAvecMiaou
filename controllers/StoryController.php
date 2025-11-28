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
        
        
    $scripts = $this->addScripts(['assets/js/ajaxStory.js']);

        $characters = $pm->getAllCharacters();
        $locations = $lm->getAllLocations();
        $items = $om->getAllItems();
        
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
            'avatar' => [$avatar],
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
}
