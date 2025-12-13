<?php

class Memory
{
    private int $id;
    private User $user;          // Association avec modèle User existant
    private int $score;
    private string $level;
    private \DateTimeImmutable $createdAt;
    private in $time;

    public function __construct(User $user, int $score, string $level)
    {
        $this->user = $user;
        $this->score = $score;
        $this->level = $level;
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters ---
    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

     public function getTime(): int
    {
        return $this->time;
    }

    // --- Setters ---
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

     public function setTime(int $time): void
    {
        $this->time = $time;
    }

    
}
