<?php

class ItemManager extends AbstractManager {
    
    public function __construct() {
        parent::__construct();
    }

    // Ajouter un item
    public function add(Items $item): bool {
        $query = $this->db->prepare("
            INSERT INTO items (item_name, item_description, url, alt) 
            VALUES (:item_name, :item_description, :url, :alt)
        ");
        
        $parameters = [
            "item_name" => $item->getItemName(),
            "item_description" => $item->getItemDescription(),
            "url" => $item->getUrl(),
            "alt" => $item->getAlt()
        ];

        return $query->execute($parameters);
    }

    // Mettre à jour un item
    public function update(Items $item): bool {
        $query = $this->db->prepare("
            UPDATE items 
            SET item_name = :item_name, 
                item_description = :item_description,
                url = :url,
                alt = :alt
            WHERE id = :id
        ");
        
        $parameters = [
            "item_name" => $item->getItemName(),
            "item_description" => $item->getItemDescription(),
            "url" => $item->getUrl(),
            "alt" => $item->getAlt(),
            "id" => $item->getId()
        ];

        return $query->execute($parameters);
    }

    // Supprimer un item
    public function delete(int $id): bool {
        $query = $this->db->prepare("
            DELETE FROM items 
            WHERE id = :id
        ");
        
        return $query->execute(["id" => $id]);
    }

    // Récupérer un item par son ID
    public function getById(int $id): ?Items {
        $query = $this->db->prepare("
            SELECT * FROM items 
            WHERE id = :id
        ");
        $query->execute(["id" => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Items(
                $row['id'],
                $row['item_name'],
                $row['item_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return null;
    }

    // Récupérer tous les items
    public function getAllItems(): array {
        $query = $this->db->query("SELECT * FROM items");
        $items = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new Items(
                $row['id'],
                $row['item_name'],
                $row['item_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return $items;
    }
    
    public function getAllItemIds(): array {
        $sql = "SELECT id FROM items";
        $query = $this->db->prepare($sql);
        $query->execute();
        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
    
    public function countItems(): int {
        $sql = "SELECT COUNT(*) as count FROM items";
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    public function addItem(array $data): int {
        if ($this->itemExists($data['item_name'])) {
            throw new Exception("L'item '{$data['item_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO items (item_name, item_description, url, alt) 
                VALUES (:item_name, :item_description, :url, :alt)";
        $params = [
            'item_name' => $data['item_name'],
            'item_description' => $data['item_description'] ?? '',
            'url' => $data['url'] ?? '',
            'alt' => $data['alt'] ?? ''
        ];
        
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $this->db->lastInsertId();
    }
    
    public function itemExists(string $name): bool {
        $sql = "SELECT id FROM items WHERE item_name = :item_name";
        $query = $this->db->prepare($sql);
        $query->execute(['item_name' => $name]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }
    
    public function getItemById(int $id): ?Items {
        $sql = "SELECT * FROM items WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Items(
                $row['id'],
                $row['item_name'],
                $row['item_description'],
                $row['url'] ?? '',
                $row['alt'] ?? ''
            );
        }
        return null;
    }
}
