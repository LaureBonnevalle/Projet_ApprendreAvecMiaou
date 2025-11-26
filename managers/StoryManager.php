<?php

class StoryManager extends AbstractManager {
    
    public function __construct() {
        parent::__construct();
    }

    public function create(Stories $story): bool {
        $query = $this->db->prepare('INSERT INTO stories (story_title, story_categorie, character, item, location, story_content, audio, url) VALUES (:story_title, :story_categorie, :character, :item, :location, :story_content, :audio, :url)');
        $query->bindValue(':story_title', $story->getStoryTitle());
        $query->bindValue(':story_categorie', $story->getCategorie(), PDO::PARAM_INT);
        $query->bindValue(':character', $story->getCharacter(), PDO::PARAM_INT);
        $query->bindValue(':item', $story->getItem(), PDO::PARAM_INT);
        $query->bindValue(':location', $story->getLocation(), PDO::PARAM_INT);
        $query->bindValue(':story_content', $story->getStoryContent());
        $query->bindValue(':audio', $story->getAudio());
        $query->bindValue(':url', $story->getUrl());
        return $query->execute();
    }

    public function read(int $id): ?Stories {
        $query = $this->db->prepare('SELECT * FROM stories WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Stories(
                $row['id'], 
                $row['story_title'], 
                $row['story_categorie'], 
                $row['character'], 
                $row['item'], 
                $row['location'], 
                $row['story_content'], 
                $row['audio'],
                $row['url']
            );
        }
        return null;
    }

    public function update(Stories $story): bool {
        $query = $this->db->prepare('UPDATE stories SET story_title = :story_title, story_categorie = :story_categorie, character = :character, item = :item, location = :location, story_content = :story_content, audio = :audio, url = :url WHERE id = :id');
        $query->bindValue(':story_title', $story->getStoryTitle());
        $query->bindValue(':story_categorie', $story->getCategorie(), PDO::PARAM_INT);
        $query->bindValue(':character', $story->getCharacter(), PDO::PARAM_INT);
        $query->bindValue(':item', $story->getItem(), PDO::PARAM_INT);
        $query->bindValue(':location', $story->getLocation(), PDO::PARAM_INT);
        $query->bindValue(':story_content', $story->getStoryContent());
        $query->bindValue(':audio', $story->getAudio());
        $query->bindValue(':url', $story->getUrl());
        $query->bindValue(':id', $story->getId(), PDO::PARAM_INT);
        return $query->execute();
    }

    public function delete(int $id): bool {
        $query = $this->db->prepare('DELETE FROM stories WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getAll(): array {
        $query = $this->db->query('SELECT * FROM stories');
        $stories = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $stories[] = new Stories(
                $row['id'], 
                $row['story_title'], 
                $row['story_categorie'], 
                $row['character'], 
                $row['item'], 
                $row['location'], 
                $row['story_content'], 
                $row['audio'],
                $row['url']
            );
        }
        return $stories;
    }
    
    /**
     * CORRECTION MAJEURE: Utilise les paramètres passés à la fonction
     * au lieu de récupérer $_GET
     */
    public function getStoryByCriteria($characterId, $itemId, $locationId) {
        $query = $this->db->prepare(
            "SELECT 
                s.*, 
                c.character_name AS character_name, 
                i.item_name AS item_name, 
                l.location_name AS location_name
            FROM stories s
            JOIN characters c ON s.character = c.id
            JOIN items i ON s.item = i.id
            JOIN locations l ON s.location = l.id 
            WHERE s.character = :character 
                AND s.item = :item 
                AND s.location = :location"
        );
       
        $query->execute([
            ':character' => $characterId,            
            ':item' => $itemId,
            ':location' => $locationId,
        ]);
       
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère toutes les stories avec leurs relations
     */
    public function getAllStories(): array {
        $sql = "SELECT s.*, 
                       i.item_name as item_name, 
                       l.location_name as location_name, 
                       c.character_name as character_name,
                       sc.nom as categorie_nom
                FROM stories s
                LEFT JOIN items i ON s.item = i.id
                LEFT JOIN locations l ON s.location = l.id  
                LEFT JOIN characters c ON s.character = c.id
                LEFT JOIN story_categories sc ON s.story_categorie = sc.id";
        
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les combinaisons existantes
     */
    public function getExistingCombinations(): array {
        $sql = "SELECT item, location, character FROM stories";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $stories = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $combinations = [];
        foreach ($stories as $story) {
            $key = implode('-', [$story['item'], $story['location'], $story['character']]);
            $combinations[$key] = true;
        }
        
        return $combinations;
    }
    
    /**
     * Calcule les combinaisons manquantes
     */
    public function getMissingCombinations(array $itemIds, array $locationIds, array $characterIds): array {
        $existingCombinations = $this->getExistingCombinations();
        
        $itemNames = $this->getElementNames('items', 'item_name');
        $locationNames = $this->getElementNames('locations', 'location_name');
        $characterNames = $this->getElementNames('characters', 'character_name');
        
        $missingCombinations = [];
        
        foreach ($itemIds as $itemId) {
            foreach ($locationIds as $locationId) {
                foreach ($characterIds as $characterId) {
                    $combinationKey = implode('-', [$itemId, $locationId, $characterId]);
                    
                    if (!isset($existingCombinations[$combinationKey])) {
                        $missingCombinations[] = [
                            'item_id' => $itemId,
                            'location_id' => $locationId,
                            'character_id' => $characterId,
                            'item_name' => $itemNames[$itemId] ?? 'Inconnu',
                            'location_name' => $locationNames[$locationId] ?? 'Inconnu',
                            'character_name' => $characterNames[$characterId] ?? 'Inconnu'
                        ];
                    }
                }
            }
        }
        
        return $missingCombinations;
    }
    
    /**
     * Calcule le total de combinaisons possibles
     */
    public function calculateTotalCombinations(int $nbItems, int $nbLocations, int $nbCharacters): int {
        return $nbItems * $nbLocations * $nbCharacters;
    }
    
    /**
     * Ajoute une story
     */
    public function addStory(array $data): int {
        $sql = "INSERT INTO stories (story_title, story_categorie, character, item, location, story_content, audio, url) 
                VALUES (:story_title, :story_categorie, :character, :item, :location, :story_content, :audio, :url)";
        
        $params = [
            'story_title' => $data['story_title'] ?? null,
            'story_categorie' => $data['story_categorie'] ?? null,
            'character' => $data['character'],
            'item' => $data['item'],
            'location' => $data['location'],
            'story_content' => $data['story_content'],
            'audio' => $data['audio'] ?? null,
            'url' => $data['url'] ?? null
        ];
        
        try {
            $statement = $this->db->prepare($sql);
            error_log("💾 Insertion story avec : " . print_r($params, true));
            $statement->execute($params);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("❌ PDO Error : " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Récupère les noms des éléments
     */
    private function getElementNames(string $table, string $nameColumn): array {
        $sql = "SELECT id, {$nameColumn} FROM {$table}";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $elements = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $names = [];
        foreach ($elements as $element) {
            $names[$element['id']] = $element[$nameColumn];
        }
        
        return $names;
    }
    
    /**
     * Compte les stories
     */
    public function countStories(): int {
        $sql = "SELECT COUNT(*) as count FROM stories";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
?>