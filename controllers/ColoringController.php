<?php

class ColoringController extends AbstractController
{
    public function displayDraw()
    {
        $avatarManager = new AvatarManager();
        $timesModels = new TimesModels();
        $categoriesManager = new ColoringCategoriesManager();
        $func= new Utils();
        $categories = $categoriesManager->getAll();
        
        $avatar = $avatarManager->getById($_SESSION['user']['avatar']);
        $elapsedTime = $timesModels->getElapsedTime();

        $scripts = $this->addScripts(['assets/js/ajaxColoring.js']);
        
        $func= new Utils();
        $func->clearSessionMessages();
        unset($_SESSION['error'], $_SESSION['success_message']);  
        

        return $this->render('coloring.html.twig', [
            'titre' => 'Coloriages',
            'user' => $_SESSION['user'] ?? null,
            'avatar' => $avatar,
            'categories' => $categories,
            'elapsed_time' => $elapsedTime,
            'session' => $_SESSION,
            'connected' => true,
            'isUser' => true,
            'start_time' => $_SESSION['start_time']
        ], $scripts);
    }

    public function getColoringsByCategorieJson()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        $content = file_get_contents("php://input");
        $data = json_decode($content, true);

        if (!isset($data['id']) || empty($data['id'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing category ID']);
            exit;
        }

        $categorieId = (int)$data['id'];

        try {
            $coloringManager = new ColoringManager();
            $colorings = $coloringManager->getAllByCategorie($categorieId);

            header('Content-Type: application/json');
            echo json_encode($colorings);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }


/********************************GESTION DES Coloriages ADMIN******************/
    
    public function modifColorings(): void 
{
    
        // Récupération des catégories
        $ccm=new  ColoringCategoriesManager();
        $categories = $ccm->getAllCategoriesColorings();
      

        // Récupération des Colorings groupés par catégorie
        $ColoringsParCategorie = [];
         $cm= new ColoringManager();
        foreach ($categories as $categorie) {
            $ColoringsParCategorie[$categorie['id']] = $cm->getAllColoringsByCategorie($categorie['id']);
        }
        $scripts = $this->addScripts(['public/assets/js/common.js','public/assets/js/formController.js','public/assets/js/formFunction.js', 'public/assets/js/ColoringAdmin.js']);
        // Affichage de la vue
        $this->render('ColoringsAdmin.html.twig', [
            'categories' => $categories,
            'ColoringsParCategorie' => $ColoringsParCategorie
        ], $scripts);
        
    
}
    
   public function addColoring(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $url = $_POST['url'] ?? '';
        $categorieId = (int) ($_POST['categorie_id'] ?? 0);

        if ($name && $url && $categorieId) {
            $cm = new ColoringManager();
            
            if (!$cm->existByUrl($url)) {
                $cm->addColoring([
                    'name' => $name,
                    'description' => $description,
                    'url' => $url,
                    'categorie_Coloring' => $categorieId
                ]);
            } else {
                // Tu pourrais afficher un message d'erreur ici
                // Ou même stocker une notification de type "URL déjà utilisée"
            }
        }

        header('Location: index.php?route=modifColoring');
        exit;
    }
}
    
    
   
    
    /**
     * Gère les catégories
     */
    public function gererCategories(): void 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleCategorieSubmission();
                return;
            }
            
            $categories = $this->ColoringManager->findAllCategories();
            $stats = $this->ColoringManager->getColoringsStats();
            
            $this->render('categoriesAdmin', [
                'categories' => $categories,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            $this->handleError("Erreur lors de la gestion des catégories : " . $e->getMessage());
        }
    }
    
    /**
     * Supprime une catégorie
     */
   public function deleteColoring(): void
{
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int) $_GET['id'];
        $cm = new ColoringManager();
        $cm->deleteColoringById($id);
    }

    header('Location: index.php?route=modifColoring');
    exit;
}
    
    /**
     * Gère la soumission du formulaire de Coloring
     */
    private function handleColoringSubmission(?int $id = null): void 
    {
        // Validation des données
        $errors = $this->validateColoringData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        // Préparation des données
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'categorie_Coloring' => (int)$_POST['categorie_Coloring'],
            'url' => ''
        ];
        
        // Gestion de l'upload de fichier
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['image']);
            
            if ($uploadResult['success']) {
                $data['url'] = $uploadResult['url'];
            } else {
                $this->setFlashMessage('error', $uploadResult['message']);
                return;
            }
        } elseif ($id === null) {
            // Pour un nouveau Coloring, l'image est obligatoire
            $this->setFlashMessage('error', 'L\'image est obligatoire');
            return;
        } else {
            // Pour une modification, garder l'ancienne URL si pas de nouvelle image
            $existingColoring = $this->ColoringManager->findColoringById($id);
            $data['url'] = $existingColoring['url'];
        }
        
        try {
            if ($id === null) {
                // Création
                $this->ColoringManager->createColoring($data);
                $this->redirect('modifColoring', 'Coloring ajouté avec succès', 'success');
            } else {
                // Modification
                $this->ColoringManager->updateColoring($id, $data);
                $this->redirect('modifColoring', 'Coloring modifié avec succès', 'success');
            }
        } catch (Exception $e) {
            $this->handleError("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }
    
    /**
     * Gère la soumission du formulaire de catégorie
     */
    private function handleCategorieSubmission(): void 
    {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'ajouter':
                $this->handleAjouterCategorie();
                break;
            case 'modifier':
                $this->handleModifierCategorie();
                break;
            default:
                $this->redirect('gererCategories', 'Action non reconnue');
        }
    }
    
    /**
     * Ajoute une nouvelle catégorie
     */
    private function handleAjouterCategorie(): void 
    {
        $errors = $this->validateCategorieData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        $data = [
            'categorie_name' => trim($_POST['categorie_name']),
            'categorie_description' => trim($_POST['categorie_description'])
        ];
        
        try {
            $this->ColoringManager->createCategorie($data);
            $this->redirect('gererCategories', 'Catégorie ajoutée avec succès', 'success');
        } catch (Exception $e) {
            $this->handleError("Erreur lors de l'ajout de la catégorie : " . $e->getMessage());
        }
    }
    
    /**
     * Modifie une catégorie existante
     */
    private function handleModifierCategorie(): void 
    {
        $id = (int)$_POST['id'];
        $errors = $this->validateCategorieData();
        
        if (!empty($errors)) {
            $this->setFlashMessage('error', implode('<br>', $errors));
            return;
        }
        
        $data = [
            'categorie_name' => trim($_POST['categorie_name']),
            'categorie_description' => trim($_POST['categorie_description'])
        ];
        
        try {
            $this->ColoringManager->updateCategorie($id, $data);
            $this->redirect('gererCategories', 'Catégorie modifiée avec succès', 'success');
        } catch (Exception $e) {
            $this->handleError("Erreur lors de la modification de la catégorie : " . $e->getMessage());
        }
    }
    
    /**
     * Valide les données d'un Coloring
     */
    private function validateColoringData(): array 
    {
        $errors = [];
        
        if (empty(trim($_POST['name']))) {
            $errors[] = "Le nom est obligatoire";
        }
        
        if (empty(trim($_POST['description']))) {
            $errors[] = "La description est obligatoire";
        }
        
        if (empty($_POST['categorie_Coloring']) || !is_numeric($_POST['categorie_Coloring'])) {
            $errors[] = "La catégorie est obligatoire";
        }
        
        return $errors;
    }
    
    /**
     * Valide les données d'une catégorie
     */
    private function validateCategorieData(): array 
    {
        $errors = [];
        
        if (empty(trim($_POST['categorie_name']))) {
            $errors[] = "Le nom de la catégorie est obligatoire";
        }
        
        return $errors;
    }
    
    /**
     * Gère l'upload d'un fichier image
     */
    private function handleFileUpload(array $file): array 
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Vérifications
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)'];
        }
        
        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('Coloring_') . '.' . $extension;
        $uploadDir = 'uploads/Colorings/';
        
        // Création du répertoire si inexistant
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Upload du fichier
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'url' => '/' . $uploadPath];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
        }
    }
}    
