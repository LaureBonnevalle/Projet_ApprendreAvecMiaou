<?php

require_once('managers/ColoringManager.php');
require_once('managers/ColoringCategoriesManager.php');




class ColoringCategories {
    private ?int $id;
    private string $categorie_name;
    private string $categorie_description;

    public function __construct(?int $id, string $categorie_name, string $categorie_description) {
        $this->id = $id;
        $this->categorie_name = $categorie_name;
        $this->categorie_description = $categorie_description;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCategorieName(): string {
        return $this->categorieName;
    }

    public function getCategorieDescription(): string {
        return $this->categorieDescription;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setCategorieName(string $categorieName): void {
        $this->categorieName = $categorieName;
    }

    public function setCategorieDescription(string $categorieDescription): void {
        $this->categorieDescription = $categorieDescription;
    }
}
?>
