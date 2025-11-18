<?php

class CategorieGamesManager extends AbstarctManager {
    public function __construct()
    {
        parent::__construct();
    }

    public function create(CategorieGames $categorie): bool {
        $query = $this->db->prepare("INSERT INTO categorie_games (categorie_name, categorie_description) VALUES (?, ?)");
        return $query->execute([
            $categorie->getCategorieName(),
            $categorie->getCategorieDescription()
        ]);
    }

    public function read(int $id): ?CategorieGames {
        $query = $this->db->prepare("SELECT * FROM categorie_games WHERE id = ?");
        $query->execute([$id]);
        $data = $query->fetch();
        if ($data) {
            return new CategorieGames($data['id'], $data['categorie_name'], $data['categorie_description']);
        }
        return null;
    }

    public function update(CategorieGames $categorie): bool {
        $query = $this->db->prepare("UPDATE categorie_games SET categorie_name = ?, categorie_description = ? WHERE id = ?");
        return $query->execute([
            $categorie->getCategorieName(),
            $categorie->getCategorieDescription(),
            $categorie->getId()
        ]);
    }

    public function delete(CategorieGames $categorie): bool {
        $query = $this->db->prepare("DELETE FROM categorie_games WHERE id = ?");
        return $query->execute([$categorie->getId()]);
    }
}
