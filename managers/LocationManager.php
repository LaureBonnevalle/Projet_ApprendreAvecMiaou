<?php

class LocationManager extends AbstractManager {
    
    public function __construct() {
        parent::__construct();
    }

    public function create(Locations $location): bool {
        $query = $this->db->prepare('
            INSERT INTO locations (location_name, location_description, url, alt) 
            VALUES (:location_name, :location_description, :url, :alt)
        ');
        $query->bindValue(':location_name', $location->getLocationName());
        $query->bindValue(':location_description', $location->getLocationDescription());
        $query->bindValue(':url', $location->getUrl());
        $query->bindValue(':alt', $location->getAlt());
        return $query->execute();
    }

    public function read(int $id): ?Locations {
        $query = $this->db->prepare('SELECT * FROM locations WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Locations(
                $row['id'],
                $row['location_name'],
                $row['location_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return null;
    }

    public function update(Locations $location): bool {
        $query = $this->db->prepare('
            UPDATE locations 
            SET location_name = :location_name, 
                location_description = :location_description,
                url = :url,
                alt = :alt
            WHERE id = :id
        ');
        $query->bindValue(':location_name', $location->getLocationName());
        $query->bindValue(':location_description', $location->getLocationDescription());
        $query->bindValue(':url', $location->getUrl());
        $query->bindValue(':alt', $location->getAlt());
        $query->bindValue(':id', $location->getId(), PDO::PARAM_INT);
        return $query->execute();
    }

    public function delete(int $id): bool {
        $query = $this->db->prepare('DELETE FROM locations WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getAllLocations(): array {
        $query = $this->db->prepare('SELECT * FROM locations');
        $query->execute();
        $locations = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $locations[] = new Locations(
                $row['id'],
                $row['location_name'],
                $row['location_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return $locations;
    }
    
    public function getById(int $id): ?Locations {
    $query = $this->db->prepare('SELECT * FROM locations WHERE id = :id');
    $query->execute(['id' => $id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        return new Locations(
            $row['id'],
            $row['location_name'],
            $row['location_description'],
            $row['url'] ?? '',
            $row['alt'] ?? ''
        );
    }
    return null;
}

    
    public function getAllLocationIds(): array {
        $sql = "SELECT id FROM locations";
        $query = $this->db->prepare($sql);
        $query->execute();
        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
    
    public function countLocations(): int {
        $sql = "SELECT COUNT(*) as count FROM locations";
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    public function addLocation(array $data): int {
        if ($this->locationExists($data['location_name'])) {
            throw new Exception("Le lieu '{$data['location_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO locations (location_name, location_description, url, alt) 
                VALUES (:location_name, :location_description, :url, :alt)";
        $params = [
            'location_name' => $data['location_name'],
            'location_description' => $data['location_description'] ?? '',
            'url' => $data['url'] ?? '',
            'alt' => $data['alt'] ?? ''
        ];
        
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $this->db->lastInsertId();
    }
    
    public function locationExists(string $name): bool {
        $sql = "SELECT id FROM locations WHERE location_name = :name";
        $query = $this->db->prepare($sql);
        $query->execute(['name' => $name]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }
    
    public function getLocationById(int $id) {
        $sql = "SELECT * FROM locations WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
