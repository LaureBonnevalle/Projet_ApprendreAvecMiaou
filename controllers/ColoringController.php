<?php

class ColoringController extends AbstractController
{
    public function displayDraw()
    {
        $avatarManager = new AvatarManager();
        $timesModels = new TimesModels();
        $categoriesManager = new ColoringCategoriesManager();

        $categories = $categoriesManager->getAll();
        
        $avatar = $avatarManager->getById($_SESSION['user']['avatar']);
        $elapsedTime = $timesModels->getElapsedTime();

        $scripts = $this->addScripts(['assets/js/ajaxColoring.js']);

        $this->clearSessionMessages();
        unset($_SESSION['error'], $_SESSION['success_message']);

        return $this->render('coloring.html.twig', [
            'titre' => 'Coloriages',
            'user' => $_SESSION['user'] ?? null,
            'avatar' => [$avatar],
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

    private function clearSessionMessages(): void
    {
        $messageKeys = ['messages', 'error', 'success', 'warning', 'info', 'flash', 'error_message', 'success_message'];
        foreach ($messageKeys as $key) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }
    }
}
