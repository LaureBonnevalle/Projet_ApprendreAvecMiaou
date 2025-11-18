<?php


class ErrorMessages {

    public function getMessages() {

        // Tableau des messages d'erreurs possibles à l'affichage.
        $errorMessages = [
            /*   0 */ "Une erreur est survenue lors de l'envoi du formulaire !",
            /*   1 */ "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.",
            /*   2 */ "Veuillez renseigner un email valide SVP !",
            /*   3 */ "Désolé, votre compte a été banni !",
            /*   4 */ "Erreur d'identification !",
            /*   5 */ "Veuillez remplir le champ 'Nom' !",
            /*   6 */ "Veuillez remplir le champ 'Prénom' !",
            /*   7 */ "Un compte existe déjà avec cette adresse email ou avec le même matricule pour ce secteur d'activité !",
            /*   8 */ "Vous n'avez pas confirmé le mot de passe correctement !",
            /*   9 */ "Veuillez remplir le champ 'Nom & prénom' !",
            /*  10 */ "Veuillez remplir le champ 'Sujet du message' !",
            /*  11 */ "Veuillez remplir le champ 'Message' !",
            /*  12 */ "Veuillez renseigner le champ 'Titre' !",
            /*  13 */ "Veuillez renseigner le champ 'Sous-Titre' !",
            /*  14 */ "Veuillez renseigner votre secteur d'activité SVP !",
            /*  15 */ "Veuillez renseigner votre matricule SVP !",
            /*  16 */ "Veuillez sélectionner un secteur !",
            /*  17 */ "Vous n'avez pas confirmé correctement le code du captcha !",
            /*  18 */ "Le fichier n'a pas été enregistré correctement !",
            /*  19 */ "Le fichier n'a pas été enregistré correctement car son contenu ne correspond pas à son extension !",
            /*  20 */ "Ce type de fichier n'est pas autorisé !",
            /*  21 */ "Le fichier est trop volumineux !",
            /*  22 */ "Une erreur a eu location au moment du téléchargement du fichier !",
            /*  23 */ "Les mots de passe ne correspondent pas !",
            /*  24 */ "Le nouveau mot de passe doit être différent de l'ancien mot de passe !",
            /*  25 */ "Il ne s'agit pas du bon matricule !",
            /*  26 */ "Le mot de passe provisoire qui vous a été envoyé doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.",
            /*  27 */ "Votre nouveau mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.",
            /*  28 */ "Le nouveau mot de passe ne doit pas être identique à l'ancien !",
            /*  29 */ "Veuillez remplir le champ 'Grade' !",
            /*  30 */ "Veuillez remplir le champ 'Qualification' !",
            /*  31 */ "Vous devez obligatoirement remplir le choix n°1 !",
            /*  32 */ "Il y a des choix en double. Ce qui n'est pas possible. Dans vos choix, vous ne pouvez pas choisir votre service, votre département ou plusieurs fois le même choix !",
            /*  33 */ "Vous devez obligatoirement remplir votre département d'affectation actuel !",
            /*  34 */ "Vous devez obligatoirement remplir votre service d'affectation actuel !",
            /*  35 */ "Veuillez renseigner votre date de statutaire !",
            /*  36 */ "Le champ 'Décrivez votre poste actuel' doit contenir au moins 30 caractères !",
            /*  37 */ "La modification de votre compte n'est pas possible pour le moment !",
            /*  38 */ "Il a des erreurs dans le formulaire",
            /*  39 */ "Vous avez reçu une alerte",
            /*  40 */ "Merci de remplir le champ 'Décision' !",
            /*  41 */ "Merci de remplir le champ 'Motif' qui doit contenir au minimum 20 caractères !",
            /*  42 */ "Veuillez remplir le champ 'Email'",
            /*  43 */ "Token Invalide",
            /*  44 */ "Aucun utilisateur n'existe avec ce mail",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",
            /*    */ "...",







        ];

        return $errorMessages;
    }



}


/*
    -- on arrive sur la page d'accueil
        - Le localstorage existe il ?
            NON - > paramètres de base + Créé le localstorage
            OUI - > Lit son contenu et on adapte la vision en fonction des choix contenus dans la clé du LS
            
            localstorage.setItem('vision', "Arial")
            localstorage.setItem('bgcolorBlack', "true")
            
    -- Dès qu'on arrive sur la moindre page du site
        -- Charger les données du LS

*/