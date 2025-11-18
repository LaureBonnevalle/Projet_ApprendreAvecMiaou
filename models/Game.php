<?php

class Jeux {
    private ?int $id;
    private string $jeux_nom;
    private int $jeux_categorie;
    private int $jeux_pedago;

    public function __construct(?int $id, string $jeux_nom, int $jeux_categorie, int $jeux_pedago) {
        $this->id = $id;
        $this->jeux_nom = $jeux_nom;
        $this->jeux_categorie = $jeux_categorie;
        $this->jeux_pedago = $jeux_pedago;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getJeuxNom(): string {
        return $this->jeux_nom;
    }

    public function getJeuxCategorie(): int {
        return $this->jeux_categorie;
    }

    public function getJeuxPedago(): int {
        return $this->jeux_pedago;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setJeuxNom(string $jeux_nom): void {
        $this->jeux_nom = $jeux_nom;
    }

    public function setJeuxCategorie(int $jeux_categorie): void {
        $this->jeux_categorie = $jeux_categorie;
    }

    public function setJeuxPedago(int $jeux_pedago): void {
        $this->jeux_pedago = $jeux_pedago;
    }
}
?>
