<?php

class ColoringManager extends AbstractManager {

    public function create(ColoringSheet $sheet): bool {
        $stmt = $this->db->prepare("
            INSERT INTO coloring_sheets (name, description, url, categorie_coloring)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $sheet->getName(),
            $sheet->getDescription(),
            $sheet->getUrl(),
            $sheet->getCategorieColoring()
        ]);
    }

    public function read(int $id): ?ColoringSheet {
        $stmt = $this->db->prepare("SELECT id, name, description, url, categorie_coloring FROM coloring_sheets WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new ColoringSheet(
            (int)$row['id'],
            $row['categorie_coloring'] !== null ? (int)$row['categorie_coloring'] : null,
            $row['name'],
            $row['description'],
            $row['url']
        );
    }

    public function update(ColoringSheet $sheet): bool {
        $stmt = $this->db->prepare("
            UPDATE coloring_sheets
            SET name = ?, description = ?, url = ?, categorie_coloring = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $sheet->getName(),
            $sheet->getDescription(),
            $sheet->getUrl(),
            $sheet->getCategorieColoring(),
            $sheet->getId()
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM coloring_sheets WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllCategories(): array {
        $stmt = $this->db->prepare("SELECT id, name, description FROM coloring_categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByCategorie(int $categorieId): array {
        $stmt = $this->db->prepare("
            SELECT cs.id, cs.name, cs.description, cs.url, cs.categorie_coloring
            FROM coloring_sheets cs
            WHERE cs.categorie_coloring = :categorie_id
        ");
        $stmt->execute(['categorie_id' => $categorieId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toJSONByCategorie(int $categorieId): void {
        if (ob_get_level()) {
            ob_end_clean();
        }
        $sheets = $this->getAllByCategorie($categorieId);
        header('Content-Type: application/json');
        echo json_encode($sheets);
        exit;
    }

    public function existsByUrl(string $url): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coloring_sheets WHERE url = :url");
        $stmt->execute(['url' => $url]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
