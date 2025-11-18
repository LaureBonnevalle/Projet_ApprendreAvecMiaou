<?php
/*require_once('models/ColoriageCategories.php');
require_once('models/Coloriages.php');*/

class ColoriageManager extends AbstractManager {
public function __construct()
    {
        parent::__construct();
    }

    public function create(Coloriages $coloring): bool {
        $query = $this->db->prepare("INSERT INTO coloring_sheets (categorie_dessin, dessin_DateHeure, fichier) VALUES (?, ?, ?)");
        return $query->execute([
            $coloring->getCategorieDessin(),
            $coloring->getDessinDateHeure()->format('Y-m-d H:i:s'),
            $coloring->getFichier()
        ]);
    }

    public function read(int $id): ?Coloriages {
        $query = $this->db->prepare("SELECT * FROM coloring_sheets WHERE id = ?");
        $query->execute([$id]);
        $data = $query->fetch();
        if ($data) {
            return new Coloriages($data['id'], $data['categorie_coloring'],  $data['url']);
        }
        return null;
    }

    public function update(Coloriages $coloring): bool {
        $query = $this->db->prepare("UPDATE coloring_sheets SET categorie_coloring = ?, dessin_DateHeure = ?, fichier = ? WHERE id = ?");
        return $query->execute([
            $coloring->getCategorieColoriage(),
            $coloring->getUrl(),
            $coloring->getId()
        ]);
    }

    public function delete(Coloriages $coloring): bool {
        $query = $this->db->prepare("DELETE FROM coloring_sheets WHERE id = ?");
        return $query->execute([$coloring->getId()]);
    }
    
    public function getAllCategoriesColoriages(): array {
        $query = $this->db->prepare("SELECT * FROM coloring_categories");
        $query->execute([]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
        
        
    }
     
    
    public function getAllColoriagesByCategorie(int $categorieId): array { 
        $sql = $this->db->prepare('SELECT c.* FROM coloring_sheets c
        INNER JOIN coloring_categories cc ON c.categorie_coloring = cc.id
        WHERE cc.id = :categorie_id');
    
        $sql->execute(['categorie_id' => $categorieId]);
        $coloring_sheets = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $coloring_sheets;
    }
    
    public function jSON($categorieId) {
    $coloringManager = new ColoriageManager();
    $coloring_sheets = $this->getAllColoriagesByCategorie($categorieId);

    header('Content-Type: application/json');
    echo json_encode($coloring_sheets);
    exit;
    }
    
    public function addColoriage(array $data): void
{
    $sql = $this->db->prepare('INSERT INTO coloring_sheets (name, description, url, categorie_coloring) VALUES (:name, :description, :url, :categorie_coloring)');
    $sql->execute([
        'name' => $data['name'],
        'description' => $data['description'],
        'url' => $data['url'],
        'categorie_coloring' => $data['categorie_coloring']
    ]);
}

public function deleteColoriageById(int $id): void
{
    $sql = $this->db->prepare("DELETE FROM coloring_sheets WHERE id = :id");
    $sql->execute(['id' => $id]);
}

public function existByUrl(string $url): bool
{
    $sql = $this->db->prepare("SELECT COUNT(*) FROM coloring_sheets WHERE url = :url");
    $sql->execute(['url' => $url]);
    return $sql->fetchColumn() > 0;
}

}
