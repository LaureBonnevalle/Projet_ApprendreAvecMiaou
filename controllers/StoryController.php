<?php
/*require_once("managers/StoryManager.php");
require_once("managers/CharacterManager.php");
require_once("managers/ItemManager.php");
require_once("managers/LocationManager.php");
require_once("managers/AvatarManager.php");
require_once("models/TimesModels.php");*/

class StoryController extends AbstractController {

    public function getImageUrl() {
        $characterId = $_POST['character_id'];
        $locationId = $_POST['location_id'];
        $itemId = $_POST['item_id'];

        $storyManager = new storyManager();
        $imageUrl = $storyManager->getImageUrl($characterId, $locationId, $itemId);

        header('Content-Type: application/json');
        echo json_encode(['url' => $imageUrl]);
        exit;
    }
    
    public function displayStories() {
        $pm = new characterManager();
        $lm = new locationManager();
        $om = new itemManager();
        $am = new AvatarManager();
        $avatar = $am->getById($_SESSION['user']['avatar']);
        $timesModels = new TimesModels();
        $elapsedTime = $timesModels->getElapsedTime();
        
        
        $scripts = $this->addScripts(['public/assets/js/ajaxStorie.js', 'public/assets/js/common.js']);

        $characters = $pm->getAllcharacters();
        $locationx = $lm->getAlllocationx();
        $items = $om->getAllitems();

        return $this->render('story.html.twig', [
            
            'user' => $_SESSION['user'] ?? null,
            'elapsed_time' => $elapsedTime,
            'characters' => $characters,
            'locationx' => $locationx,
            'items' => $items,
            'avatar' => $avatar,
        ], $scripts);
    }

    
    
    public function getImage() {
        $entity = $_GET['entity'];
        $id = $_GET['id'];
        //var_dump($_GET);
        //die;
        
        $manager = null;
        
        switch($entity) {
            case 'character':
                $manager = new characterManager();
                break;
            case 'item':
                $manager = new itemManager();
                break;
            case 'location':
                $manager = new locationManager();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid entity']);
                exit;
                break;
        }

        $data = $manager->getById($id);
        //file_put_contents('text1.txt', $data['url']);
        //file_put_contents('text2.txt', $data['alt']);
        header('Content-Type: application/json');
        echo json_encode($data['url']);
        //echo json_encode($data['alt']);
        exit;
        
    }
    
    public function getStory() {
        $characterId = $_GET['perso'];
        $itemId = $_GET['item'];
        $locationId  = $_GET['location'];
        
        
        $storyManager = new storyManager();
        $story = $storyManager->getStoryByCriteria($characterId, $itemId, $locationId);
                
        header('Content-Type: application/json');
        
        echo json_encode($story);
        exit;
    }
}
