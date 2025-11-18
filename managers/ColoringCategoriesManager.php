<?php

//require_once('models/ColoriageCategories.php');

class ColoriageCategoriesManager extends AbstractManager {

    public function getAllCategoriesColoriages(): array {
        
        $query = $this->db->prepare("SELECT * FROM coloring_categories");
        $query->execute([]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return $results;
        
        
        
    }

    
    function createThumbnailFromPDF($pdfFilePath, $thumbnailPath) {
    $imagick = new \Imagick();
    $imagick->setResolution(150, 150);
    $imagick->readImage($pdfFilePath . '[0]');
    $imagick->setImageFormat('jpg');
    $imagick->writeImage($thumbnailPath);
    $imagick->clear();
    $imagick->destroy();
        }

// Utilisation
       // $pdfFilePath = 'path/to/your/pdf/file.pdf';
       // $thumbnailPath = 'path/to/save/thumbnail.jpg';
       // createThumbnailFromPDF($pdfFilePath, $thumbnailPath);
}