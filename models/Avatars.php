<?php

class Avatars {
    private ?int $id;
    private string $name;
    private string $url;
    private string $description;
    private string $caracteristique;
    private string $qualite;
    private string $urlMini; // correspond à la colonne url_mini

    public function __construct(
        ?int $id,
        string $name,
        string $url,
        string $description,
        string $caracteristique,
        string $qualite,
        string $urlMini
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
        $this->caracteristique = $caracteristique;
        $this->qualite = $qualite;
        $this->urlMini = $urlMini;
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

    public function getUrlMini(): string {
        return $this->urlMini;
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

    public function setUrlMini(string $urlMini): void {
        $this->urlMini = $urlMini;
    }
}
