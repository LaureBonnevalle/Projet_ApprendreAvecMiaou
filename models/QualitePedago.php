<?php

class QualitePedago {
    private int $id;
    private string $qualite_name;
    private string $qualite_description;

    public function __construct(int $id, string $qualite_name, string $qualite_description) {
        $this->id = $id;
        $this->qualite_name = $qualite_name;
        $this->qualite_description = $qualite_description;
    }

    // Getter pour id
    public function getId(): int {
        return $this->id;
    }

    // Setter pour id
    public function setId(int $id): void {
        $this->id = $id;
    }

    // Getter pour qualite_name
    public function getQualiteName(): string {
        return $this->qualite_name;
    }

    // Setter pour qualite_name
    public function setQualiteName(string $qualite_name): void {
        $this->qualite_name = $qualite_name;
    }

    // Getter pour qualite_description
    public function getQualiteDescription(): string {
        return $this->qualite_description;
    }

    // Setter pour qualite_description
    public function setQualiteDescription(string $qualite_description): void {
        $this->qualite_description = $qualite_description;
    }
}
?>
