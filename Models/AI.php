<?php
class AI {
    
    /**
     * Generate AI feedback based on student's reward redemption patterns
     */
    public static function generateStudentFeedback($pdo, $studentId) {
        try {
            // Get student's redemption history
            $stmt = $pdo->prepare("
                SELECT r.category, COUNT(*) as redemption_count, 
                       SUM(ABS(al.points_amount)) as total_points_spent,
                       MIN(al.created_at) as first_redemption,
                       MAX(al.created_at) as last_redemption
                FROM activity_log al
                JOIN rewards r ON al.target_id = r.id
                WHERE al.user_id = ? 
                AND al.activity_type = 'redeem_reward'
                GROUP BY r.category
                ORDER BY redemption_count DESC
            ");
            $stmt->execute([$studentId]);
            $redemptionStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($redemptionStats)) {
                return [
                    'feedback' => "No reward redemption history found. This student hasn't redeemed any rewards yet.",
                    'pattern' => 'No Data',
                    'confidence' => 0,
                    'suggestions' => ['Encourage the student to explore available rewards.']
                ];
            }
            
            // Analyze patterns
            $analysis = self::analyzeRedemptionPatterns($redemptionStats);
            
            // Generate feedback
            $feedback = self::generateFeedbackFromAnalysis($analysis, $redemptionStats);
            
            return [
                'feedback' => $feedback,
                'pattern' => $analysis['primary_pattern'],
                'confidence' => $analysis['confidence_score'],
                'suggestions' => $analysis['suggestions'],
                'stats' => [
                    'total_redemptions' => array_sum(array_column($redemptionStats, 'redemption_count')),
                    'total_points_spent' => array_sum(array_column($redemptionStats, 'total_points_spent')),
                    'categories_engaged' => count($redemptionStats)
                ]
            ];
            
        } catch (Exception $e) {
            error_log("AI Feedback Error: " . $e->getMessage());
            return [
                'feedback' => "Unable to generate AI feedback. Please try again later.",
                'pattern' => 'Error',
                'confidence' => 0,
                'suggestions' => []
            ];
        }
    }
    
    /**
     * Analyze redemption patterns
     */
    private static function analyzeRedemptionPatterns($redemptionStats) {
        $categoryCounts = [];
        $totalRedemptions = 0;
        
        foreach ($redemptionStats as $stat) {
            $categoryCounts[$stat['category']] = $stat['redemption_count'];
            $totalRedemptions += $stat['redemption_count'];
        }
        
        // Pattern detection
        $patterns = [];
        $confidenceScore = 0;
        
        // Pattern 1: Achievement-driven
        $achievementRewards = ($categoryCounts['Badge'] ?? 0) + ($categoryCounts['Certificate'] ?? 0);
        if ($achievementRewards > $totalRedemptions * 0.6) {
            $patterns['achievement_driven'] = [
                'score' => $achievementRewards / $totalRedemptions,
                'description' => 'Prefers achievement-based rewards'
            ];
            $confidenceScore += 0.3;
        }
        
        // Pattern 2: Utility-driven
        $utilityRewards = ($categoryCounts['Bonus Points'] ?? 0) + ($categoryCounts['Discount'] ?? 0);
        if ($utilityRewards > $totalRedemptions * 0.6) {
            $patterns['utility_driven'] = [
                'score' => $utilityRewards / $totalRedemptions,
                'description' => 'Prefers practical rewards'
            ];
            $confidenceScore += 0.3;
        }
        
        // Pattern 3: Explorer
        $uniqueCategories = count($categoryCounts);
        if ($uniqueCategories >= 3 && max($categoryCounts) < $totalRedemptions * 0.5) {
            $patterns['explorer'] = [
                'score' => $uniqueCategories / 6,
                'description' => 'Explores various reward types'
            ];
            $confidenceScore += 0.2;
        }
        
        // Determine primary pattern
        $primaryPattern = 'balanced';
        if (!empty($patterns)) {
            arsort($patterns);
            $primaryPattern = array_key_first($patterns);
        }
        
        // Generate suggestions
        $suggestions = self::generateSuggestions($primaryPattern, $categoryCounts, $totalRedemptions);
        
        return [
            'primary_pattern' => $primaryPattern,
            'patterns' => $patterns,
            'confidence_score' => min(1.0, $confidenceScore),
            'suggestions' => $suggestions
        ];
    }
    
    /**
     * Generate feedback text
     */
    private static function generateFeedbackFromAnalysis($analysis, $redemptionStats) {
        $pattern = $analysis['primary_pattern'];
        $totalRedemptions = array_sum(array_column($redemptionStats, 'redemption_count'));
        $uniqueCategories = count($redemptionStats);
        
        $feedbackTemplates = [
            'achievement_driven' => "This student appears highly motivated by achievement and recognition. They have redeemed achievement-based rewards. Consider offering more milestone-based rewards.",
            
            'utility_driven' => "The student prefers practical, utility-based rewards indicating a focus on short-term progress and efficiency. Consider offering more immediate-use rewards.",
            
            'explorer' => "The student explores a wide variety of rewards, showing curiosity and engagement. Consider offering diverse reward options to maintain their interest.",
            
            'perk_seeker' => "This student values special perks and privileges. Consider offering tiered perks or exclusive access rewards.",
            
            'balanced' => "The student shows a balanced approach to reward redemption. A diverse reward portfolio works well."
        ];
        
        return $feedbackTemplates[$pattern] ?? 
               "Based on the student's reward redemption patterns, they show engagement with the reward system.";
    }
    
    /**
     * Generate suggestions
     */
    private static function generateSuggestions($pattern, $categoryCounts, $totalRedemptions) {
        $suggestions = [];
        
        switch ($pattern) {
            case 'achievement_driven':
                $suggestions = [
                    "Offer milestone badges for completed challenges",
                    "Create certificate rewards for course completion",
                    "Implement achievement leaderboards"
                ];
                break;
                
            case 'utility_driven':
                $suggestions = [
                    "Offer bonus point bundles",
                    "Create time-limited discount offers",
                    "Offer practical study aids as rewards"
                ];
                break;
                
            case 'explorer':
                $suggestions = [
                    "Rotate reward categories monthly",
                    "Create 'mystery boxes' with random rewards",
                    "Offer category-specific challenges"
                ];
                break;
                
            default:
                $suggestions = [
                    "Monitor reward preferences",
                    "Offer a balanced mix of reward types",
                    "Track redemption frequency"
                ];
        }
        
        return array_slice($suggestions, 0, 3);
    }
    
    /**
     * Get students for AI feedback
     */
    public static function getStudentsForFeedback($pdo) {
        $sql = "
            SELECT u.id, u.name, u.email, u.points,
                   COUNT(al.id) as redemption_count,
                   COUNT(DISTINCT r.category) as categories_tried,
                   MAX(al.created_at) as last_redemption
            FROM users u
            LEFT JOIN activity_log al ON u.id = al.user_id 
                AND al.activity_type = 'redeem_reward'
            LEFT JOIN rewards r ON al.target_id = r.id
            WHERE u.role = 'student'
            GROUP BY u.id
            HAVING redemption_count > 0
            ORDER BY last_redemption DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>