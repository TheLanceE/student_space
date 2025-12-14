<?php
// generate_quiz.php
require_once 'config.php';
require_once 'QuizGenerator.php';

header('Content-Type: application/json');

// Get POST data
$subject = $_POST['subject'] ?? 'General';
$topic = $_POST['topic'] ?? 'General Knowledge';
$difficulty = $_POST['difficulty'] ?? 'medium';
$numQuestions = $_POST['num_questions'] ?? 5;

// Validate
$numQuestions = min(max(intval($numQuestions), 1), 20); // Limit to 20 questions

try {
    $generator = new QuizGenerator();
    $result = $generator->generateQuestions($subject, $topic, $difficulty, $numQuestions);
    
    // Extract the actual quiz from response
    if (isset($result['choices'][0]['message']['content'])) {
        $content = json_decode($result['choices'][0]['message']['content'], true);
        echo json_encode([
            'success' => true,
            'quiz' => $content['quiz'] ?? $content
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to generate questions'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>