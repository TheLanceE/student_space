<?php
class Quiz {
    private ?int $id;
    private ?string $title;
    private ?string $category;
    private ?string $grade;
    private ?int $passing_grade;
    private ?string $description;
    private ?string $status;
    private ?int $time_limit;
    private ?string $created_at;

    public function __construct(?int $id, ?string $title, ?string $category, ?int $passing_grade, ?string $description, ?string $status, ?string $grade, ?int $time_limit = null, ?string $created_at = null) {
        $this->id = $id;
        $this->title = $title;
        $this->category = $category;
        $this->passing_grade = $passing_grade;
        $this->description = $description;
        $this->status = $status;
        $this->grade = $grade;
        $this->time_limit = $time_limit;
        $this->created_at = $created_at;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function getCategory(): ?string { return $this->category; }
    public function getGrade(): ?string { return $this->grade; }
    public function getPassingGrade(): ?int { return $this->passing_grade; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): ?string { return $this->status; }
    public function getTimeLimit(): ?int { return $this->time_limit; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setTitle(?string $title): void { $this->title = $title; }
    public function setCategory(?string $category): void { $this->category = $category; }
    public function setGrade(?string $grade): void { $this->grade = $grade; }
    public function setPassingGrade(?int $passing_grade): void { $this->passing_grade = $passing_grade; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setStatus(?string $status): void { $this->status = $status; }
    public function setTimeLimit(?int $time_limit): void { $this->time_limit = $time_limit; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}
?>