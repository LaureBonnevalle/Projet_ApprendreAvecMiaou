<?php

class Characters {
    private ?int $id;
    private string $character_name;
    private string $character_description;
    private string $url ="";
    private string $alt ="";

    public function __construct(?int $id, string $character_name, string $character_description, string $url ="", string $alt ="") 
    
    {
        $this->id = $id;
        $this->character_name = $character_name;
        $this->character_description = $character_description;
        $this->url = $url;
        $this->alt = $alt;
        
    }

    // Getter pour id
    public function getId(): ?int {
        return $this->id;
    }

    // Setter pour id
    public function setId(?int $id): void {
        $this->id = $id;
    }

    // Getter pour character_name
    public function getCharacterName(): string {
        return $this->character_name;
    }
    public function getUrl(): string {
        return $this->url;
    }
    public function getAlt(): string {
        return $this->alt;
    }

    // Setter pour character_name
    public function setCharacterName(string $character_name): void {
        $this->character_name = $character_name;
    }

    // Getter pour character_description
    public function getCharacterDescription(): string {
        return $this->character_description;
    }

    // Setter pour character_description
    public function setCharacterDescription(string $character_description): void {
        $this->character_description = $character_description;
    }
    public function setUrl(string $url): void {
        $this->url = $url;
    }
    public function setalt(string $alt): void {
        $this->alt = $alt;
    }

    // Méthode utile pour créer un character depuis un array (comme celui de la BDD)
    public static function fromArray(array $data): self {
        return new self(
            $data['id'] ?? 0,
            $data['character_name'] ?? '', // Correspond à votre colonne
            $data['character_description'] ?? '', // Correspond à votre colonne
            $data['url'] ?? '',
            $data['alt'] ?? ''
        );
    }
    
    // Méthode pour convertir en array
    public function toArray(): array {
        return [
            'id' => $this->id,
            'character_name' => $this->character_name,
            'character_description' => $this->character_description,
            'url' => $this->url,
            'alt' => $this->alt
        ];
    }
}
?>
