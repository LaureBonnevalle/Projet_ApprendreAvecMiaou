<?php
/*require_once('managers/LocationManager.php');
require_once('managers/ItemManager.php');
require_once('managers/CharacterManager.php');*/

class StoryManager extends AbstractManager {
    
   public function __construct()
    {
        parent::__construct();
    }

    public function create(Stories $story): Story {
        $query = $this->db->prepare('INSERT INTO stories (story_titre, categorie, character, item, location, story_content, audio) VALUES (:story_titre, :categorie, :character, :item, :location, :story_content, :audio)');
        $query->bindValue(':story_titre', $story->getStoryTitre());
        $query->bindValue(':categorie', $story->getCategorie(), PDO::PARAM_INT);
        $query->bindValue(':character', $story->getCharacter(), PDO::PARAM_INT);
        $query->bindValue(':item', $story->getItem(), PDO::PARAM_INT);
        $query->bindValue(':location', $story->getLocation(), PDO::PARAM_INT);
        $query->bindValue(':story_content', $story->getStoryContent());
        $query->bindValue(':audio', $story->getAudio());
        return $query->execute();
    }

    public function read(int $id): ?Stories {
        $query = $this->pdo->prepare('SELECT * FROM stories WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Stories($row['id'], $row['story_titre'], $row['categorie'], $row['character'], $row['item'], $row['location'], $row['story_content'], $row['audio']);
        }
        return null;
    }

    public function update(Stories $story): Story {
        $query = $this->pdo->prepare('UPDATE stories SET story_titre = :story_titre, categorie = :categorie, character = :character, item = :item, location = :location, story_content = :story_content, audio = :audio WHERE id = :id');
        $query->bindValue(':story_titre', $story->getStoryTitre());
        $query->bindValue(':categorie', $story->getCategorie(), PDO::PARAM_INT);
        $query->bindValue(':character', $story->getCharacter(), PDO::PARAM_INT);
        $query->bindValue(':item', $story->getItem(), PDO::PARAM_INT);
        $query->bindValue(':location', $story->getLocation(), PDO::PARAM_INT);
        $query->bindValue(':story_content', $story->getStoryContent());
        $query->bindValue(':audio', $story->getAudio());
        $query->bindValue(':id', $story->getId(), PDO::PARAM_INT);
        return $query->execute();
    }

    public function delete(int $id): bool {
        $query = $this->pdo->prepare('DELETE FROM stories WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getAll(): array {
        $query = $this->pdo->query('SELECT * FROM stories');
        $stories = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $stories[] = new Stories($row['id'], $row['story_titre'], $row['categorie'], $row['character'], $row['item'], $row['location'], $row['story_content'], $row['audio']);
        }
        return $stories;
    }
    
    public function getStoryByCriteria($characterId, $itemId, $locationId) {
        
        $characterId = $_GET['perso'];
        $itemId = $_GET['item'];
        $locationId  = $_GET['location'];        
                
        $query = $this->db->prepare(
            "SELECT 
                h.*, 
                p.perso_name AS character, 
                o.item_name AS item, 
                l.location_name AS location
                FROM stories h
                JOIN characters p 
                    ON h.character = p.id
                JOIN items o 
                    ON h.item = o.id
                JOIN locations l 
                    ON h.location = l.id 
                
                WHERE h.character = :character 
                    AND h.item = :item 
                    AND h.location = :location"
        );
       
        $query->execute([
            ':character' => $characterId,            
            ':item' => $itemId,
            ':location' => $locationId,
        ]);
       
        return $query->fetch(PDO::FETCH_ASSOC);
        
    }
    
    public function getImageUrl($characterId, $locationId, $itemId) {
        $query = $this->db->prepare(
            "SELECT image_url
            FROM stories
            WHERE character_id = :characterId AND location_id = :locationId AND item_id = :itemId"
        );
        $query->execute([
            ':characterId' => $characterId,
            ':locationId' => $locationId,
            ':itemId' => $itemId
        ]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image_url'] : null;
    }
    
     /**
     * Récupère toutes les stories existantes
     * @return array Liste des stories avec leurs relations
     */
    public function getAllStories(): array {
        $sql = "SELECT h.*, 
                       o.name as item_nom, 
                       l.location_name as location_nom, 
                       p.perso_name as character_nom,
                       sc.nom as categorie_nom
                FROM stories h
                LEFT JOIN items o ON h.item = o.id
                LEFT JOIN locations l ON h.location = l.id  
                LEFT JOIN characters p ON h.character = p.id
                LEFT JOIN story_categories sc ON h.story_categorie = sc.id";
        
        return $this->findAll($sql);
    }
    
    /**
     * Récupère les combinaisons existantes sous forme de clés
     * @return array Tableau associatif avec les clés "item_id-location_id-character_id"
     */
    public function getExistingCombinations(): array {
    $sql = "SELECT item, location, character FROM stories";
    
    // Utiliser $this->db au location de $this->pdo
    $statement = $this->db->prepare($sql);
    $statement->execute();
    $stories = $statement->fetchAll();
    
    $combinations = [];
    foreach ($stories as $story) {
        $key = implode('-', [$story['item'], $story['location'], $story['character']]);
        $combinations[$key] = true;
    }
    
    return $combinations;
}
    
    /**
     * Calcule toutes les combinaisons possibles manquantes
     * @param array $itemIds Liste des IDs d'items
     * @param array $locationIds Liste des IDs de locations  
     * @param array $characterIds Liste des IDs de characters
     * @return array Liste des combinaisons manquantes avec noms
     */
    public function getMissingCombinations(array $itemIds, array $locationIds, array $characterIds): array {
        // Récupération des combinaisons existantes
        $existingCombinations = $this->getExistingCombinations();
        
        // Récupération des noms pour l'affichage
        $itemNames = $this->getElementNames('items', 'name');
        $locationNames = $this->getElementNames('locations', 'location_name');
        $characterNames = $this->getElementNames('characters', 'perso_name');
        
        $missingCombinations = [];
        
        // Génération de toutes les combinaisons possibles
        foreach ($itemIds as $itemId) {
            foreach ($locationIds as $locationId) {
                foreach ($characterIds as $characterId) {
                    $combinationKey = implode('-', [$itemId, $locationId, $characterId]);
                    
                    // Si la combinaison n'existe pas, l'ajouter aux manquantes
                    if (!isset($existingCombinations[$combinationKey])) {
                        $missingCombinations[] = [
                            'item_id' => $itemId,
                            'location_id' => $locationId,
                            'character_id' => $characterId,
                            'item_nom' => $itemNames[$itemId] ?? 'Inconnu',
                            'location_nom' => $locationNames[$locationId] ?? 'Inconnu',
                            'character_nom' => $characterNames[$characterId] ?? 'Inconnu'
                        ];
                    }
                }
            }
        }
        
        return $missingCombinations;
    }
    
    /**
     * Calcule le nombre total de combinaisons possibles
     * @param int $nbItems Nombre d'items
     * @param int $nbLocations Nombre de locations
     * @param int $nbCharacters Nombre de characters
     * @return int Nombre total de combinaisons
     */
    public function calculateTotalCombinations(int $nbItems, int $nbLocations, int $nbCharacters): int {
        return $nbItems * $nbLocations * $nbCharacters;
    }
    
    /**
     * Ajoute une nouvelle story
     * @param array $data Données de l'story
     * @return int ID de l'story créée
     */
    /**
 * Ajoute une nouvelle story
 * @param array $data Données de l'story
 * @return int ID de l'story créée
 */
public function addStory(array $data): int {
    $sql = "INSERT INTO stories (story_titre, story_categorie, character, item, location, story_content, audio, url) 
            VALUES (:story_titre, :story_categorie, :character, :item, :location, :story_content, :audio, :url)";
    
    $params = [
        'story_titre' => $data['story_titre'] ?? null,
        'story_categorie' => $data['story_categorie'] ?? null,
        'character' => $data['character'],
        'item' => $data['item'],
        'location' => $data['location'],
        'story_content' => $data['story_content'],
        'audio' => $data['audio'] ?? null,
        'url' => $data['url'] ?? null
    ];
    
    // Utiliser $this->db au location de $this->execute() pour être cohérent avec le reste du code
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
     * Récupère les noms des éléments d'une table
     * @param string $table Nom de la table
     * @param string $nameColumn Nom de la colonne contenant le nom
     * @return array Tableau associatif [id => nom]
     */
    private function getElementNames(string $table, string $nameColumn): array {
        $sql = "SELECT id, {$nameColumn} FROM {$table}";
        $elements = $this->findAll($sql);
        
        $names = [];
        foreach ($elements as $element) {
            $names[$element['id']] = $element[$nameColumn];
        }
        
        return $names;
    }
    
    /**
     * Compte le nombre d'stories existantes
     * @return int Nombre d'stories
     */
    public function countStories(): int {
        $sql = "SELECT COUNT(*) as count FROM stories";
        $result = $this->findOne($sql);
        return $result['count'] ?? 0;
    }
}
?>
