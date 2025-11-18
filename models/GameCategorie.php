<?php

class CategorieJeux {
    private ?int $id;
    private string $categorie_name;
    private int $categorie_description;

    public function __construct(?int $id, string $categorie_name, int $categorie_description) {
        $this->id = $id;
        $this->categorie_name = $categorie_name;
        $this->categorie_description = $categorie_description;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCategorieName(): string {
        return $this->categorie_name;
    }

    public function getCategorieDescription(): int {
        return $this->categorie_description;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setCategorieName(string $categorie_name): void {
        $this->categorie_name = $categorie_name;
    }

    public function setCategorieDescription(int $categorie_description): void {
        $this->categorie_description = $categorie_description;
    }
}
?>
