<?php
class Quiz {
    private $id;
    private $title;
    private $category;
    private $grade;
    private $passing_grade;
    private $description;
    private $status;
    private $time_limit;
    private $created_at;

    public function __construct($id, $title, $category, $passing_grade, $description, $status, $grade, $time_limit = null, $created_at = null) {
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
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getCategory() { return $this->category; }
    public function getGrade() { return $this->grade; }
    public function getPassingGrade() { return $this->passing_grade; }
    public function getDescription() { return $this->description; }
    public function getStatus() { return $this->status; }
    public function getTimeLimit() { return $this->time_limit; }
    public function getCreatedAt() { return $this->created_at; }

    public function setId($id) { $this->id = $id; }
    public function setTitle($title) { $this->title = $title; }
    public function setCategory($category) { $this->category = $category; }
    public function setGrade($grade) { $this->grade = $grade; }
    public function setPassingGrade($passing_grade) { $this->passing_grade = $passing_grade; }
    public function setDescription($description) { $this->description = $description; }
    public function setStatus($status) { $this->status = $status; }
    public function setTimeLimit($time_limit) { $this->time_limit = $time_limit; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
}
?>