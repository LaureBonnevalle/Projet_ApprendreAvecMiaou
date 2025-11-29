<?php

class ColoringManager extends AbstractManager {

    /**
     * Crée un nouveau coloring sheet
     */
    public function create(ColoringSheet $sheet): bool {
        $stmt = $this->db->prepare("
            INSERT INTO coloring_sheets (name, description, url, categorie_coloring, thumbnail_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $sheet->getName(),
            $sheet->getDescription(),
            $sheet->getUrl(),
            $sheet->getCategorieColoring(),
            $sheet->getThumbnailUrl()
        ]);
    }

    /**
     * Lit un coloring sheet par son ID
     */
    public function read(int $id): ?ColoringSheet {
        $stmt = $this->db->prepare("SELECT * FROM coloring_sheets WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new ColoringSheet(
            (int)$row['id'],
            $row['categorie_coloring'] !== null ? (int)$row['categorie_coloring'] : null,
            $row['name'],
            $row['description'],
            $row['url'],
            $row['thumbnail_url'] ?? null
        );
    }

    /**
     * Met à jour un coloring sheet
     */
    public function update(ColoringSheet $sheet): bool {
        $stmt = $this->db->prepare("
            UPDATE coloring_sheets
            SET name = ?, description = ?, url = ?, categorie_coloring = ?, thumbnail_url = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $sheet->getName(),
            $sheet->getDescription(),
            $sheet->getUrl(),
            $sheet->getCategorieColoring(),
            $sheet->getThumbnailUrl(),
            $sheet->getId()
        ]);
    }

    /**
     * Supprime un coloring sheet
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM coloring_sheets WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Récupère toutes les catégories
     */
    public function getAllCategories(): array {
        $stmt = $this->db->prepare("SELECT id, name, description FROM coloring_categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les coloriages d'une catégorie
     */
    public function getAllByCategorie(int $categorieId): array {
        $stmt = $this->db->prepare("
            SELECT cs.id, cs.name, cs.description, cs.url, cs.thumbnail_url, cs.categorie_coloring
            FROM coloring_sheets cs
            WHERE cs.categorie_coloring = :categorie_id
            ORDER BY cs.name ASC
        ");
        $stmt->execute(['categorie_id' => $categorieId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les coloriages
     */
    public function getAll(): array {
        $stmt = $this->db->prepare("
            SELECT cs.id, cs.name, cs.description, cs.url, cs.thumbnail_url, cs.categorie_coloring
            FROM coloring_sheets cs
            ORDER BY cs.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les coloriages en JSON
     */
    public function toJSONByCategorie(int $categorieId): void {
        if (ob_get_level()) {
            ob_end_clean();
        }
        $sheets = $this->getAllByCategorie($categorieId);
        header('Content-Type: application/json');
        echo json_encode($sheets);
        exit;
    }

    /**
     * Vérifie si un coloriage existe par son URL
     */
    public function existsByUrl(string $url): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coloring_sheets WHERE url = :url");
        $stmt->execute(['url' => $url]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Génère tous les thumbnails à partir des PDFs
     */
    public function generateAllThumbnails(): array {
        $stmt = $this->db->query("SELECT id, url, thumbnail_url FROM coloring_sheets");
        $colorings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $generated = [];
        $errors = [];

        foreach ($colorings as $coloring) {
            // Si le thumbnail existe déjà, on skip
            if (!empty($coloring['thumbnail_url']) && file_exists(__DIR__ . '/../public/' . $coloring['thumbnail_url'])) {
                continue;
            }

            $pdfPath = __DIR__ . '/../public/' . $coloring['url'];
            
            // Créer le dossier s'il n'existe pas
            $thumbnailDir = __DIR__ . '/../public/assets/img/coloringSheets/thumbnails';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $thumbnailPath = $thumbnailDir . '/coloring_' . $coloring['id'] . '.png';

            if (!file_exists($pdfPath)) {
                $errors[] = "PDF introuvable pour ID {$coloring['id']}: {$pdfPath}";
                continue;
            }

            try {
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($pdfPath . '[0]'); // Première page
                $imagick->setImageFormat('png');
                $imagick->thumbnailImage(800, 0); // Largeur 800px, hauteur auto
                $imagick->writeImage($thumbnailPath);
                $imagick->clear();
                $imagick->destroy();

                $generated[] = $thumbnailPath;

                // Mise à jour de la BDD
                $relativePath = "assets/img/coloringSheets/thumbnails/coloring_{$coloring['id']}.png";
                $update = $this->db->prepare("UPDATE coloring_sheets SET thumbnail_url = :thumb WHERE id = :id");
                $update->execute([
                    'thumb' => $relativePath,
                    'id' => $coloring['id']
                ]);

                error_log("✅ Thumbnail créé pour ID {$coloring['id']}");

            } catch (Exception $e) {
                $errors[] = "Erreur thumbnail ID {$coloring['id']}: " . $e->getMessage();
                error_log("❌ Erreur thumbnail ID {$coloring['id']}: " . $e->getMessage());
            }
        }

        return [
            'generated' => $generated,
            'errors' => $errors,
            'total' => count($colorings),
            'success' => count($generated)
        ];
    }

    /**
     * Génère le thumbnail pour un seul coloriage
     */
    public function generateThumbnail(int $id): bool {
        $coloring = $this->read($id);
        if (!$coloring) return false;

        $pdfPath = __DIR__ . '/../public/' . $coloring->getUrl();
        
        // Créer le dossier s'il n'existe pas
        $thumbnailDir = __DIR__ . '/../public/assets/img/coloringSheets/thumbnails';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        $thumbnailPath = $thumbnailDir . '/coloring_' . $id . '.png';

        if (!file_exists($pdfPath)) {
            error_log("❌ PDF introuvable: {$pdfPath}");
            return false;
        }

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('png');
            $imagick->thumbnailImage(800, 0);
            $imagick->writeImage($thumbnailPath);
            $imagick->clear();
            $imagick->destroy();

            // Mise à jour de la BDD
            $relativePath = "assets/img/coloringSheets/thumbnails/coloring_{$id}.png";
            $update = $this->db->prepare("UPDATE coloring_sheets SET thumbnail_url = :thumb WHERE id = :id");
            $update->execute([
                'thumb' => $relativePath,
                'id' => $id
            ]);

            error_log("✅ Thumbnail créé pour ID {$id}");
            return true;

        } catch (Exception $e) {
            error_log("❌ Erreur thumbnail ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un thumbnail
     */
    public function deleteThumbnail(int $id): bool {
        $coloring = $this->read($id);
        if (!$coloring || !$coloring->getThumbnailUrl()) return false;

        $thumbnailPath = __DIR__ . '/../public/' . $coloring->getThumbnailUrl();
        
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }

        // Mettre à NULL dans la BDD
        $update = $this->db->prepare("UPDATE coloring_sheets SET thumbnail_url = NULL WHERE id = :id");
        return $update->execute(['id' => $id]);
    }

    /**
     * Compte le nombre de coloriages par catégorie
     */
    public function countByCategorie(int $categorieId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coloring_sheets WHERE categorie_coloring = :categorie_id");
        $stmt->execute(['categorie_id' => $categorieId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compte le nombre total de coloriages
     */
    public function countAll(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM coloring_sheets");
        return (int)$stmt->fetchColumn();
    }
}