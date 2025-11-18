<?php

class Messages {
    private ?int $id;
    private DateTime $receptedDate;
    private string $firstname;
    private string $email;
    private string $subject;
    private string $content;
    private int    $statut;
    

    public function __construct(?int $id, DateTime $receptedDate, string $firstname, string $email, string $subject, string $content, int $statut) {
        $this->id = $id;
        $this->receptedDate = $receptedDate;
        $this->firstname = $firstname;
        $this->email =$email;
        $this->subject = $subject;
        $this->content = $content;
        $this->statut = $statut;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getfirstname(): string {
        return $this->firstname;
    }

    public function getSubject(): string {
        return $this->subject;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getReceptedDate(): DateTime {
        return $this->receptedDate;
    }
    public function getEmail(): string{
        return $this->email;
    }
    public function getStatut(): int {
        return $this->statut;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setFirstname(string $firstname): void {
        $this->firstname = $firstname;
    }

    public function setSubject(string $subject): void {
        $this->subject = $subject;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setReceptedDate(DateTime $receptedDate): void {
        $this->receptedDate = $receptedDate;
    }
    
    public function setEmail(string $email): void{
        $this->email = $email;
    }
    public function setStatut(int $statut): void{
        $this->statut = $statut;
    }
    
}
?>

