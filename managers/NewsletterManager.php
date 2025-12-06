<?php

class NewsletterManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Abonne un utilisateur à la newsletter
     *
     * @param string $firstname
     * @param string $email
     * @return bool true si l'abonnement est enregistré, false sinon
     */
    public function subscribe(string $firstname, string $email) : bool
    {
        try {
            // Vérifier si l'email existe déjà
            $sql = "SELECT COUNT(*) FROM newsletter WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $exists = $stmt->fetchColumn();

            if ($exists > 0) {
                // Email déjà abonné
                return false;
            }

            // Insérer le nouvel abonnement
            $sql = "INSERT INTO newsletter (firstname, email, subscribed_at) 
                    VALUES (:firstname, :email, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'firstname' => $firstname,
                'email'     => $email
            ]);

            return true;
        } catch (PDOException $e) {
            // Log de l'erreur si besoin
            error_log("Erreur NewsletterManager::subscribe - " . $e->getMessage());
            return false;
        }
    }

     public function getAllSubscribers() : array
    {
        try {
            $sql = "
                SELECT email FROM newsletter
                UNION
                SELECT email FROM users WHERE newsletter = 1
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur NewsletterManager::getAllSubscribers - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprime un abonnement à la newsletter
     * - Si l'email est dans la table newsletter → suppression
     * - Si l'email est dans la table users → newsletter = 0
     *
     * @param string $email
     * @return bool true si suppression réussie, false sinon
     */
    public function deleteSubscription(string $email) : bool
    {
        try {
            // Vérifier si l'email est dans la table newsletter
            $sql = "SELECT COUNT(*) FROM newsletter WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $existsNewsletter = $stmt->fetchColumn();

            if ($existsNewsletter > 0) {
                $sql = "DELETE FROM newsletter WHERE email = :email";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute(['email' => $email]);
            }

            // Vérifier si l'email est dans la table users
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND newsletter = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $existsUser = $stmt->fetchColumn();

            if ($existsUser > 0) {
                $sql = "UPDATE users SET newsletter = 0 WHERE email = :email";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute(['email' => $email]);
            }

            return false; // Email non trouvé
        } catch (PDOException $e) {
            error_log("Erreur NewsletterManager::deleteSubscription - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un email de la table newsletter s'il existe.
     *
     * @param string $email
     * @return bool true si suppression effectuée, false sinon
     */
    public function deleteByEmail(string $email) : bool
    {
        try {
            $sql = "DELETE FROM newsletter WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);

            // rowCount() retourne le nombre de lignes supprimées
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur NewsletterManager::deleteByEmail - " . $e->getMessage());
            return false;
        }
    }
}