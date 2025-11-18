<?php

/*require_once('managers/ColoringeManager.php');
require_once('managers/ColoringCategoriesManager.php');*/


class ColoringController extends AbstractController
{
    public function displayDraw()
    {
        $avatarManager = new AvatarManager();
        $timesModels = new TimesModels();
        $cm = new ColoringCategoriesManager();
        $categories= $cm->getAllCategoriesColorings();
        //var_dump($categories);
        $scripts = $this->addScripts(['public/assets/js/ajaxColoring.js']);
        
        //var_dump($categories);
        //var_dump($_SESSION);

        $avatar = $avatarManager->getById($_SESSION['user']['avatar']);
        $errorMessage = $_SESSION['error_message'] ?? null;
        $elapsedTime = $timesModels->getElapsedTime();
        
        //var_dump($categories, $avatar);
        
        $colorings = [];
    if (isset($_GET['categorie_id'])) {
        $cm = new ColoringManager();
        $colorings = $cm->getAllColoringsByCategorie((int)$_GET['categorie_id']);
        
        
        /*foreach ($colorings as &$coloring) {
            $thumbnailPath = 'public/assets/img/coloringSheets/' . $coloring['id'] . '.jpg';
            if (!file_exists($thumbnailPath)) {
                createThumbnailFromPDF($coloring['url'], $thumbnailPath);
            }
            $coloring['thumbnail_url'] = $thumbnailPath;
        }*/
    }

    return $this->render('coloring.html.twig', [
        'user' => $_SESSION['user'] ?? null,
        'avatar' => $avatar,
        'categories' => $categories,
        'error_message' => $errorMessage,
        'elapsed_time' => $elapsedTime,
        'colorings' => $colorings,
    ], $scripts);
        
        
    }
    
    public function getColoringsByCategorieJson(){
        
        ob_clean(); // Clean the output buffer

        $content = file_get_contents("php://input");
        $data = json_decode($content, true);
        
        $coloringManager = new ColoringManager();
        $colorings = $coloringManager->getAllColoringsByCategorie($data['id']);        
    
        header('Content-Type: application/json');
        echo json_encode($colorings);
        
        exit;
    }
   
    
    function createThumbnailFromPDF($pdfFilePath, $thumbnailPath) {
        
        $pdfFilePath = "public/assets/img/coloringSheets/";
        $thumbnailPath = "public/assets/img/coloringSheets/thumbnails/";
    $imagick = new \Imagick();
    $imagick->setResolution(150, 150);
    $imagick->readImage($pdfFilePath . '[0]');
    $imagick->setImageFormat('jpg');
    $imagick->writeImage($thumbnailPath);
    $imagick->clear();
    $imagick->destroy();
    }
    
    /*public function downloadFile(Request $request): Response
    {
        $fileId = $cm->getId($id);
        // Logique pour récupérer le chemin du fichier basé sur l'ID
        $filePath = '/path/to/files/' . $fileId . '.pdf';

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filePath)
        );

        return $response;
    }*/
}