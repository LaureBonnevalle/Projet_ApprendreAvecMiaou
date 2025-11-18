<?php

class Newsletter {
    private ?int $id;
    private string $news_email;
    private string $news_prenom;

    public function __construct(?int $id, string $news_email, string $news_prenom) {
        $this->id = $id;
        $this->news_email = $news_email;
        $this->news_prenom = $news_prenom;
    }

    // Getter pour id
    public function getId(): ?int {
        return $this->id;
    }

    // Setter pour id
    public function setId(?int $id): void {
        $this->id = $id;
    }

    // Getter pour news_email
    public function getNewsEmail(): string {
        return $this->news_email;
    }

    // Setter pour news_email
    public function setNewsEmail(string $news_email): void {
        $this->news_email = $news_email;
    }

    // Getter pour news_prenom
    public function getNewsPrenom(): string {
        return $this->news_prenom;
    }

    // Setter pour news_prenom
    public function setNewsPrenom(string $news_prenom): void {
        $this->news_prenom = $news_prenom;
    }
}
?>
