<?php
class AIPredictions {
    
    /**
     * Predict student's end-of-month metrics
     */
    public static function predictEndOfMonth($pdo, $studentId) {
        try {
            // Get current month
            $startOfMonth = date('Y-m-01');
            $today = date('Y-m-d');
            $daysInMonth = date('t');
            $daysPassed = date('j');
            
            // Get student stats
            $student = self::getStudentStats($pdo, $studentId, $startOfMonth);
            
            if (!$student) {
                return [
                    'points_prediction' => 0,
                    'tier_prediction' => 'Unknown',
                    'rewards_prediction' => 0,
                    'confidence' => 0,
                    'reasoning' => 'No student data found'
                ];
            }
            
            // Calculate predictions
            return self::calculatePredictions($student, $daysPassed, $daysInMonth);
            
        } catch (Exception $e) {
            error_log("AI Prediction Error: " . $e->getMessage());
            return [
                'points_prediction' => 0,
                'tier_prediction' => 'Error',
                'rewards_prediction' => 0,
                'confidence' => 0,
                'reasoning' => 'Prediction unavailable'
            ];
        }
    }
    
    /**
     * Get student statistics
     */
    private static function getStudentStats($pdo, $studentId, $startDate) {
        $stmt = $pdo->prepare("
            SELECT 
                u.id, u.name, u.points as current_points,
                COUNT(DISTINCT CASE WHEN al.activity_type = 'challenge_complete' AND al.created_at >= ? THEN al.target_id END) as challenges_this_month,
                COUNT(DISTINCT CASE WHEN al.activity_type = 'redeem_reward' AND al.created_at >= ? THEN al.target_id END) as rewards_this_month,
                COUNT(DISTINCT DATE(al.created_at)) as active_days
            FROM users u
            LEFT JOIN activity_log al ON u.id = al.user_id
            WHERE u.id = ?
        ");
        
        $stmt->execute([$startDate, $startDate, $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate predictions
     */
    private static function calculatePredictions($student, $daysPassed, $daysInMonth) {
        $currentPoints = (int)$student['current_points'];
        $challengesThisMonth = (int)$student['challenges_this_month'];
        $rewardsThisMonth = (int)$student['rewards_this_month'];
        $activeDays = (int)$student['active_days'];
        
        // Calculate daily averages
        $challengesPerDay = $daysPassed > 0 ? $challengesThisMonth / $daysPassed : 1;
        $rewardsPerDay = $daysPassed > 0 ? $rewardsThisMonth / $daysPassed : 0.5;
        
        // Predict remaining month
        $daysRemaining = $daysInMonth - $daysPassed;
        $predictedAdditionalChallenges = $challengesPerDay * $daysRemaining * 0.9;
        $predictedAdditionalRewards = $rewardsPerDay * $daysRemaining * 0.8;
        
        // Points prediction (average 50 points per challenge)
        $predictedPointsEarned = $predictedAdditionalChallenges * 50;
        $pointsPrediction = $currentPoints + $predictedPointsEarned;
        
        // Tier prediction
        $tierPrediction = self::predictTier($pointsPrediction);
        
        // Rewards prediction
        $rewardsPrediction = $rewardsThisMonth + $predictedAdditionalRewards;
        
        // Confidence
        $confidence = self::calculateConfidence($student, $daysPassed);
        
        // Reasoning
        $reasoning = self::generateReasoning($student, $pointsPrediction, $rewardsPrediction, $confidence);
        
        return [
            'points_prediction' => max(0, round($pointsPrediction)),
            'tier_prediction' => $tierPrediction,
            'rewards_prediction' => max(0, round($rewardsPrediction)),
            'confidence' => $confidence,
            'reasoning' => $reasoning,
            'current_stats' => [
                'current_points' => $currentPoints,
                'challenges_this_month' => $challengesThisMonth,
                'rewards_this_month' => $rewardsThisMonth,
                'active_days' => $activeDays
            ]
        ];
    }
    
    /**
     * Predict tier
     */
    private static function predictTier($points) {
        if ($points >= 500) return 'Platinum';
        if ($points >= 250) return 'Gold';
        if ($points >= 100) return 'Silver';
        if ($points >= 25) return 'Bronze';
        return 'Bronze (pending)';
    }
    
    /**
     * Calculate confidence
     */
    private static function calculateConfidence($student, $daysPassed) {
    $activeDays = (int)$student['active_days'];
    $challengesThisMonth = (int)$student['challenges_this_month'];
    
    // If no activity at all, very low confidence
    if ($activeDays == 0 && $challengesThisMonth == 0) {
        return 10; // 10% confidence - basically guessing
    }
    
    // Activity confidence: based on consistency
    $activityConfidence = 0;
    if ($daysPassed > 0) {
        $activityRate = $activeDays / $daysPassed;
        if ($activityRate >= 0.8) $activityConfidence = 0.9; // Very consistent
        elseif ($activityRate >= 0.5) $activityConfidence = 0.7; // Fairly consistent
        elseif ($activityRate >= 0.3) $activityConfidence = 0.5; // Somewhat consistent
        else $activityConfidence = 0.3; // Inconsistent
    } else {
        $activityConfidence = 0.1; // No days passed yet
    }
    
    // Data confidence: based on amount of data
    $dataConfidence = 0;
    if ($challengesThisMonth >= 10) $dataConfidence = 0.9; // Lots of data
    elseif ($challengesThisMonth >= 5) $dataConfidence = 0.7; // Good amount
    elseif ($challengesThisMonth >= 2) $dataConfidence = 0.5; // Some data
    elseif ($challengesThisMonth == 1) $dataConfidence = 0.3; // Minimal data
    else $dataConfidence = 0.1; // No data
    
    // Time factor: earlier in month = lower confidence
    $timeFactor = $daysPassed / date('t'); // Portion of month passed
    
    // Weighted confidence calculation
    $confidence = (
        ($activityConfidence * 0.4) +      // 40% weight to activity consistency
        ($dataConfidence * 0.4) +          // 40% weight to data quantity
        ($timeFactor * 0.2)                // 20% weight to time passed
    ) * 100;
    
    // Round and ensure reasonable bounds
    $confidence = round($confidence);
    $confidence = max(10, min(95, $confidence)); // Between 10-95%
    
    return $confidence;
} 

    /**
     * Generate reasoning
     */
    private static function generateReasoning($student, $pointsPrediction, $rewardsPrediction, $confidence) {
    $currentPoints = $student['current_points'];
    $challengesThisMonth = $student['challenges_this_month'];
    $activeDays = $student['active_days'];
    $daysPassed = date('j'); // Current day of month
    
    $pointsIncrease = $pointsPrediction - $currentPoints;
    
    $reasoning = "Based on your current activity: ";
    $reasoning .= "You've completed {$challengesThisMonth} challenge" . ($challengesThisMonth != 1 ? 's' : '') . " ";
    $reasoning .= "over {$activeDays} day" . ($activeDays != 1 ? 's' : '') . " this month. ";
    
    if ($pointsIncrease > 0) {
        $reasoning .= "We predict you'll earn approximately {$pointsIncrease} more points by month end. ";
    } else {
        $reasoning .= "Your current points are already at a good level. ";
    }
    
    // Add confidence explanation
    if ($confidence >= 70) {
        $reasoning .= "High confidence ({$confidence}%) due to consistent activity patterns. ";
    } elseif ($confidence >= 40) {
        $reasoning .= "Moderate confidence ({$confidence}%) based on recent trends. ";
    } else {
        $reasoning .= "Lower confidence ({$confidence}%) - more activity would improve accuracy. ";
    }
    
    // Add personalized tips
    if ($challengesThisMonth < 2) {
        $reasoning .= "Try completing more challenges to increase prediction accuracy. ";
    } elseif ($activeDays / $daysPassed < 0.3) {
        $reasoning .= "More regular participation would improve your point earnings. ";
    }
    
    return $reasoning;
}

    
    /**
     * Get all students with predictions
     */
    public static function getAllStudentPredictions($pdo) {
        $stmt = $pdo->prepare("
            SELECT id, name, points 
            FROM users 
            WHERE role = 'student' 
            ORDER BY points DESC
        ");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $predictions = [];
        foreach ($students as $student) {
            $prediction = self::predictEndOfMonth($pdo, $student['id']);
            $predictions[] = array_merge($student, $prediction);
        }
        
        return $predictions;
    }
}
?>