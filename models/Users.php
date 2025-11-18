<?php

class Users {
    private int $id;
    private string $email;
    private string $password;
    private string $firstname;
    private ?int $age;
    private int $avatar;
    private int $newsletter;
    private int $role;
    private int $statut;
    private string $createdAt;

    /*public function __construct(?int $id, string $email, string $password, string $firstname, ?int $age, int $avatar, ?int $newsletter, ?string $statut, string $cle, ?int $actif, DateTime $createdAt ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->avatar = $avatar;
        $this->newsletter = $newsletter;
        $this->statut = $statut;
        $this->cle = $cle;
        $this->actif = $actif;
        $this->createdAt = $createdAt;
    }*/

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getFirstname(): string {
        return $this->firstname;
    }

    public function getAge(): ?int {
        return $this->age;
    }

    public function getAvatar(): int {
        return $this->avatar;
    }


    public function getNewsletter(): ?int {
        return $this->newsletter;
    }
    
    public function getStatut(): ?int {
        return $this->statut;
    }

    public function getRole(): int {
        return $this->role;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function setFirstname(string $firstname): void {
        $this->firstname = $firstname;
    }

    public function setAge(?int $age): void {
        $this->age = $age;
    }

    public function setAvatar(int $avatar): void {
        $this->avatar = $avatar;
    }

   

    public function setNewsletter(?int $newsletter): void {
        $this->newsletter = $newsletter;
    }
    
   
    public function setStatut(?int $statut): void {
        $this->statut = $statut;
    }

    public function setRole(int $role): void {
        $this->role = $role;
    }
    
    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    } 
}
?>
