<?php

require_once("models/Items.php");

class ItemManager extends AbstractManager {
    
   public function __construct()
    {
        parent::__construct();
    }

    // Ajouter un item
    public function add(Items $item): bool {
        $query = $this->db->prepare("
            INSERT INTO items (item_name, item_description) 
            VALUES (:item_name, :item_description)
        ");
        
        $parameters = [
            "item_name" => $item->getItemName(),
            "item_description" => $item->getItemDescription()
        ];

        return $query->execute($parameters);
    }

    // Mettre à jour un item
    public function update(Items $item): bool {
        $query = $this->db->prepare("
            UPDATE items 
            SET item_name = :item_name, item_description = :item_description 
            WHERE id = :id
        ");
        
        $parameters = [
            "item_name" => $item->getItemName(),
            "item_description" => $item->getItemDescription(),
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
        
        $parameters = [
            "id" => $id
        ];

        return $query->execute($parameters);
    }

    // Récupérer un item par son ID
    public function getById(int $id) {
        $query = $this->db->prepare("
            SELECT * FROM items 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        $query->execute($parameters);
        return $query->fetch();

        /*if ($data) {
            return new Items($data['id'], $data['item_name'], $data['item_description']);
        }

        return null;*/
    }

    // Récupérer tous les items
    public function getAllItems(): array {
        $query = $this->db->query("
            SELECT * FROM items
        ");

        $items = [];
        while ($data = $query->fetch()) {
            $items[] = new Items($data['id'], $data['item_name'], $data['item_description']);
        }

        return $items;
    }
    
    public function getAllItemIds(): array {
        $sql = "SELECT id FROM items";
        return array_column($this->findAll($sql), 'id');
    }
    
    public function countItems(): int {
        $sql = "SELECT COUNT(*) as count FROM items";
        $result = $this->findOne($sql);
        return $result['count'] ?? 0;
    }
    
    
    public function addItem(array $data): int {
        // Vérifier si l'item existe déjà
        if ($this->itemExists($data['item_name'])) {
            throw new Exception("L'item '{$data['item_name']}' existe déjà.");
        }
        
        $sql = "INSERT INTO items (item_name, item_description, url, alt) VALUES (:item_name, :item_description, :url, :alt)";
        $params = [
            'item_name' => $data['item_name'],
            'item_description' => $data['item_description'] ?? null,
            'url' => $data['url'] ?? null,
            'alt' => $data['alt'] ?? null
        ];
        
        $this->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function itemExists(string $name): bool {
        $sql = "SELECT id FROM items WHERE item_name = :item_name";
        $result = $this->findOne($sql, ['item_name' => $name]);
        return $result !== false;
    }
    
    /**
     * Récupère un item par son ID
     * @param int $id ID de l'item
     * @return array|false Données de l'item ou false si non trouvé
     */
    public function getItemById(int $id) {
        $sql = "SELECT * FROM items WHERE id = :id";
        return $this->findOne($sql, ['id' => $id]);
    }
    
    
    
    
}
?>
