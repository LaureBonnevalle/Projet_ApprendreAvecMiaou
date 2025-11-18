<?php


//require_once("models/Locations.php");

class LocationManager extends AbstractManager {
    
   public function __construct()
    {
        parent::__construct();
    }

    public function create(Locations $location): bool {
        $query = $this->db->prepare('INSERT INTO locations (location_name, location_description) VALUES (:location_name, :location_description)');
        $query->bindValue(':location_name', $location->getLocationName());
        $query->bindValue(':location_description', $location->getLocationDescription());
        return $query->execute();
    }

    public function read(int $id): ?Locations {
        $query = $this->db->prepare('SELECT * FROM locations WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Locations($row['id'], $row['location_name'], $row['location_description']);
        }
        return null;
    }

    public function update(Locations $location): bool {
        $query = $this->db->prepare('UPDATE locations SET location_name = :location_name, location_description = :location_description WHERE id = :id');
        $query->bindValue(':location_name', $location->getLocationName());
        $query->bindValue(':location_description', $location->getLocationDescription());
        $query->bindValue(':id', $location->getId(), PDO::PARAM_INT);
        return $query->execute();
    }

    public function delete(int $id): bool {
        $query = $this->db->prepare('DELETE FROM locations WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getAllLocations(): array {
        $query = $this->db->query('SELECT * FROM locations');
        $locations = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $locations[] = new Locations($row['id'], $row['location_name'], $row['location_description']);
        }
        return $locations;
    }
    
    public function getById(int $id)  {
        
        //file_put_contents('text2.txt', $id);
        
        $query = $this->db->prepare("
            SELECT * FROM locations 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        $query->execute($parameters);
        return $query->fetch();
    }
    
    /* Récupère les IDs de tous les locations
     * @return array Liste des IDs
     */
    public function getAllLocationIds(): array {
        $sql = "SELECT id FROM locations";
        return array_column($this->findAll($sql), 'id');
    }
    
    /**
     * Compte le nombre de locations
     * @return int Nombre de locations
     */
    public function countLocations(): int {
        $sql = "SELECT COUNT(*) as count FROM locations";
        $result = $this->findOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Ajoute un nouveau location
     * @param array $data Données du location (location_name, location_description, url, alt)
     * @return int ID du location créé
     * @throws Exception Si le location existe déjà
     */
    public function addLocation(array $data): int {
        // Vérifier si le location existe déjà
        if ($this->locationExists($data['location_name'])) {
            throw new Exception("Le location '{$data['location_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO locations (location_name, location_description, url, alt) VALUES (:location_name, :location_description, :url, :alt)";
        $params = [
            'location_name' => $data['location_name'],
            'location_description' => $data['location_description'] ?? null,
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
     * Vérifie si un location existe déjà
     * @param string $name Nom du location
     * @return bool True si le location existe
     */
    public function locationExists(string $name): bool {
        $sql = "SELECT id FROM locations WHERE location_name = :name";
        $result = $this->findOne($sql, ['name' => $name]);
        return $result !== false;
    }
    
    /**
     * Récupère un location par son ID
     * @param int $id ID du location
     * @return array|false Données du location ou false si non trouvé
     */
    public function getLocationById(int $id) {
        $sql = "SELECT * FROM locations WHERE id = :id";
        return $this->findOne($sql, ['id' => $id]);
    }
    
    
    
    
    
    
    
    
    
}
?>