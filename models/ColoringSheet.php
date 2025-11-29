<?php

class ColoringSheet {
    private ?int $id;
    private ?int $categorie_coloring; // nullable selon la BDD
    private string $name;
    private string $description;
    private string $url;
    private ?string $thumbnail_url;

    public function __construct(
        ?int $id,
        ?int $categorie_coloring,
        string $name,
        string $description,
        string $url,
        ?string $thumbnail_url = null
    ) {
        $this->id = $id;
        $this->categorie_coloring = $categorie_coloring;
        $this->name = $name;
        $this->description = $description;
        $this->thumbnail_url = $thumbnail_url;
    }

    public function getId(): ?int { return $this->id; }
    public function getCategorieColoring(): ?int { return $this->categorie_coloring; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getUrl(): string { return $this->url; }
    public function getThumbnailUrl(): ?string { return $this->thumbnail_url; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setCategorieColoring(?int $categorie_coloring): void { $this->categorie_coloring = $categorie_coloring; }
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setUrl(string $url): void { $this->url = $url; }
}   public function setThumbnailUrl(?string $thumbnail_url): void { $this->thumbnail_url = $thumbnail_url; }
