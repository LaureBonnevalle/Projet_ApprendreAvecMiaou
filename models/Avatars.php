<?php

class Avatars {
    private ?int $id;
    private string $name;
    private string $url;
    private string $description;
    private string $caracteristique;
    private string $qualite;

    public function __construct(?int $id, string $name, string $url, string $description, string $caracteristique, string $qualite) {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
        $this->caracteristique = $caracteristique;
        $this->qualite = $qualite;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getCaracteristique(): string {
        return $this->caracteristique;
    }

    public function getQualite(): string {
        return $this->qualite;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setUrl(string $url): void {
        $this->url = $url;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setCaracteristique(string $caracteristique): void {
        $this->caracteristique = $caracteristique;
    }

    public function setQualite(string $qualite): void {
        $this->qualite = $qualite;
    }
}
