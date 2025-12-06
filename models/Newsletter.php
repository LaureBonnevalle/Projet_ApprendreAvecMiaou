<?php

class Newsletter {
    private ?int $id;
    private string $firstname;
    private string $email;

    public function __construct(?int $id, string $email, string $firstname) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->email = $email;
    }

    // Getter pour id
    public function getId(): ?int {
        return $this->id;
    }

    // Setter pour id
    public function setId(?int $id): void {
        $this->id = $id;
    }

    // Getter pour email
    public function getEmail(): string {
        return $this->email;
    }

    // Setter pour email
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    // Getter pour firstname
    public function getFirstname(): string {
        return $this->firstname;
    }

    // Setter pour firstname
    public function setFirstname(string $firstname): void {
        $this->firstname = $firstname;
    }
}
?>
