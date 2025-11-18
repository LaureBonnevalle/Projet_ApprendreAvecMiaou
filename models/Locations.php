<?php

class Locationx {
    private ?int $id;
    private string $location_name;
    private string $location_description;
    private string $url;
    private string $alt;

    public function __construct(?int $id, string $location_name, string $location_description) {
        $this->id = $id;
        $this->location_name = $location_name;
        $this->location_description = $location_description;
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
}
?>
