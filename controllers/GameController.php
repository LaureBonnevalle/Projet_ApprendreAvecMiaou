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
    
    
    
    
    
}