<?php

class Locations {
    private ?int $id;
    private string $location_name;
    private string $location_description;
    private string $url="";
    private string $alt="";

    public function __construct(?int $id, string $location_name, string $location_description, string $url, string $alt) {
        $this->id = $id;
        $this->location_name = $location_name;
        $this->location_description = $location_description;
        $this->url = $url;
        $this->alt = $alt;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLocationName(): string {
        return $this->location_name;
    }

    public function getLocationDescription(): string {
        return $this->location_description;
    }
    
    public function getUrl(): string {
        return $this->url;
    }
    
    public function getAlt(): string {
        return $this->alt;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setLocationName(string $location_name): void {
        $this->location_name = $location_name;
    }

    public function setLocationDescription(string $location_description): void {
        $this->location_description = $location_description;
    }
    
    public function setUrl(string $url): void {
        $this->url = $url;
    }
    
    public function setAlt(string $alt): void {
        $this->alt = $alt;
    }
    // Méthode utile pour créer un location depuis un array (comme celui de la BDD)
    public static function fromArray(array $data): self {
        return new self(
            $data['id'] ?? 0,
            $data['location_name'] ?? '', // Correspond à votre colonne
            $data['location_description'] ?? '', // Correspond à votre colonne
            $data['url'] ?? '',
            $data['alt'] ?? ''
        );
    }
    
    // Méthode pour convertir en array
    public function toArray(): array {
        return [
            'id' => $this->id,
            'location_name' => $this->location_name,
            'location_description' => $this->location_description,
            'url' => $this->url,
            'alt' => $this->alt
        ];
    }
}
?>
