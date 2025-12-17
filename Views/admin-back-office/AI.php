<?php 
if(session_status() === PHP_SESSION_NONE) session_start(); 
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/AI.php';
require_once __DIR__ . '/../../Models/AIPredictions.php';
require_once __DIR__ . '/../../Models/Users.php';

$adminID = $_SESSION['userID'] ?? 1;
$students = Users::getStudents($pdo);

// Check for AI actions
$showFeedback = isset($_GET['view_feedback']) || isset($_SESSION['ai_feedback']);
$showPredictions = isset($_GET['view_predictions']) || isset($_SESSION['ai_predictions']);
$showAllPredictions = isset($_GET['view_all_predictions']) || isset($_SESSION['all_ai_predictions']);
$showDashboard = isset($_GET['ai_dashboard']) || isset($_SESSION['ai_students']);
$showPredictionsDashboard = isset($_GET['predictions_dashboard']) || isset($_SESSION['predictions_dashboard']);

// Get saved feedback if exists
$savedFeedback = $_SESSION['saved_feedback'] ?? [];

// Clear session messages
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
$formError = $_SESSION['form_error'] ?? '';
$predictionError = $_SESSION['prediction_error'] ?? '';
$selectedStudent = $_SESSION['selected_student'] ?? '';
$selectedPredictionStudent = $_SESSION['selected_prediction_student'] ?? '';

unset($_SESSION['success'], $_SESSION['error'], $_SESSION['form_error'], $_SESSION['prediction_error'], 
      $_SESSION['selected_student'], $_SESSION['selected_prediction_student']);

// Get AI feedback data if exists
$aiFeedback = $_SESSION['ai_feedback'] ?? null;
$aiPredictions = $_SESSION['ai_predictions'] ?? null;
$allAIPredictions = $_SESSION['all_ai_predictions'] ?? null;
$aiStudents = $_SESSION['ai_students'] ?? null;
$predictionsDashboard = $_SESSION['predictions_dashboard'] ?? null;

// Clear session data after display
unset($_SESSION['ai_feedback'], $_SESSION['ai_predictions'], $_SESSION['all_ai_predictions'], 
      $_SESSION['ai_students'], $_SESSION['predictions_dashboard'], $_SESSION['saved_feedback']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>AI Insights - Admin</title>
<link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background: #f8fafc;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
    min-height: 100vh;
}
.container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
}
.dashboard-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    color: white;
}
.ai-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-left: 5px solid #8b5cf6;
    transition: all 0.3s ease;
    height: 100%;
}
.ai-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}
.ai-feature-card {
    border-left: 5px solid #2563eb;
    text-align: center;
    cursor: pointer;
}
.ai-feature-card:hover {
    border-left-color: #8b5cf6;
}
.feedback-card {
    border-left: 5px solid #059669;
}
.prediction-card {
    border-left: 5px solid #f59e0b;
}
.ai-btn {
    background: #8b5cf6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.ai-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
}
.ai-btn-feedback {
    background: #059669;
}
.ai-btn-feedback:hover {
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
}
.ai-btn-predict {
    background: #f59e0b;
}
.ai-btn-predict:hover {
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}
.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-pattern {
    background: #dbeafe;
    color: #1e40af;
}
.badge-high-confidence {
    background: #d1fae5;
    color: #065f46;
}
.badge-medium-confidence {
    background: #fef3c7;
    color: #92400e;
}
.badge-low-confidence {
    background: #fee2e2;
    color: #991b1b;
}
.message-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: #dc2626;
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    font-weight: bold;
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
    font-size: 1rem;
    display: none;
}
.message-toast.success {
    background: #059669;
    box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3);
}
.confidence-bar {
    background: #e5e7eb;
    border-radius: 10px;
    height: 10px;
    margin: 10px 0;
    overflow: hidden;
}
.confidence-fill {
    height: 100%;
    background: #059669;
    border-radius: 10px;
}
.suggestion-item {
    background: #f3f4f6;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid #2563eb;
}
.prediction-item {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
}
.prediction-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2563eb;
}
.pattern-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin: 20px 0;
}
.stat-box {
    background: #f8fafc;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #e5e7eb;
}
.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
    color: #2563eb;
}
.stat-label {
    font-size: 0.9rem;
    color: #6b7280;
}
.ai-insight {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    border-left: 5px solid #0284c7;
}
.feedback-text {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    font-style: italic;
    line-height: 1.6;
}
.tab-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
    flex-wrap: wrap;
}
.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    transition: all 0.3s ease;
}
.tab-btn:hover {
    color: #2563eb;
    background: #f0f9ff;
}
.tab-btn.active {
    color: #2563eb;
    border-bottom: 3px solid #2563eb;
    background: #f0f9ff;
}
.tab-content {
    display: none;
    animation: fadeIn 0.5s ease;
}
.tab-content.active {
    display: block;
}
/* Form validation styles */
.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}
.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
</head>
<body>

<?php if ($success_message): ?>
<div class="message-toast success" style="display: block;">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="message-toast" style="display: block;">
    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
</div>
<?php endif; ?>

<main class="container py-5">
    <div class="dashboard-header text-center">
        <h1 class="mb-3">
            <i class="fas fa-brain me-3"></i>AI Insights Dashboard
        </h1>
        <p class="mb-0 opacity-90">AI-powered analytics for student motivation and predictions</p>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-nav">
        <button class="tab-btn active" onclick="showTab('dashboard')">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </button>
        <button class="tab-btn" onclick="showTab('feedback')">
            <i class="fas fa-comment-dots me-2"></i>AI Feedback
        </button>
        <button class="tab-btn" onclick="showTab('predictions')">
            <i class="fas fa-chart-line me-2"></i>AI Predictions
        </button>
        <button class="tab-btn" onclick="showTab('saved')">
            <i class="fas fa-save me-2"></i>Saved Insights
        </button>
    </div>

    <!-- Dashboard Tab -->
    <div id="dashboard-tab" class="tab-content active">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="ai-card ai-feature-card" onclick="showTab('feedback')">
                    <div class="pattern-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4>AI Feedback Generator</h4>
                    <p class="text-muted">Generate personalized feedback based on student reward patterns</p>
                    <div class="ai-btn ai-btn-feedback mt-3">Try It Now</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ai-card ai-feature-card" onclick="showTab('predictions')">
                    <div class="pattern-icon">
                        <i class="fas fa-crystal-ball"></i>
                    </div>
                    <h4>AI Predictions</h4>
                    <p class="text-muted">Predict student points, tiers, and rewards at month end</p>
                    <div class="ai-btn ai-btn-predict mt-3">Try It Now</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="ai-card">
                    <h4><i class="fas fa-lightbulb me-2"></i>How It Works</h4>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h5><i class="fas fa-chart-pie me-2 text-primary"></i>1. Analyze Patterns</h5>
                            <p class="text-muted">AI analyzes student reward redemption history and activity patterns.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fas fa-brain me-2 text-success"></i>2. Generate Insights</h5>
                            <p class="text-muted">Creates personalized feedback and predictions based on data patterns.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fas fa-user-edit me-2 text-warning"></i>3. Admin Control</h5>
                            <p class="text-muted">You review, edit, and decide whether to use the AI suggestions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($aiStudents || $predictionsDashboard): ?>
        <div class="row mt-4">
            <?php if ($aiStudents): ?>
            <div class="col-md-6">
                <div class="ai-card">
                    <h4><i class="fas fa-users me-2"></i>Students Ready for AI Analysis</h4>
                    <p class="text-muted">Students with sufficient activity for AI feedback generation</p>
                    <div class="stats-grid mt-3">
                        <div class="stat-box">
                            <div class="stat-value"><?= count($aiStudents) ?></div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">
                                <?= array_sum(array_column($aiStudents, 'redemption_count')) ?>
                            </div>
                            <div class="stat-label">Total Redemptions</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="../../Controllers/AIFeedbackController.php?action=dashboard" class="ai-btn">
                            <i class="fas fa-sync me-2"></i>Refresh List
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($predictionsDashboard && isset($predictionsDashboard['stats'])): 
                $stats = $predictionsDashboard['stats'];
            ?>
            <div class="col-md-6">
                <div class="ai-card prediction-card">
                    <h4><i class="fas fa-chart-bar me-2"></i>Prediction Overview</h4>
                    <div class="stats-grid mt-3">
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['total_students'] ?? 0 ?></div>
                            <div class="stat-label">Students Analyzed</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['average_predicted_points'] ?? 0 ?></div>
                            <div class="stat-label">Avg Predicted Points</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['average_confidence'] ?? 0 ?>%</div>
                            <div class="stat-label">Avg Confidence</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="../../Controllers/AIPredictionsController.php?action=admin_view" class="ai-btn ai-btn-predict">
                            <i class="fas fa-sync me-2"></i>Refresh Predictions
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- AI Feedback Tab -->
    <div id="feedback-tab" class="tab-content">
        <div class="ai-card">
            <h4><i class="fas fa-robot me-2"></i>AI Feedback Generator</h4>
            <p class="text-muted mb-4">Generate personalized feedback for students based on their reward redemption patterns</p>
            
            <!-- Student Selection -->
            <div class="mb-4">
                <h5><i class="fas fa-user-graduate me-2"></i>Select Student</h5>
                
                <?php if ($formError): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($formError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="GET" action="../../Controllers/AIFeedbackController.php" class="row g-3" id="studentForm">
                    <div class="col-md-8">
                        <select name="student_id" class="form-control <?= $formError ? 'is-invalid' : '' ?>" id="studentSelect">
                            <option value="">-- Select a Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>" <?= ($selectedStudent == $student['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['name']) ?> (<?= $student['points'] ?> points)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($formError): ?>
                        <div class="invalid-feedback">
                            Please select a student from the list
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="action" value="generate">
                        <button type="submit" class="ai-btn ai-btn-feedback w-100" onclick="return validateForm()">
                            <i class="fas fa-magic me-2"></i>Generate AI Feedback
                        </button>
                    </div>
                </form>
            </div>

            <!-- Generated Feedback Display -->
            <?php if ($aiFeedback): 
                $student = $aiFeedback['student'];
                $feedback = $aiFeedback['feedback'];
                $pattern = $aiFeedback['pattern'] ?? 'no_data';
                $confidence = $aiFeedback['confidence'] ?? 0;
                $suggestions = $aiFeedback['suggestions'] ?? [];
                $stats = $aiFeedback['stats'] ?? null;
            ?>
            <div class="ai-insight">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        AI Feedback for <?= htmlspecialchars($student['name']) ?>
                    </h5>
                    <?php if (!empty($pattern) && $pattern !== 'no_data'): ?>
                    <span class="badge-status badge-pattern">
                        <i class="fas fa-shapes me-1"></i>
                        <?= ucfirst(str_replace('_', ' ', $pattern)) ?> Pattern
                    </span>
                    <?php else: ?>
                    <span class="badge-status badge-low-confidence">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        No Reward History
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($confidence > 0): ?>
                <div class="mb-3">
                    <span class="badge-status <?= 
                        $confidence > 0.7 ? 'badge-high-confidence' : 
                        ($confidence > 0.4 ? 'badge-medium-confidence' : 'badge-low-confidence')
                    ?>">
                        <i class="fas fa-chart-line me-1"></i>
                        Confidence: <?= round($confidence * 100) ?>%
                    </span>
                </div>
                
                <div class="confidence-bar">
                    <div class="confidence-fill" style="width: <?= $confidence * 100 ?>%"></div>
                </div>
                <?php endif; ?>
                
                <div class="feedback-text mt-4 mb-4">
                    <i class="fas fa-quote-left me-2 text-muted"></i>
                    <?= htmlspecialchars($feedback) ?>
                    <i class="fas fa-quote-right ms-2 text-muted"></i>
                </div>
                
                <?php if (!empty($stats) && is_array($stats)): ?>
                <div class="stats-grid mb-4">
                    <div class="stat-box">
                        <div class="stat-value"><?= $stats['total_redemptions'] ?? 0 ?></div>
                        <div class="stat-label">Total Redemptions</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= $stats['total_points_spent'] ?? 0 ?></div>
                        <div class="stat-label">Points Spent</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= $stats['categories_engaged'] ?? 0 ?></div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No reward redemption data available for analysis.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($suggestions)): ?>
                <h6><i class="fas fa-lightbulb me-2"></i>AI Suggestions:</h6>
                <div class="mt-3">
                    <?php foreach ($suggestions as $suggestion): ?>
                        <div class="suggestion-item">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <?= htmlspecialchars($suggestion) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <form method="POST" action="../../Controllers/AIFeedbackController.php" class="row g-3">
                        <input type="hidden" name="action" value="save">
                        <div class="col-md-8">
                            <input type="text" name="notes" class="form-control" placeholder="Add your notes (optional)">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="ai-btn ai-btn-feedback w-100">
                                <i class="fas fa-save me-2"></i>Save Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Students List -->
            <?php if ($aiStudents): ?>
            <div class="mt-5">
                <h5><i class="fas fa-list me-2"></i>Students Available for AI Analysis</h5>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Points</th>
                                <th>Redemptions</th>
                                <th>Categories</th>
                                <th>Last Activity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aiStudents as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><span class="badge bg-primary"><?= $student['points'] ?></span></td>
                                <td><?= $student['redemption_count'] ?></td>
                                <td><?= $student['categories_tried'] ?></td>
                                <td><?= $student['last_redemption'] ? date('M d', strtotime($student['last_redemption'])) : 'Never' ?></td>
                                <td>
                                    <a href="../../Controllers/AIFeedbackController.php?action=generate&student_id=<?= $student['id'] ?>" 
                                       class="btn btn-sm ai-btn">
                                        <i class="fas fa-magic me-1"></i>Generate
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AI Predictions Tab -->
    <div id="predictions-tab" class="tab-content">
        <div class="ai-card">
            <h4><i class="fas fa-crystal-ball me-2"></i>AI Predictions</h4>
            <p class="text-muted mb-4">Predict student performance at the end of the month based on current activity</p>
            
            <!-- Single Student Prediction -->
            <div class="mb-5">
                <h5><i class="fas fa-user-graduate me-2"></i>Predict for Specific Student</h5>
                
                <?php if ($predictionError): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($predictionError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="GET" action="../../Controllers/AIPredictionsController.php" class="row g-3" id="predictionsForm">
                    <div class="col-md-8">
                        <select name="student_id" class="form-control <?= $predictionError ? 'is-invalid' : '' ?>">
                            <option value="">-- Select a Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>" <?= ($selectedPredictionStudent == $student['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['name']) ?> (<?= $student['points'] ?> points)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($predictionError): ?>
                        <div class="invalid-feedback">
                            Please select a student from the list
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="action" value="predict_student">
                        <button type="submit" class="ai-btn ai-btn-predict w-100" onclick="return validatePredictionsForm()">
                            <i class="fas fa-chart-line me-2"></i>Generate Predictions
                        </button>
                    </div>
                </form>
            </div>

            <!-- Generated Prediction Display -->
            <?php if ($aiPredictions): 
                $student = $aiPredictions['student'];
                $predictions = $aiPredictions['predictions'];
                $generatedAt = $aiPredictions['generated_at'];
            ?>
            <div class="ai-insight">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Predictions for <?= htmlspecialchars($student['name']) ?>
                    </h5>
                    <small class="text-muted">Generated: <?= date('M d, H:i', strtotime($generatedAt)) ?></small>
                </div>
                
                <div class="mb-4">
                    <span class="badge-status <?= 
                        $predictions['confidence'] > 70 ? 'badge-high-confidence' : 
                        ($predictions['confidence'] > 40 ? 'badge-medium-confidence' : 'badge-low-confidence')
                    ?>">
                        <i class="fas fa-chart-line me-1"></i>
                        Confidence: <?= $predictions['confidence'] ?>%
                    </span>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="prediction-item text-center">
                            <div class="prediction-value"><?= $predictions['points_prediction'] ?></div>
                            <div class="text-muted">Predicted Points</div>
                            <small class="text-muted">(Current: <?= $predictions['current_stats']['current_points'] ?>)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="prediction-item text-center">
                            <div class="prediction-value"><?= $predictions['tier_prediction'] ?></div>
                            <div class="text-muted">Predicted Tier</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="prediction-item text-center">
                            <div class="prediction-value"><?= $predictions['rewards_prediction'] ?></div>
                            <div class="text-muted">Predicted Rewards</div>
                            <small class="text-muted">(This month: <?= $predictions['current_stats']['rewards_this_month'] ?>)</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-brain me-2"></i>AI Reasoning:</h6>
                    <div class="feedback-text mt-2">
                        <?= htmlspecialchars($predictions['reasoning']) ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6><i class="fas fa-chart-bar me-2"></i>Current Month Statistics:</h6>
                    <div class="stats-grid mt-3">
                        <div class="stat-box">
                            <div class="stat-value"><?= $predictions['current_stats']['challenges_this_month'] ?></div>
                            <div class="stat-label">Challenges</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $predictions['current_stats']['rewards_this_month'] ?></div>
                            <div class="stat-label">Rewards Redeemed</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $predictions['current_stats']['active_days'] ?></div>
                            <div class="stat-label">Active Days</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Students Predictions -->
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Predictions for All Students</h5>
                    <a href="../../Controllers/AIPredictionsController.php?action=predict_all" class="ai-btn ai-btn-predict">
                        <i class="fas fa-sync me-2"></i>Generate All Predictions
                    </a>
                </div>
                
                <?php if ($allAIPredictions): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Current Points</th>
                                <th>Predicted Points</th>
                                <th>Predicted Tier</th>
                                <th>Predicted Rewards</th>
                                <th>Confidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allAIPredictions as $prediction): ?>
                            <tr>
                                <td><?= htmlspecialchars($prediction['name']) ?></td>
                                <td><span class="badge bg-primary"><?= $prediction['points'] ?></span></td>
                                <td><span class="badge bg-success"><?= $prediction['points_prediction'] ?></span></td>
                                <td><span class="badge-status badge-pattern"><?= $prediction['tier_prediction'] ?></span></td>
                                <td><span class="badge bg-info"><?= $prediction['rewards_prediction'] ?></span></td>
                                <td>
                                    <div class="confidence-bar" style="width: 100px; display: inline-block;">
                                        <div class="confidence-fill" style="width: <?= $prediction['confidence'] ?>%"></div>
                                    </div>
                                    <small class="ms-2"><?= $prediction['confidence'] ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($predictionsDashboard && isset($predictionsDashboard['all_predictions'])): 
                    $allPredictions = $predictionsDashboard['all_predictions'];
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Current Points</th>
                                <th>Predicted Points</th>
                                <th>Predicted Tier</th>
                                <th>Predicted Rewards</th>
                                <th>Confidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPredictions as $prediction): ?>
                            <tr>
                                <td><?= htmlspecialchars($prediction['name']) ?></td>
                                <td><span class="badge bg-primary"><?= $prediction['points'] ?></span></td>
                                <td><span class="badge bg-success"><?= $prediction['points_prediction'] ?></span></td>
                                <td><span class="badge-status badge-pattern"><?= $prediction['tier_prediction'] ?></span></td>
                                <td><span class="badge bg-info"><?= $prediction['rewards_prediction'] ?></span></td>
                                <td>
                                    <div class="confidence-bar" style="width: 100px; display: inline-block;">
                                        <div class="confidence-fill" style="width: <?= $prediction['confidence'] ?>%"></div>
                                    </div>
                                    <small class="ms-2"><?= $prediction['confidence'] ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No predictions generated yet. Click "Generate All Predictions" to start.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Saved Insights Tab -->
    <div id="saved-tab" class="tab-content">
        <div class="ai-card">
            <h4><i class="fas fa-save me-2"></i>Saved AI Insights</h4>
            <p class="text-muted mb-4">Previously saved AI feedback and predictions</p>
            
            <?php if (!empty($savedFeedback)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Pattern</th>
                            <th>Confidence</th>
                            <th>Admin Notes</th>
                            <th>Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($savedFeedback as $feedback): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($feedback['created_at'])) ?></td>
                            <td><?= htmlspecialchars($feedback['student_name']) ?></td>
                            <td>
                                <span class="badge-status badge-pattern">
                                    <?= ucfirst($feedback['pattern_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="confidence-bar" style="width: 80px; display: inline-block;">
                                    <div class="confidence-fill" style="width: <?= $feedback['confidence_score'] * 100 ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(substr($feedback['admin_notes'], 0, 50)) ?><?= strlen($feedback['admin_notes']) > 50 ? '...' : '' ?></small>
                            </td>
                            <td><?= htmlspecialchars($feedback['admin_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No saved insights yet. Generate and save AI feedback to see it here.
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Activate selected button
    event.target.classList.add('active');
}

// Form validation
function validateForm() {
    const studentSelect = document.getElementById('studentSelect');
    if (!studentSelect.value) {
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-2';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please select a student from the list';
        
        // Add red border to select
        studentSelect.classList.add('is-invalid');
        
        // Remove any existing error messages
        const existingErrors = studentSelect.parentElement.querySelectorAll('.alert-danger');
        existingErrors.forEach(error => error.remove());
        
        // Insert error message after the select
        studentSelect.parentElement.appendChild(errorDiv);
        
        // Remove error after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
            studentSelect.classList.remove('is-invalid');
        }, 5000);
        
        return false;
    }
    return true;
}

// Also add validation for the predictions form
function validatePredictionsForm() {
    const predictionsSelect = document.querySelector('#predictionsForm select[name="student_id"]');
    if (predictionsSelect && !predictionsSelect.value) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-2';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please select a student from the list';
        
        predictionsSelect.classList.add('is-invalid');
        
        const existingErrors = predictionsSelect.parentElement.querySelectorAll('.alert-danger');
        existingErrors.forEach(error => error.remove());
        
        predictionsSelect.parentElement.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
            predictionsSelect.classList.remove('is-invalid');
        }, 5000);
        
        return false;
    }
    return true;
}

// Auto-show appropriate tab based on URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('view_feedback') || urlParams.has('ai_dashboard')) {
        showTab('feedback');
    } else if (urlParams.has('view_predictions') || urlParams.has('view_all_predictions') || urlParams.has('predictions_dashboard')) {
        showTab('predictions');
    } else if (urlParams.has('saved')) {
        showTab('saved');
    }
    
    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const toasts = document.querySelectorAll('.message-toast');
        toasts.forEach(toast => toast.style.display = 'none');
    }, 5000);
});
</script>
</body>
</html>