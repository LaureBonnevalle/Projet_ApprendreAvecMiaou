<?php


class SendEmail {

    public function sendEmailConfirme(string $firstname, string $email, string $passwordView) :void {

        $expeditorEmail = "apprendreavecmiaou@alwaysdata.net";
        $destinataire = $email;

        $header= "MIME-Version: 1.0\r\n";
        $header.= 'From:"ApprendreAvecMiaou"<'.$expeditorEmail.'>'."\n";
        //$header.= "Cc: .......@hotmail.fr\n";
        $header.= "X-Priority: 1\n";
        $header.= 'Content-Type: text/html; charset="uft-8"'."\n";
        $header.= 'Content-Transfer-Encoding: 8bit';

        $message =
'<html>
    <body>
        <div style="font-size: 24px; text-align: center">
            <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>

            <h1>Site de jeux et activités pour enfants</h1>

            <p>Bonjour, <span style="text-transform:uppercase">' . $email . ' responsable de ' . $firstname . '</span>

            <p>Votre demande d\'accès au site a bien été enregistrée.</p>
            <p>Votre mot de passe temporaire est : <strong>' . $passwordView . '</strong></p>
            <p>Email : <strong>' . $email . '</strong></p>
            <p style="color: rgb(255, 0, 0); font-size: 24px;">Nous vous conseillons de le changer rapidement !</p>
    
            <p>Respectez bien les majuscules et les minuscules.<br />
            Votre compte n\'est pas encore activé. Vous devez vous connectez et modifier votre mot de passe.</p>

            <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
        </div>
    </body>
</html>';

        /*
        $message .= "Content-Disposition: attachment; filename=\"Cars.png\"\n\n";
        $message .= $content_encode . "\n";
        $message .= "\n\n";	*/

        file_put_contents('../documents/email1.html', $message);

        //mail($destinataire, "CONFIRMATION DE RECEPTION DE DEMANDE D'INSCRIPTION SUR ApprendreAvecMiaou.", $message, $header);
    }
        
        /*public function sendEmailConfirme(string $firstname, string $email, string $passwordView): void {
        $expeditorEmail = "apprendreavecmiaou@gmail.com";
        $destinataire = $email;
        $subject = "CONFIRMATION DE RECEPTION DE DEMANDE D'INSCRIPTION SUR ApprendreAvecMiaou";
    
        $header = "MIME-Version: 1.0\r\n";
        $header .= 'From: "ApprendreAvecMiaou" <'.$expeditorEmail.'>'."\n";
        $header .= "X-Priority: 1\n";
        $header .= 'Content-Type: text/html; charset="utf-8"'."\n";
        $header .= 'Content-Transfer-Encoding: 8bit';
    
        $message = '<html>
        <body>
            <div style="font-size: 24px; text-align: center">
                <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
                <h1>Site de jeux et activités pour enfants</h1>
                <p>Bonjour, <span style="text-transform:uppercase">' . $firstname . '</span></p>
                <p>Votre demande d\'accès au site a bien été enregistrée.</p>
                <p>Votre mot de passe temporaire est : <strong>' . $passwordView . '</strong></p>
                <p>Email : <strong>' . $email . '</strong></p>
                <p style="color: rgb(255, 0, 0); font-size: 24px;">Nous vous conseillons de le changer rapidement !</p>
                <p>Respectez bien les majuscules et les minuscules.<br />
                Votre compte n\'est pas encore activé. Vous devez vous connectez et modifier votre mot de passe.</p>
                <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
            </div>
        </body>
        </html>';
    
        // Send the email
        mail($destinataire, $subject, $message, $header);
    
        // Optionally save the email to a file for records
        file_put_contents('documents/email1.html', $message);
        }*/

    
    public function sendEmailResponse(string $email, string $subjectReceived, string $firstname, string $contentReceived, string $receptedDate,   string $response): void {
        $expeditorEmail = "apprendreavecmiaou@alwaysdata.net";
        $destinataire = $email;
        $subject = "Re: '.$subjectReceived.'";
    
        $header = "MIME-Version: 1.0\r\n";
        $header .= 'From: "ApprendreAvecMiaou" <'.$expeditorEmail.'>'."\n";
        $header .= "X-Priority: 1\n";
        $header .= 'Content-Type: text/html; charset="utf-8"'."\n";
        $header .= 'Content-Transfer-Encoding: 8bit';
    
        $message = '<html>
        <body>
            <div style="font-size: 24px; text-align: center">
                <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
                <h1>Site de jeux et activités pour enfants</h1>
                <p>Bonjour, <span style="text-align: left">' . $firstname . '</span></p>
                <p>Votre message: '.$contentReceived.' du '.$receptedDate.' </p>
                
                <p>La réponse de ApprendreAvecMiaou : '.$response.' </p>
                
                <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
            </div>
        </body>
        </html>';
    
        // Send the email
        mail($destinataire, $subject, $message, $header);
    
        // Optionally save the email to a file for records
        file_put_contents('../documents/email2.html', $message);
    }
    
    public function sendPasswordResetEmail(string $firstname, string $email, string $newPassword): void {
    $expeditorEmail = "apprendreavecmiaou@alwaysdata.net";
    $destinataire = $email;
    $subject = "RÉINITIALISATION DE VOTRE MOT DE PASSE - ApprendreAvecMiaou";

    $header = "MIME-Version: 1.0\r\n";
    $header .= 'From: "ApprendreAvecMiaou" <'.$expeditorEmail.'>'."\n";
    $header .= "X-Priority: 1\n";
    $header .= 'Content-Type: text/html; charset="utf-8"'."\n";
    $header .= 'Content-Transfer-Encoding: 8bit';

    $message = '<html>
    <body>
        <div style="font-size: 18px; text-align: center; max-width: 600px; margin: 0 auto;">
            <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
            
            <h1 style="color: #333;">Réinitialisation de votre mot de passe</h1>
            
            <p>Bonjour <span style="text-transform:uppercase; font-weight: bold;">' . htmlspecialchars($firstname) . '</span>,</p>
            
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #856404; font-size: 16px;">
                    <strong>🔒 Votre mot de passe a été réinitialisé</strong>
                </p>
                
                <div style="background-color: #e9ecef; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 20px; margin: 15px 0; border-left: 4px solid #007bff;">
                    <strong>' . htmlspecialchars($newPassword) . '</strong>
                </div>
                
                <p><strong>Email :</strong> ' . htmlspecialchars($email) . '</p>
            </div>
            
            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <p style="color: #856404; font-weight: bold;">⚠️ IMPORTANT - SÉCURITÉ</p>
                <ul style="text-align: left; color: #856404;">
                    <li>Connectez-vous <strong>immédiatement</strong> avec ce nouveau mot de passe</li>
                    <li><strong>Changez ce mot de passe</strong> dès votre première connexion</li>
                    <li>Ne partagez <strong>jamais</strong> ce mot de passe</li>
                    <li>Supprimez cet email après vous être connecté</li>
                </ul>
            </div>
            
            <p style="font-size: 14px; color: #666;">
                Si vous n\'avez pas demandé cette réinitialisation, 
                <strong style="color: #dc3545;">contactez immédiatement l\'administrateur</strong>.
            </p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <p style="font-size: 12px; color: #999;">
                    Respectez bien les majuscules et les minuscules.<br/>
                    Email envoyé automatiquement le ' . date('d/m/Y à H:i') . '
                </p>
            </div>
            
            <img src="assets/img/Miaou/chatfiligranne.jpg" alt="logo du site"/>
        </div>
    </body>
    </html>';

    // Envoi de l'email
    $emailSent = mail($destinataire, $subject, $message, $header);
    
    // Log pour debug
    if ($emailSent) {
        error_log("Email de réinitialisation envoyé avec succès à : " . $email);
    } else {
        error_log("Échec envoi email de réinitialisation à : " . $email);
    }
    
    
    // Optionally save the email to a file for records
        file_put_contents('../documents/email3.html', $message);
    // Sauvegarde optionnelle pour les logs
    file_put_contents('../documents/email_reset_' . date('Y-m-d_H-i-s') . '.html', $message);
}
}
