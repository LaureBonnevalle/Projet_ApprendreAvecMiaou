<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendEmail {

    private function getMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        try {
            // Config SMTP depuis les variables globales $_ENV
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = $_ENV['SMTP_SECURE']; // 'ssl' ou 'tls'
            $mail->Port       = $_ENV['SMTP_PORT'];

            // Expéditeur
            $mail->setFrom($_ENV['SMTP_USER'], $_ENV['MAIL8FROM8NAME']);

            // Format HTML
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Erreur configuration PHPMailer: " . $e->getMessage());
        }

        return $mail;
    }

    public function sendEmailConfirme(string $firstname, string $email, string $passwordView): void {
        $mail = $this->getMailer();
        $mail->addAddress($email);
        $mail->AddEmbeddedImage('../public/assets/img/Miaou/EnteteEmailGrand.jpg', 'logo_miaou');
        $message = '<img src="cid:logo_miaou" alt="logo du site">';

        $mail->Subject = "Confirmation d'inscription sur le site ApprendreAvecMiaou";

        $message = '
        <html>
            <body style="width: 550px">
            
            </style>
            <div class="header">
                <img src="cid:logo_miaou" style="width: 550px"/>
            </div>
                <div >
                    <p style="margin-top: 10px; text-align: center">Bonjour,</p>
                    <p style="text-align: justify">Votre demande d\'accès au site pour <strong>' . htmlspecialchars($firstname) . '</strong> a bien été enregistrée. Votre mot de passe temporaire est : <strong>' . htmlspecialchars($passwordView) . '</strong></p>
                    <p>Votre email de connexion: <strong>' . htmlspecialchars($email) . '</strong></p>
                    <p style="color: #FF33FF; font-size: 18px;">Votre compte n\'est pas encore activé. Vous devez vous connecter et modifier votre mot de passe pour activer votre compte.</p>
                    <p>Respectez bien les majuscules et les minuscules.<br />
                    </p>
                    <a href:"http://
                </div>
            <div class=footer style="background-color:#f1f1f1; padding:15px; text-align:center; font-size:14px; color:#555;">
                <p>👉 Accédez à votre compte ici : <a href="https://apprendreavecmiaou.alwaysdata.net/index.php?route=login" 
                style="color:#FF33FF; text-decoration:underline">
                Se connecter
                </a>
                </p>
                <p>&copy; ' . date("Y") . ' Apprendre avec Miaou</p>
            </div>
            </body>
        </html>';

        $mail->Body = $message;

        try {
            $mail->send();
            error_log("Email de confirmation envoyé à : " . $email);
        } catch (Exception $e) {
            error_log("Erreur envoi email confirmation: " . $mail->ErrorInfo);
        }

        file_put_contents('../documents/email1.html', $message);
    }

    public function sendEmailResponse(string $email, string $subjectReceived, string $firstname, string $contentReceived, string $receptedDate, string $response): void {
        $mail = $this->getMailer();
        $mail->addAddress($email);
        $mail->AddEmbeddedImage('../public/assets/img/Miaou/EnteteEmailGrand.jpg', 'logo_miaou');
        $message = '<img src="cid:logo_miaou" alt="logo du site">';

        $mail->Subject = "Re: " . $subjectReceived;

        $message = '
        <html>
        <body style="width: 550px">
            
            </style>
            <div class="header">
                <img src="cid:logo_miaou" style="width: 550px"/>
            </div>
            <div >
                <p style="margin-top: 10px; text-align: center">Bonjour,</p>
                <p>Votre message: ' . htmlspecialchars($contentReceived) . ' du ' . htmlspecialchars($receptedDate) . '</p>
                <p>La réponse de ApprendreAvecMiaou : ' . htmlspecialchars($response) . '</p>
            </div>
            <div class=footer style="background-color:#f1f1f1; padding:15px; text-align:center; font-size:14px; color:#555;">
                <p>👉 Répondre à cet email : <a href="https://apprendreavecmiaou.alwaysdata.net/index.php?route=contact" 
                style="color:#FF33FF; text-decoration:underline">
                Se connecter
                </a>
                </p>
                <p>&copy; ' . date("Y") . ' Apprendre avec Miaou</p>
            </div>
        </body>
        </html>';

        $mail->Body = $message;

        try {
            $mail->send();
            error_log("Email de réponse envoyé à : " . $email);
        } catch (Exception $e) {
            error_log("Erreur envoi email réponse: " . $mail->ErrorInfo);
        }

        file_put_contents('../documents/email2.html', $message);
    }

    public function sendPasswordResetEmail(string $firstname, string $email, string $newPassword): void {
        $mail = $this->getMailer();
        $mail->addAddress($email);
        $mail->AddEmbeddedImage('../public/assets/img/Miaou/EnteteEmailGrand.jpg', 'logo_miaou');
        $message = '<img src="cid:logo_miaou" alt="logo du site">';

        $mail->Subject = "Réinitialisation de votre mot de passe - ApprendreAvecMiaou";

        $message = '
        <html>
        <body style="width: 550px">
            <div class="header">
                <img src="cid:logo_miaou" style="width: 550px"/>
            </div>
            <div >
                <p style="margin-top: 10px; text-align: center">Bonjour ' . htmlspecialchars($firstname) . '</p>
                <p style="text-align: justify"><strong>🔒 Votre mot de passe a été réinitialisé</strong></p>
                    <p>Votre nouveau mot de passe temporaire est: <strong>' . htmlspecialchars($newPassword) . '</strong>
                    </p>
                    <p>Votre email de connexion:<strong> ' . htmlspecialchars($email) . '</strong></p>
                </div>
                <p style="color: #FF33FF; font-size: 18px;">Votre compte est désactivé. Vous devez vous connecter et modifier votre mot de passe pour réactiver votre compte.</p>
                    <p>Respectez bien les majuscules et les minuscules.<br />
                    </p>⚠️ IMPORTANT - SÉCURITÉ: Si vous n\'avez pas demandé cette réinitialisation, 
                    <strong style="color: #dc3545;">contactez immédiatement l\'administrateur</strong>.
                </p>
                <div class=footer style="background-color:#f1f1f1; padding:15px; text-align:center; font-size:14px; color:#555;">
                <p>👉 Contacter l\'administrateur : <a href="mailto:apprendreavecmiaou@gmail.com"  style="color: #FF33FF; text-decoration:underline";">Contacter l\'admin</a></p>
                <p>&copy; ' . date("Y") . ' Apprendre avec Miaou</p>
            </div>
        </body>
        </html>';

        $mail->Body = $message;

        try {
            $mail->send();
            error_log("Email de réinitialisation envoyé à : " . $email);
        } catch (Exception $e) {
            error_log("Erreur envoi email reset: " . $mail->ErrorInfo);
        }

        file_put_contents('../documents/email3.html', $message);
        file_put_contents('../documents/email_reset_' . date('Y-m-d_H-i-s') . '.html', $message);
    }

    /**
     * Envoie la newsletter HTML avec l'en-tête arc-en-ciel
     * 
     * @param string $to Email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $htmlContent Contenu HTML de la newsletter
     * @return bool true si envoi réussi, false sinon
     */
    public function sendNewsletter(string $to, string $subject, string $htmlContent) : bool
    {
        $mail = $this->getMailer();
        
        try {
            // Ajouter le destinataire
            $mail->addAddress($to);
            
            // Intégrer l'image d'en-tête arc-en-ciel "La Newsletter de Miaou"
            // Utilisez le chemin vers votre image d'en-tête arc-en-ciel
            $mail->AddEmbeddedImage('../public/assets/img/Miaou/EnetetEmailNewsletter.jpg', 'newsletter_header');
            
            // Note: Si votre image s'appelle différemment, changez le nom du fichier ici
            // Par exemple: EnteteNewsletterMiaou.jpg, header_rainbow.jpg, etc.
            
            // Sujet
            $mail->Subject = $subject;
            
            // Contenu HTML (déjà généré par generateNewsletterHTML)
            $mail->Body = $htmlContent;
            
            // Version texte alternative (sans HTML)
            $mail->AltBody = strip_tags($htmlContent);
            
            // Envoi
            $mail->send();
            error_log("Newsletter envoyée à : " . $to);
            
            // Optionnel : sauvegarder une copie de la newsletter envoyée
            $filename = '../documents/newsletter_' . date('Y-m-d_H-i-s') . '_' . md5($to) . '.html';
            file_put_contents($filename, $htmlContent);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur envoi newsletter à $to : {$mail->ErrorInfo}");
            return false;
        }
    }
}
