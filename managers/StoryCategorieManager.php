<?php
/*require_once("AbstractManager.php");
require_once("models/StoriesCategorie.php");*/

class StoryCategorieManager extends AbstractManager {
    
    /**
     * Nouvelle méthode pour récupérer toutes les catégories (contourne le problème findAll)
     * @return array Liste des catégories
     */
    public function getAllCategories(): array {
        try {
            $sql = "SELECT * FROM story_categories ORDER BY nom";
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getAllCategories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Nouvelle méthode pour compter les catégories (contourne le problème findOne)
     * @return int Nombre de catégories
     */
    public function countCategories(): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM story_categories";
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Erreur countCategories: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère une catégorie par son ID (utilise les méthodes existantes qui fonctionnent)
     * @param int $id ID de la catégorie
     * @return array|false Données de la catégorie ou false si non trouvée
     */
    public function getCategoryById(int $id) {
        $sql = "SELECT * FROM story_categories WHERE id = :id";
        return $this->findOne($sql, ['id' => $id]);
    }
    
    /**
     * Nouvelle méthode pour ajouter une catégorie (contourne le problème execute)
     * @param string $nom Nom de la catégorie
     * @return int ID de la catégorie créée
     * @throws Exception Si la catégorie existe déjà
     */
    public function addCategory(string $nom): int {
        // Vérifier si la catégorie existe déjà
        if ($this->categoryExists($nom)) {
            throw new Exception("La catégorie '{$nom}' existe déjà.");
        }
        
        try {
            $sql = "INSERT INTO story_categories (nom) VALUES (:nom)";
            $query = $this->db->prepare($sql);
            $query->execute(['nom' => $nom]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur addCategory: " . $e->getMessage());
            throw new Exception("Erreur lors de l'ajout de la catégorie.");
        }
    }
    
    /**
     * Nouvelle méthode pour vérifier si une catégorie existe
     * @param string $nom Nom de la catégorie
     * @return bool True si la catégorie existe
     */
    public function categoryExists(string $nom): bool {
        try {
            $sql = "SELECT id FROM story_categories WHERE nom = :nom";
            $query = $this->db->prepare($sql);
            $query->execute(['nom' => $nom]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result !== false;
        } catch (PDOException $e) {
            error_log("Erreur categoryExists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nouvelle méthode pour supprimer une catégorie
     * @param int $id ID de la catégorie
     * @return bool True si suppression réussie
     */
    public function deleteCategory(int $id): bool {
        try {
            $sql = "DELETE FROM story_categories WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur deleteCategory: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nouvelle méthode pour mettre à jour une catégorie
     * @param int $id ID de la catégorie
     * @param string $nom Nouveau nom
     * @return bool True si mise à jour réussie
     */
    public function updateCategory(int $id, string $nom): bool {
        try {
            $sql = "UPDATE story_categories SET nom = :nom WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['nom' => $nom, 'id' => $id]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur updateCategory: " . $e->getMessage());
            return false;
        }
    }
}