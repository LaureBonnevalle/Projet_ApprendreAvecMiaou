<?php

class ColoringCategoriesManager extends AbstractManager {

    public function getAll(): array {
    $stmt = $this->db->prepare("SELECT id, categorie_name, categorie_description FROM coloring_categories");
    $stmt->execute();
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];
    foreach ($datas as $data) {
        $categories[] = new ColoringCategory(
            $data['id'],
            $data['categorie_name'],
            $data['categorie_description']
        );
    
        }
    return $categories;
    
}
    


    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, description FROM coloring_categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(ColoringCategory $cat): bool {
        $stmt = $this->db->prepare("INSERT INTO coloring_categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$cat->getName(), $cat->getDescription()]);
    }

    public function update(ColoringCategory $cat): bool {
        $stmt = $this->db->prepare("UPDATE coloring_categories SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$cat->getName(), $cat->getDescription(), $cat->getId()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM coloring_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
