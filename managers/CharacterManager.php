<?php

class CharacterManager extends AbstractManager {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function add(Characters $character): bool {
        $query = $this->db->prepare("
            INSERT INTO characters (character_name, character_description, url, alt) 
            VALUES (:character_name, :character_description, :url, :alt)
        ");
        
        $parameters = [
            "character_name" => $character->getCharacterName(),
            "character_description" => $character->getCharacterDescription(),
            "url" => $character->getUrl(),
            "alt" => $character->getAlt()
        ];

        return $query->execute($parameters);
    }

    public function update(Characters $character): bool {
        $query = $this->db->prepare("
            UPDATE characters 
            SET character_name = :character_name, 
                character_description = :character_description,
                url = :url,
                alt = :alt
            WHERE id = :id
        ");
        
        $parameters = [
            "character_name" => $character->getCharacterName(),
            "character_description" => $character->getCharacterDescription(),
            "url" => $character->getUrl(),
            "alt" => $character->getAlt(),
            "id" => $character->getId()
        ];

        return $query->execute($parameters);
    }

    public function delete(int $id): bool {
        $query = $this->db->prepare("
            DELETE FROM characters 
            WHERE id = :id
        ");
        
        $parameters = ["id" => $id];
        return $query->execute($parameters);
    }

    public function getById(int $id): ?Characters {
        $query = $this->db->prepare("
            SELECT * FROM characters 
            WHERE id = :id
        ");
        
        $query->execute(["id" => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return new Characters(
                $row['id'], 
                $row['character_name'], 
                $row['character_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return null;
    }

    public function getAllCharacters(): array {
        $query = $this->db->prepare("SELECT * FROM characters");
        $query->execute();
        
        $characters = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $characters[] = new Characters(
                $row['id'], 
                $row['character_name'], 
                $row['character_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }

        return $characters;
    }
    
    public function getAllCharacterIds(): array {
        $sql = "SELECT id FROM characters";
        $query = $this->db->prepare($sql);
        $query->execute();
        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
    
    public function countCharacters(): int {
        $sql = "SELECT COUNT(*) as count FROM characters";
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    public function addCharacter(array $data): int {
        if ($this->characterExists($data['character_name'])) {
            throw new Exception("Le personnage '{$data['character_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO characters (character_name, character_description, url, alt) 
                VALUES (:character_name, :character_description, :url, :alt)";
        $params = [
            'character_name' => $data['character_name'],
            'character_description' => $data['character_description'] ?? '',
            'url' => $data['url'] ?? '',
            'alt' => $data['alt'] ?? ''
        ];
        
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $this->db->lastInsertId();
    }
    
    public function characterExists(string $name): bool {
        $sql = "SELECT id FROM characters WHERE character_name = :name";
        $query = $this->db->prepare($sql);
        $query->execute(['name' => $name]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }
    
    public function getCharacterById(int $id): ?Characters {
        return $this->getById($id);
    }
}
