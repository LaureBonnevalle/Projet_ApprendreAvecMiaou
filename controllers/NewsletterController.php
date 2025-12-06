<?php

class NewsletterController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Affichage et traitement du formulaire d'abonnement à la newsletter
     */
    public function newsletterSubscribe() : void
    {
        // Scripts JS nécessaires (validation côté client)
        $scripts = $this->addScripts([
            'assets/js/formController.js',
        ]);

        $tm = new CSRFTokenManager();
        $token = $tm->generateCSRFToken();

        // Initialisation des messages
        $_SESSION['error_message'] = "";
        $_SESSION['success_message'] = "";

        // Si formulaire soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $func = new Utils();

            // Vérifier les clés POST
            if (!$func->checkPostKeys(['firstname', 'email', 'csrf_token'])) {
                $_SESSION['error_message'] = "Les champs requis n'existent pas.";
                $this->redirectTo('newsletterSubscribe');
                return;
            }

            $data = [
                'firstname'  => $func->e(trim($_POST['firstname'])),
                'email'      => $func->e(strtolower(trim($_POST['email']))),
                'csrf_token' => $_POST['csrf_token'],
            ];

            // Vérifier que tous les champs sont remplis
            if (empty($data['firstname']) || empty($data['email']) || empty($data['csrf_token'])) {
                $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
                $this->redirectTo('newsletterSubscribe');
                return;
            }

            // Vérifier le token CSRF
            if (!$tm->validateCSRFToken($data['csrf_token']) || $_SESSION['csrf_token'] != $data['csrf_token']) {
                $_SESSION['error_message'] = "Token CSRF invalide.";
                $this->redirectTo('newsletterSubscribe');
                return;
            }

            // Vérifier format email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = "Adresse email invalide.";
                $this->redirectTo('newsletterSubscribe');
                return;
            }

            // Enregistrer l'abonnement (exemple avec NewsletterManager)
            $nm = new NewsletterManager();
            $result = $nm->subscribe($data['firstname'], $data['email']);

            if ($result) {
                $_SESSION['success_message'] = "Merci {$data['firstname']} ! Vous êtes abonné à la newsletter.";
            } else {
                $_SESSION['error_message'] = "Cet email est déjà abonné ou une erreur est survenue.";
            }

            $this->redirectTo('homepage');
            return;
        }

        // Affichage du formulaire
        $am= new AvatarManager();
        $avatar = $am->getById(4); 

        $this->render("newsletterSubscription.html.twig", [
            'elapsed_time' => 0, // reset 
            'session' => $_SESSION,
            'start_time' => $_SESSION['start_time'],
            'success_message' => $_SESSION["login_data"]['success_message'] ?? null, 
            "avatar" => $avatar,
            'csrf_token' => $token,
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null
        ], $scripts);
    }

     /**
     * Affiche le formulaire d'envoi de newsletter (admin uniquement)
     */
    public function sendNewsletterForm() : void
    {
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
            $_SESSION['error_message'] = "Accès refusé.";
            $this->redirectTo('homepage');
            return;
        }

        $tm = new CSRFTokenManager();
        $token = $tm->generateCSRFToken();

        // Compter le nombre d'abonnés
        $nm = new NewsletterManager();
        $subscribers = $nm->getAllSubscribers();
        $subscriberCount = count($subscribers);

        $this->render("admin/newsletterSend.html.twig", [
            'session' => $_SESSION,
            'csrf_token' => $token,
            'subscriber_count' => $subscriberCount,
            'success_message' => $_SESSION['success_message'] ?? null,
            'error_message' => $_SESSION['error_message'] ?? null
        ]);
    }

    /**
     * Traite l'envoi de la newsletter
     */
    public function processNewsletterSend() : void
    {
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
            $_SESSION['error_message'] = "Accès refusé.";
            $this->redirectTo('homepage');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('sendNewsletterForm');
            return;
        }

        $func = new Utils();
        $tm = new CSRFTokenManager();

        // Vérifier les clés POST
        if (!$func->checkPostKeys(['subject', 'article1_title', 'article1_content', 'article2_title', 'article2_content', 'csrf_token'])) {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
            $this->redirectTo('sendNewsletterForm');
            return;
        }

        // Récupérer et nettoyer les données
        $data = [
            'subject' => $func->e(trim($_POST['subject'])),
            'article1_title' => $func->e(trim($_POST['article1_title'])),
            'article1_content' => trim($_POST['article1_content']), // Garder le HTML
            'article2_title' => $func->e(trim($_POST['article2_title'])),
            'article2_content' => trim($_POST['article2_content']), // Garder le HTML
            'csrf_token' => $_POST['csrf_token'],
            'year' => date('Y') // Ajout de l'année pour le footer
        ];

        // Vérifier le token CSRF
        if (!$tm->validateCSRFToken($data['csrf_token'])) {
            $_SESSION['error_message'] = "Token CSRF invalide.";
            $this->redirectTo('sendNewsletterForm');
            return;
        }

        // Vérifier que les champs ne sont pas vides
        foreach ($data as $key => $value) {
            if ($key !== 'csrf_token' && empty($value)) {
                $_SESSION['error_message'] = "Tous les champs doivent être remplis.";
                $this->redirectTo('sendNewsletterForm');
                return;
            }
        }

        // Gérer l'upload de l'image d'en-tête (optionnel)
        $headerImageUrl = null;
        if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
            $headerImageUrl = $this->handleImageUpload($_FILES['header_image']);
        }

        // Récupérer tous les abonnés
        $nm = new NewsletterManager();
        $subscribers = $nm->getAllSubscribersWithInfo();

        if (empty($subscribers)) {
            $_SESSION['error_message'] = "Aucun abonné trouvé.";
            $this->redirectTo('sendNewsletterForm');
            return;
        }

        // Générer le HTML de la newsletter
        $htmlContent = $this->generateNewsletterHTML($data, $headerImageUrl);

        // Envoyer la newsletter à tous les abonnés
        $sendEmail = new SendEmail();
        $successCount = 0;
        $errorCount = 0;

        foreach ($subscribers as $subscriber) {
            // Personnaliser le contenu avec le prénom
            $personalizedHTML = str_replace('{{firstname}}', $subscriber['firstname'] ?? 'Abonné', $htmlContent);
            
            $result = $sendEmail->sendNewsletter(
                $subscriber['email'],
                $data['subject'],
                $personalizedHTML
            );

            if ($result) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // Message de résultat
        if ($successCount > 0) {
            $_SESSION['success_message'] = "Newsletter envoyée avec succès à $successCount abonné(s).";
            if ($errorCount > 0) {
                $_SESSION['success_message'] .= " $errorCount échec(s).";
            }
        } else {
            $_SESSION['error_message'] = "Échec de l'envoi de la newsletter.";
        }

        $this->redirectTo('sendNewsletterForm');
    }

    /**
     * Génère le HTML de la newsletter avec le logo arc-en-ciel Miaou
     */
    private function generateNewsletterHTML(array $data, ?string $headerImageUrl) : string
    {
        // L'image d'en-tête principale est toujours le logo arc-en-ciel
        // L'image custom uploadée (si présente) sera affichée après dans le contenu
        $customImageSection = '';
        if ($headerImageUrl) {
            $customImageSection = '
            <div style="padding: 0 30px;">
                <img src="' . $headerImageUrl . '" alt="Image newsletter" style="width: 100%; height: auto; display: block; border-radius: 8px; margin-bottom: 20px;">
            </div>';
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$data['subject']}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        
        <!-- En-tête arc-en-ciel avec logo et titre -->
        <div class="header">
            <img src="cid:newsletter_header" alt="La Newsletter de Miaou" style="width: 100%; display: block;">
        </div>
        
        <!-- Image custom optionnelle -->
        {$customImageSection}
        
        <!-- Contenu principal -->
        <section style="padding: 30px;" id="newsletter-content">
            <p style="font-size: 18px; margin-bottom: 20px; color: #555; text-align: center;">
                Bonjour {{firstname}},
            </p>
            
            <!-- Article 1 -->
            <article style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #eee;">
                <h2 style="color: #FF33FF; font-size: 24px; margin-bottom: 15px;">
                    {$data['article1_title']}
                </h2>
                <div style="color: #555; text-align: justify;">
                    {$data['article1_content']}
                </div>
            </article>
            
            <!-- Article 2 -->
            <article style="margin-bottom: 30px;">
                <h2 style="color: #FF33FF; font-size: 24px; margin-bottom: 15px;">
                    {$data['article2_title']}
                </h2>
                <div style="color: #555; text-align: justify;">
                    {$data['article2_content']}
                </div>
            </article>
        </section>
        
        <!-- Footer -->
        <div class="footer" style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 14px; color: #555;">
            <p>Vous recevez cet email car vous êtes abonné à notre newsletter.</p>
            <p>👉 <a href="https://apprendreavecmiaou.alwaysdata.net/index.php?route=newsletterUnsubscribe" 
                style="color: #FF33FF; text-decoration: underline;">
                Se désabonner
            </a></p>
            <p>&copy; {$data['year']} Apprendre avec Miaou</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Gère l'upload de l'image d'en-tête
     */
    private function handleImageUpload(array $file) : ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = 'assets/img/newsletter/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'header_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'https://votre-site.com/' . $filepath;
        }

        return null;
    }
}
