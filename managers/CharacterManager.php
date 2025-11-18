<?php

require_once('models/Characters.php');

class CharacterManager extends AbstractManager {
    
   public function __construct()
    {
        parent::__construct();
    }
    
    // Ajouter un character
    public function add(Characters $character): Character {
        $query = $this->db->prepare("
            INSERT INTO characters (perso_name, perso_description) 
            VALUES (:perso_name, :perso_description)
        ");
        
        $parameters = [
            "perso_name" => $character->getPersoName(),
            "perso_description" => $character->getPersoDescription()
        ];

        return $query->execute($parameters);
    }

    // Mettre à jour un character
    public function update(Characters $character): Character {
        $query = $this->db->prepare("
            UPDATE characters 
            SET perso_name = :perso_name, perso_description = :perso_description 
            WHERE id = :id
        ");
        
        $parameters = [
            "perso_name" => $character->getPersoName(),
            "perso_description" => $character->getPersoDescription(),
            "id" => $character->getId()
        ];

        return $query->execute($parameters);
    }

    // Supprimer un character
    public function delete(int $id): Character {
        $query = $this->db->prepare("
            DELETE FROM characters 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        return $query->execute($parameters);
    }

    // Récupérer un character par son ID
    public function getById(int $id)  {
        
        //file_put_contents('text2.txt', $id);
        
        $query = $this->db->prepare("
            SELECT * FROM characters 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        $query->execute($parameters);
        return $query->fetch();
/*
        if ($data) {
            return new Characters($data['id'], $data['perso_name'], $data['perso_description']);
        }

        return null;*/
    }

    // Récupérer tous les characters
    public function getAllCharacters(): array {
        $query = $this->db->prepare("
            SELECT * FROM characters
        ");
        $result =$query->execute();
        
        //return $result->fetch(PDO::FETCH_ASSOC);

        $characters = [];
        while ($result = $query->fetch()) {
            $characters[] = new Characters($result['id'], $result['perso_name'], $result['perso_description']);
        }

        return $characters;
    }
    
    /**
     * Récupère les IDs de tous les characters
     * @return array Liste des IDs
     */
    public function getAllCharacterIds(): array {
        $sql = "SELECT id FROM characters";
        return array_column($this->findAll($sql), 'id');
    }
    
    /**
     * Compte le nombre de characters
     * @return int Nombre de characters
     */
    public function countCharacters(): int {
        $sql = "SELECT COUNT(*) as count FROM characters";
        $result = $this->findOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Ajoute un nouveau character
     * @param array $data Données du character (perso_name, perso_description, url, alt)
     * @return int ID du character créé
     * @throws Exception Si le character existe déjà
     */
    public function addCharacter(array $data): int {
        // Vérifier si le character existe déjà
        if ($this->characterExists($data['perso_name'])) {
            throw new Exception("Le character '{$data['perso_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO characters (perso_name, perso_description, url, alt) VALUES (:perso_name, :perso_description, :url, :alt)";
        $params = [
            'perso_name' => $data['perso_name'],
            'perso_description' => $data['perso_description'] ?? null,
            'url' => $data['url'] ?? null,
            'alt' => $data['alt'] ?? null
        ];
        try {
    $this->execute($sql, $params);
    return $this->db->lastInsertId();
} catch (\PDOException $e) {
    throw new Exception("Erreur SQL : " . $e->getMessage());
}
    }
    
    /**
     * Vérifie si un character existe déjà
     * @param string $name Nom du character
     * @return bool True si le character existe
     */
    public function characterExists(string $name): bool {
        $sql = "SELECT id FROM characters WHERE perso_name = :name";
        $result = $this->findOne($sql, ['name' => $name]);
        return $result !== false;
    }
    
    /**
     * Récupère un character par son ID
     * @param int $id ID du character
     * @return array|false Données du character ou false si non trouvé
     */
    public function getCharacterById(int $id) {
        $sql = "SELECT * FROM characters WHERE id = :id";
        return $this->findOne($sql, ['id' => $id]);
    }
    
    
    
    
    
    
    
    
    
}
?>
