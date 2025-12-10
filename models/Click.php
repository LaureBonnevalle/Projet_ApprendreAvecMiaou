<?php

class Click
{
    private ?int $id = null;
    private User $user;        // relation vers un objet User
    private int $score;
    private string $level;     // 'facile', 'intermediaire', 'difficile'
    private DateTime $createdAt;

    
    public function __construct(
        ?int $id,
        User $user,
        int $score,
        string $level,
        DateTime $createdAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->score = $score;
        $this->level = $level;
        $this->createdAt = $createdAt;
    }

   
    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getScore(): int { return $this->score; }
    public function getLevel(): string { return $this->level; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setUser(User $user): void { $this->user = $user; }
    public function setScore(int $score): void { $this->score = $score; }
    public function setLevel(string $level): void { $this->level = $level; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }
}
