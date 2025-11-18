<?php

class Characters {
    private ?int $id;
    private string $perso_name;
    private string $perso_description;
    private string $url;
    private string $alt;

    public function __construct(?int $id, string $perso_name, string $perso_description) {
        $this->id = $id;
        $this->perso_name = $perso_name;
        $this->perso_description = $perso_description;
    }

    // Getter pour id
    public function getId(): ?int {
        return $this->id;
    }

    // Setter pour id
    public function setId(?int $id): void {
        $this->id = $id;
    }

    // Getter pour perso_name
    public function getPersoName(): string {
        return $this->perso_name;
    }
    public function getUrl(): string {
        return $this->url;
    }
    public function getAlt(): string {
        return $this->alt;
    }

    // Setter pour perso_name
    public function setPersoName(string $perso_name): void {
        $this->perso_name = $perso_name;
    }

    // Getter pour perso_description
    public function getPersoDescription(): string {
        return $this->perso_description;
    }

    // Setter pour perso_description
    public function setPersoDescription(string $perso_description): void {
        $this->perso_description = $perso_description;
    }
    public function setUrl(string $url): void {
        $this->url = $url;
    }
    public function setalt(string $alt): void {
        $this->alt = $alt;
    }
}
?>
