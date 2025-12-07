<?php
class QuestionOption {
    private $id;
    private $question_id;
    private $option_label;
    private $option_text;
    private $is_correct;

    public function __construct($id, $question_id, $option_label, $option_text, $is_correct = false) {
        $this->id = $id;
        $this->question_id = $question_id;
        $this->option_label = $option_label;
        $this->option_text = $option_text;
        $this->is_correct = $is_correct;
    }

    // Getters and Setters
    public function getId() { return $this->id; }
    public function getQuestionId() { return $this->question_id; }
    public function getOptionLabel() { return $this->option_label; }
    public function getOptionText() { return $this->option_text; }
    public function getIsCorrect() { return $this->is_correct; }

    public function setQuestionId($question_id) { $this->question_id = $question_id; }
    public function setOptionLabel($option_label) { $this->option_label = $option_label; }
    public function setOptionText($option_text) { $this->option_text = $option_text; }
    public function setIsCorrect($is_correct) { $this->is_correct = $is_correct; }
}
?>