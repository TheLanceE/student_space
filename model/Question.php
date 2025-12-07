<?php
class Question {
    private $id;
    private $quiz_id;
    private $question_text;
    private $question_order;

    public function __construct($id, $quiz_id, $question_text, $question_order) {
        $this->id = $id;
        $this->quiz_id = $quiz_id;
        $this->question_text = $question_text;
        $this->question_order = $question_order;
    }

    // Getters and Setters
    public function getId() { return $this->id; }
    public function getQuizId() { return $this->quiz_id; }
    public function getQuestionText() { return $this->question_text; }
    public function getQuestionOrder() { return $this->question_order; }

    public function setQuizId($quiz_id) { $this->quiz_id = $quiz_id; }
    public function setQuestionText($question_text) { $this->question_text = $question_text; }
    public function setQuestionOrder($question_order) { $this->question_order = $question_order; }
}
?>