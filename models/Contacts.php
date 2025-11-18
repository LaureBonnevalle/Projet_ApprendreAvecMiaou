<?php

class Contacts {
 
    private $id;
    private $receptedDate; 
    private $firstname;
    private $email;
    private $subject;
    private $content;
    private $statut;

    // Getter and setteur : id
    public function getId() {
        return $this->id;
    }

    public function setId( $id): void {
        $this->id = $id;
    }

    // Getter and setteur : receptedDate
    public function getReceptedDate(): ?string {
        return $this->receptedDate;
    }

    public function setReceptedDate(?string $receptedDate): void {
        $this->receptedDate = $receptedDate;
    }

    // Getter and setteur : firstname
    public function getFirstname(): ?string {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void {
        $this->firstname = $firstname;
    }

    // Getter and setteur : email
    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    // Getter and setteur : content
    public function getContent(): ?string {
        return $this->content;
    }
    
    public function getSubject(): ?string {
        return $this->subject;
    }
    
    public function setSubject(?string $subject): void {
        $this->subject = $subject;
    }

    public function setContent(?string $content): void {
        $this->content = $content;
    }

    // Getter and setteur : statut
    public function getStatut(): ?int {
        return $this->statut;
    }

    public function setStatut(?int $statut): void {
        $this->statut = $statut;
    }
}