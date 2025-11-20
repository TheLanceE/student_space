<?php
class QuestionOption {
    private ?int $id;
    private ?int $question_id;
    private ?string $option_label;
    private ?string $option_text;
    private ?bool $is_correct;

    public function __construct(?int $id, ?int $question_id, ?string $option_label, ?string $option_text, ?bool $is_correct = false) {
        $this->id = $id;
        $this->question_id = $question_id;
        $this->option_label = $option_label;
        $this->option_text = $option_text;
        $this->is_correct = $is_correct;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function getQuestionId(): ?int { return $this->question_id; }
    public function getOptionLabel(): ?string { return $this->option_label; }
    public function getOptionText(): ?string { return $this->option_text; }
    public function getIsCorrect(): ?bool { return $this->is_correct; }

    public function setQuestionId(?int $question_id): void { $this->question_id = $question_id; }
    public function setOptionLabel(?string $option_label): void { $this->option_label = $option_label; }
    public function setOptionText(?string $option_text): void { $this->option_text = $option_text; }
    public function setIsCorrect(?bool $is_correct): void { $this->is_correct = $is_correct; }
}
?>