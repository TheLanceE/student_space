<?php
class Question {
    private ?int $id;
    private ?int $quiz_id;
    private ?string $question_text;
    private ?int $question_order;

    public function __construct(?int $id, ?int $quiz_id, ?string $question_text, ?int $question_order) {
        $this->id = $id;
        $this->quiz_id = $quiz_id;
        $this->question_text = $question_text;
        $this->question_order = $question_order;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function getQuizId(): ?int { return $this->quiz_id; }
    public function getQuestionText(): ?string { return $this->question_text; }
    public function getQuestionOrder(): ?int { return $this->question_order; }

    public function setQuizId(?int $quiz_id): void { $this->quiz_id = $quiz_id; }
    public function setQuestionText(?string $question_text): void { $this->question_text = $question_text; }
    public function setQuestionOrder(?int $question_order): void { $this->question_order = $question_order; }
}
?>