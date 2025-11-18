<?php

class Colorings {
    private ?int $id;
    private int $categorie_dessin;
    private DateTime $dessin_DateHeure;
    private string $fichier;

    public function __construct(?int $id, int $categorie_dessin, DateTime $dessin_DateHeure, string $fichier) {
        $this->id = $id;
        $this->categorie_dessin = $categorie_dessin;
        $this->dessin_DateHeure = $dessin_DateHeure;
        $this->fichier = $fichier;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCategorieDessin(): int {
        return $this->categorie_dessin;
    }

    public function getDessinDateHeure(): DateTime {
        return $this->dessin_DateHeure;
    }

    public function getFichier(): string {
        return $this->fichier;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setCategorieDessin(int $categorie_dessin): void {
        $this->categorie_dessin = $categorie_dessin;
    }

    public function setDessinDateHeure(DateTime $dessin_DateHeure): void {
        $this->dessin_DateHeure = $dessin_DateHeure;
    }

    public function setFichier(string $fichier): void {
        $this->fichier = $fichier;
    }
}
?>
