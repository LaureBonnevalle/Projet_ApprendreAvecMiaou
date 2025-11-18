<?php

class Stories {
    private ?int $id;
    private string $story_titre;
    private int $categorie;
    private int $character;
    private int $item;
    private int $location;
    private string $story_content;
    private string $url;
    private string $audio;

    public function __construct(?int $id, string $story_titre, int $categorie, int $character, int $item, int $location, string $story_content, string $audio) {
        $this->id = $id;
        $this->story_titre = $story_titre;
        $this->categorie = $categorie;
        $this->character = $character;
        $this->item = $item;
        $this->location = $location;
        $this->story_content = $story_content;
        $this->url = $url;
        $this->audio = $audio;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getStorieTitre(): string {
        return $this->story_titre;
    }

    public function getCategorie(): int {
        return $this->categorie;
    }

    public function getCharacter(): int {
        return $this->character;
    }

    public function getItem(): int {
        return $this->item;
    }

    public function getLocation(): int {
        return $this->location;
    }

    public function getStorieContent(): string {
        return $this->story_content;
    }

    public function getUrl(): string {
        return $this->url;
    }
    
    public function getAudio(): string {
        return $this->audio;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setStorieTitre(string $story_titre): void {
        $this->story_titre = $story_titre;
    }

    public function setCategorie(int $categorie): void {
        $this->categorie = $categorie;
    }

    public function setCharacter(int $character): void {
        $this->character = $character;
    }

    public function setItem(int $item): void {
        $this->item = $item;
    }

    public function setLocation(int $location): void {
        $this->location = $location;
    }

    public function setStorieContent(string $story_content): void {
        $this->story_content = $story_content;
    }

    public function setUrludio(string $url): void {
        $this->url = $url;
    }
    
    public function setAudio(string $audio): void {
        $this->audio = $audio;
    }
}
?>
