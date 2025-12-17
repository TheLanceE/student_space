<?php 
if(session_status() === PHP_SESSION_NONE) session_start(); 
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/AIPredictions.php';
require_once __DIR__ . '/../../Models/Points.php';


$studentID = $_SESSION['userID'] ?? 1;
$balance = Points::getBalance($pdo, $studentID);

// Get predictions if requested
$showPredictions = isset($_GET['student_predictions']) || isset($_SESSION['student_predictions']);
$studentPredictions = $_SESSION['student_predictions'] ?? null;

// Clear session
unset($_SESSION['student_predictions']);

// Generate predictions if not already available
if ($showPredictions && !$studentPredictions) {
    // Redirect to generate predictions
    header('Location: ../../Controllers/AIPredictionsController.php?action=student_view');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>AI Predictions - Student</title>
<link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background: #f8fafc;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    color: #2c3e50;
}
.container {
    max-width: 1200px;
}
.ai-card { 
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-left: 5px solid #8b5cf6;
}
.prediction-card {
    border-left: 5px solid #2563eb;
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
.points-display {
    background: white; 
    border: 3px solid #2563eb; 
    color: #2563eb; 
    padding: 20px; 
    border-radius: 12px; 
    text-align: center; 
    font-size: 1.5em; 
    font-weight: bold;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.prediction-item {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
    text-align: center;
}
.prediction-value {
    font-size: 2rem;
    font-weight: bold;
    color: #2563eb;
    margin: 10px 0;
}
.confidence-bar {
    background: #e5e7eb;
    border-radius: 10px;
    height: 10px;
    margin: 15px 0;
    overflow: hidden;
}
.confidence-fill {
    height: 100%;
    background: #059669;
    border-radius: 10px;
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
.section-title {
    color: #1e40af;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #2563eb;
}
.section-title i {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    padding: 5px;
    margin-right: 10px;
    font-size: 1.2em;
}
.tier-badge {
    font-size: 3rem;
    margin-bottom: 10px;
    display: block;
}
</style>
</head>
<body>

<main class="container py-5">
    

    <!-- Points Display -->
    <div class="points-display">
        <i class="fas fa-star me-2"></i>
        <span id="balance"><?= $balance ?></span> Points Available
    </div>

    <!-- AI Predictions Section -->
    <div class="ai-card">
        <h3 class="section-title">
            <i class="fas fa-crystal-ball me-2"></i>AI Performance Predictions
        </h3>
        
        <p class="text-muted mb-4">
            See AI-powered predictions for your end-of-month performance based on your current activity patterns.
            These predictions help you plan your learning goals.
        </p>
        
        <?php if ($studentPredictions): 
            $student = $studentPredictions['student'];
            $predictions = $studentPredictions['predictions'];
            $generatedAt = $studentPredictions['generated_at'];
        ?>
        <div class="ai-insight">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    Your Month-End Predictions
                </h5>
                <small class="text-muted">Generated: <?= date('M d, H:i', strtotime($generatedAt)) ?></small>
            </div>
            
            <!-- Confidence Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Prediction Confidence</span>
                    <span><?= $predictions['confidence'] ?>%</span>
                </div>
                <div class="confidence-bar">
                    <div class="confidence-fill" style="width: <?= $predictions['confidence'] ?>%"></div>
                </div>
            </div>
            
            <!-- Predictions Grid -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="prediction-item">
                        <div class="tier-badge">📊</div>
                        <div class="prediction-value"><?= $predictions['points_prediction'] ?></div>
                        <div class="text-muted">Predicted Points</div>
                        <small class="text-muted">Current: <?= $predictions['current_stats']['current_points'] ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="prediction-item">
                        <div class="tier-badge">🏆</div>
                        <div class="prediction-value"><?= $predictions['tier_prediction'] ?></div>
                        <div class="text-muted">Predicted Tier</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="prediction-item">
                        <div class="tier-badge">🎁</div>
                        <div class="prediction-value"><?= $predictions['rewards_prediction'] ?></div>
                        <div class="text-muted">Predicted Rewards</div>
                        <small class="text-muted">This month: <?= $predictions['current_stats']['rewards_this_month'] ?></small>
                    </div>
                </div>
            </div>
            
            <!-- AI Reasoning -->
            <div class="mb-4">
                <h6><i class="fas fa-brain me-2"></i>How AI Calculated This:</h6>
                <div class="alert alert-light mt-2">
                    <?= htmlspecialchars($predictions['reasoning']) ?>
                </div>
            </div>
            
            <!-- Current Stats -->
            <div>
                <h6><i class="fas fa-chart-bar me-2"></i>Your Current Month Activity:</h6>
                <div class="stats-grid mt-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= $predictions['current_stats']['challenges_this_month'] ?></div>
                        <div class="stat-label">Challenges Completed</div>
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
            
            <!-- Refresh Button -->
            <div class="text-center mt-4">
                <a href="../../Controllers/AIPredictionsController.php?action=student_view" class="ai-btn">
                    <i class="fas fa-sync me-2"></i>Refresh Predictions
                </a>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <div class="tier-badge">🔮</div>
            <h4 class="mb-3">Generate Your AI Predictions</h4>
            <p class="text-muted mb-4">Get AI-powered insights into your expected month-end performance</p>
            <a href="../../Controllers/AIPredictionsController.php?action=student_view" class="ai-btn btn-lg">
                <i class="fas fa-magic me-2"></i>Generate AI Predictions
            </a>
        </div>
        <?php endif; ?>
        
        <!-- How It Works -->
        <div class="mt-5">
            <h5><i class="fas fa-info-circle me-2"></i>How AI Predictions Work</h5>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-value">📈</div>
                        <div class="stat-label">Pattern Analysis</div>
                        <small class="text-muted">AI analyzes your activity patterns</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-value">🧮</div>
                        <div class="stat-label">Trend Projection</div>
                        <small class="text-muted">Projects current trends to month end</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-value">🎯</div>
                        <div class="stat-label">Personalized Insights</div>
                        <small class="text-muted">Provides tailored predictions for you</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tips Section -->
    <div class="ai-card prediction-card">
        <h3 class="section-title">
            <i class="fas fa-lightbulb me-2"></i>Tips to Improve Your Predictions
        </h3>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Complete Daily Challenges</h6>
                    <p class="mb-0 small">Regular challenge completion improves point predictions</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6><i class="fas fa-chart-line me-2"></i>Maintain Consistency</h6>
                    <p class="mb-0 small">Active days per week increase prediction accuracy</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-warning">
                    <h6><i class="fas fa-gift me-2"></i>Strategic Reward Redemption</h6>
                    <p class="mb-0 small">Balance point earning and spending for optimal tier progression</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-primary">
                    <h6><i class="fas fa-target me-2"></i>Set Monthly Goals</h6>
                    <p class="mb-0 small">Use predictions to set and achieve your learning targets</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Auto-refresh predictions every 5 minutes if viewing
<?php if ($studentPredictions): ?>
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutes
<?php endif; ?>
</script>
</body>
</html>