<?php

//require_once("models/Avatars.php");

class AvatarManager extends AbstractManager {
    
    public function __construct()
    {
        parent::__construct();
    }

    // Ajouter un avatar
    public function addAvatar(Avatars $avatar): Avatars {
        $query = $this->db->prepare("
            INSERT INTO avatars (name, url, description, caracteristique, qualite, url_mini) 
            VALUES (:name, :url, :description, :caracteristique, :qualite, :urlMini)
        ");
        
        $parameters = [
            "name" => $avatar->getName(),
            "url" => $avatar->getUrl(),
            "description" => $avatar->getDescription(),
            "caracteristique" => $avatar->getCaracteristique(),
            "qualite" => $avatar->getQualite(),
            "urlMini" => $avatar->getUrlMini()
        ];

        $query->execute($parameters);
        $lastInsertId = $this->db->lastInsertId();

        // Retourner un nouvel item Avatar avec l'ID inséré
        return new Avatars(
            $lastInsertId,
            $avatar->getName(),
            $avatar->getUrl(),
            $avatar->getDescription(),
            $avatar->getCaracteristique(),
            $avatar->getQualite(),
            $avatar->getUrlMini()
        );
    }

    // Mettre à jour un avatar
    public function update(Avatars $avatar): bool {
        $query = $this->db->prepare("
            UPDATE avatars 
            SET name = :name, url = :url, description = :description, caracteristique = :caracteristique, qualite = :qualite, url_mini = :urlMini
            WHERE id = :id
        ");
        
        $parameters = [
            "name" => $avatar->getName(),
            "url" => $avatar->getUrl(),
            "description" => $avatar->getDescription(),
            "caracteristique" => $avatar->getCaracteristique(),
            "qualite" => $avatar->getQualite(),
            "urlMini" => $avatar->getUrlMini(),
            "id" => $avatar->getId()
        ];

        return $query->execute($parameters);
    }

    // Supprimer un avatar
    public function delete(int $id): bool {
        $query = $this->db->prepare("
            DELETE FROM avatars 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        return $query->execute($parameters);
    }

    // Récupérer un avatar par son ID
    public function getById(int $id): ?Avatars {
        $query = $this->db->prepare("
            SELECT * FROM avatars 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        $query->execute($parameters);
        $data = $query->fetch();

        if ($data) {
            return new Avatars(
                $data['id'],
                $data['name'],
                $data['url'],
                $data['description'],
                $data['caracteristique'],
                $data['qualite'],
                $data['url_mini']
            );
        }

        return null;
    }

    // Récupérer un avatar par son nom
    public function getByName(string $name): ?Avatars {
        $query = $this->db->prepare("
            SELECT * FROM avatars 
            WHERE name = :name
        ");
        
        $parameters = [
            "name" => $name
        ];

        $query->execute($parameters);
        $result = $query->fetch();

        if ($result) {
            return new Avatars(
                $result['id'],
                $result['name'],
                $result['url'],
                $result['description'],
                $result['caracteristique'],
                $result['qualite'],
                $result['url_mini']
            );
        }

        return null;
    }
    
    // Récupérer un avatar par son URL
    public function getByUrl(string $url): ?Avatars {
        $query = $this->db->prepare("SELECT * FROM avatars WHERE url = :url");
        $query->execute(['url' => $url]);
        $result = $query->fetch();

        if ($result) {
            return new Avatars(
                $result['id'],
                $result['name'],
                $result['url'],
                $result['description'],
                $result['caracteristique'],
                $result['qualite'],
                $result['url_mini']
            );
        }
        
        return null; 
    }

    // Récupérer tous les avatars
    public function findAllAvatars() : array
    {
        $query = $this->db->prepare('SELECT * FROM avatars');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $avatars = [];

        foreach($result as $item)
        {
            $avatar = new Avatars(
                $item["id"],
                $item["name"],
                $item["url"],
                $item["description"],
                $item["caracteristique"],
                $item["qualite"],
                $item['url_mini']
            );
            
            $avatars[] = $avatar;
        }

        return $avatars;
    }
    
    // Supprimer un avatar (version simplifiée)
    public function deleteAvatar(int $id) {
        $sql = "DELETE FROM `avatars` WHERE id = ?";
        $params = [$id]; // Utiliser un tableau indexé
        $this->execute($sql, $params);
    }
    
    // Réassigner les utilisateurs à un avatar par défaut
    public function reassignUsersToDefaultAvatar(int $oldAvatarId, int $defaultAvatarId = 4): void
    {
        $query = $this->db->prepare("
            UPDATE users 
            SET avatar = :defaultId 
            WHERE avatar = :oldId
        ");
        
        $query->execute([
            'defaultId' => $defaultAvatarId,
            'oldId' => $oldAvatarId,
        ]);
    }
    
    /* 
    // Méthode pour mettre à jour le mot de passe
    public function updatePassword(int $userId, string $passwordHash): bool {
        try {
            $query = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
            return $query->execute([
                ':password' => $passwordHash,
                ':id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updatePassword: " . $e->getMessage());
            return false;
        }
    }

    // Méthode pour mettre à jour le statut
    public function updateStatus(int $userId, int $status): bool {
        try {
            $query = $this->db->prepare("UPDATE users SET statut = :statut WHERE id = :id");
            return $query->execute([
                ':statut' => $status,
                ':id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updateStatus: " . $e->getMessage());
            return false;
        }
    }

    // Méthode pour mettre à jour le rôle
    public function updateRole(int $userId, int $role): bool {
        try {
            $query = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
            return $query->execute([
                ':role' => $role,
                ':id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updateRole: " . $e->getMessage());
            return false;
        }
    }
    */
}

?>
