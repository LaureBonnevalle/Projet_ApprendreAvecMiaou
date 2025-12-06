<?php

class Contacts {
    private int $id;
    private string $receptedDate; 
    private string $firstname;
    private string $email;
    private string $subject;
    private string $content;
    private int $statut;

    // Getter et setter : id
    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    // Getter et setter : receptedDate
    public function getReceptedDate(): ?string {
        return $this->receptedDate;
    }
    public function setReceptedDate(?string $receptedDate): void {
        $this->receptedDate = $receptedDate;
    }

    // Getter et setter : firstname
    public function getFirstname(): ?string {
        return $this->firstname;
    }
    public function setFirstname(?string $firstname): void {
        $this->firstname = $firstname;
    }

    // Getter et setter : email
    public function getEmail(): ?string {
        return $this->email;
    }
    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    // Getter et setter : subject
    public function getSubject(): ?string {
        return $this->subject;
    }
    public function setSubject(?string $subject): void {
        $this->subject = $subject;
    }

    // Getter et setter : content
    public function getContent(): ?string {
        return $this->content;
    }
    public function setContent(?string $content): void {
        $this->content = $content;
    }

    // Getter et setter : statut
    public function getStatut(): ?int {
        return $this->statut;
    }
    public function setStatut(?int $statut): void {
        $this->statut = $statut;
    }
}
