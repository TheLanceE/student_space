<?php
require_once '../../Controllers/config.php';
// auth_check already included by config.php

// Database connection already available as $db_connection from config.php
try {
  // Get user details
  $user_id = $_SESSION['user_id'] ?? null;

  if (!$user_id) {
    header('Location: login.php?error=not_logged_in');
    exit;
  }

  $sessionUsername = $_SESSION['username'] ?? ($_SESSION['google_name'] ?? 'Student');
  $sessionFullName = $_SESSION['full_name'] ?? $_SESSION['google_name'] ?? $sessionUsername;
    error_log('[Profile] Looking up user_id: ' . $user_id);
    error_log('[Profile] Session data: ' . json_encode([
        'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
        'username' => $_SESSION['username'] ?? 'NOT SET',
        'role' => $_SESSION['role'] ?? 'NOT SET',
        'email' => $_SESSION['email'] ?? 'NOT SET'
    ]));
    
    $sql = "SELECT * FROM students WHERE id = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') LIMIT 1";
    $stmt = $db_connection->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log('[Profile] User found: ' . ($user ? 'YES' : 'NO'));
    if ($user) {
        error_log('[Profile] User data: ' . json_encode($user));
    }
    
    // Get quiz stats (try different column names for student ID)
    $stats = ['total_quizzes' => 0, 'avg_score' => 0, 'best_score' => 0, 'lowest_score' => 0];
    try {
        $stats_sql = "SELECT COUNT(*) as total_quizzes, 
                      COALESCE(AVG(score), 0) as avg_score,
                      COALESCE(MAX(score), 0) as best_score,
                      COALESCE(MIN(score), 0) as lowest_score
                      FROM scores WHERE userId = ?";
        $stats_stmt = $db_connection->prepare($stats_sql);
        $stats_stmt->execute([$user_id]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Try alternate column name
        try {
            $stats_sql = "SELECT COUNT(*) as total_quizzes, 
                          COALESCE(AVG(score), 0) as avg_score,
                          COALESCE(MAX(score), 0) as best_score,
                          COALESCE(MIN(score), 0) as lowest_score
                          FROM scores WHERE student_id = ?";
            $stats_stmt = $db_connection->prepare($stats_sql);
            $stats_stmt->execute([$user_id]);
            $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            error_log('[Profile] Stats query error: ' . $e2->getMessage());
        }
    }
    
    
    // If no user found, use session data as fallback
    if (!$user) {
        error_log('[Profile] No user found in database for ID: ' . $user_id);
        $user = [
          'id' => $user_id,
          'username' => $sessionUsername,
          'email' => $_SESSION['email'] ?? '',
          'mobile' => '',
          'address' => '',
          'gradeLevel' => 'Not assigned',
          'createdAt' => date('Y-m-d H:i:s'),
          'lastLoginAt' => date('Y-m-d H:i:s'),
          'fullName' => $sessionFullName,
          'avatarPath' => $_SESSION['avatar_path'] ?? null
        ];
    } else {
        // User found - ensure all fields have values
        error_log('[Profile] Displaying user: ' . $user['fullName'] . ' (email: ' . ($user['email'] ?? 'EMPTY') . ')');
    }
    
} catch(PDOException $e) {
    error_log('[Profile] Database error: ' . $e->getMessage());
      $user = [
        'id' => $user_id ?? '',
        'username' => $sessionUsername,
        'email' => $_SESSION['email'] ?? '',
        'mobile' => '',
        'address' => '',
        'gradeLevel' => 'Not assigned',
        'createdAt' => date('Y-m-d H:i:s'),
        'lastLoginAt' => null,
        'fullName' => $sessionFullName,
        'avatarPath' => $_SESSION['avatar_path'] ?? null
      ];
    $stats = ['total_quizzes' => 0, 'avg_score' => 0, 'best_score' => 0, 'lowest_score' => 0];
}

    $avatarPath = $user['avatarPath'] ?? ($_SESSION['avatar_path'] ?? null);
    $avatarUrl = null;
    if ($avatarPath) {
      $candidate = __DIR__ . '/../../' . $avatarPath;
      if (file_exists($candidate)) {
        $avatarUrl = '../../' . $avatarPath;
      }
    }
    if (!$avatarUrl) {
      // Try to discover by user id
      $glob = glob(__DIR__ . '/../../uploads/avatars/' . $user_id . '.*');
      if (!empty($glob)) {
        $fileName = basename($glob[0]);
        $avatarUrl = '../../uploads/avatars/' . $fileName;
      }
    }
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>EduMind+ | My Profile</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
   .profile-header {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     padding: 3rem 0;
     margin-bottom: 2rem;
     position: relative;
     overflow: hidden;
   }
   
   .profile-header::before {
     content: '';
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
     background-size: cover;
     opacity: 0.3;
   }
   
   .profile-avatar {
     width: 120px;
     height: 120px;
     background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
     border-radius: 50%;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 3rem;
     font-weight: bold;
     color: #667eea;
     box-shadow: 0 10px 30px rgba(0,0,0,0.3);
     margin: 0 auto 1rem;
     animation: bounceIn 0.8s ease-out;
   }
   
   @keyframes bounceIn {
     0% {
       transform: scale(0);
       opacity: 0;
     }
     50% {
       transform: scale(1.1);
     }
     100% {
       transform: scale(1);
       opacity: 1;
     }
   }
   
   .stat-card {
     background: white;
     border-radius: 1rem;
     padding: 1.5rem;
     box-shadow: 0 4px 15px rgba(0,0,0,0.1);
     transition: all 0.3s ease;
     border: none;
     position: relative;
     overflow: hidden;
   }
   
   .stat-card::before {
     content: '';
     position: absolute;
     top: 0;
     left: 0;
     width: 4px;
     height: 100%;
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   }
   
   .stat-card:hover {
     transform: translateY(-5px) scale(1.02);
     box-shadow: 0 8px 25px rgba(0,0,0,0.15);
   }
   
   .stat-icon {
     width: 50px;
     height: 50px;
     border-radius: 12px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.5rem;
     margin-bottom: 0.5rem;
   }
   
   .stat-value {
     font-size: 2rem;
     font-weight: 700;
     color: #212529;
     margin: 0.5rem 0;
   }
   
   .stat-label {
     color: #6c757d;
     font-size: 0.9rem;
     text-transform: uppercase;
     letter-spacing: 0.5px;
   }
   
   .info-card {
     background: white;
     border-radius: 1rem;
     padding: 2rem;
     box-shadow: 0 4px 15px rgba(0,0,0,0.1);
     margin-bottom: 2rem;
   }
   
   .info-row {
     display: flex;
     padding: 1rem 0;
     border-bottom: 1px solid #e9ecef;
     align-items: center;
   }
   
   .info-row:last-child {
     border-bottom: none;
   }
   
   .info-icon {
     width: 40px;
     height: 40px;
     border-radius: 10px;
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     display: flex;
     align-items: center;
     justify-content: center;
     margin-right: 1rem;
     font-size: 1.2rem;
   }
   
   .info-label {
     font-weight: 600;
     color: #495057;
     margin-bottom: 0.25rem;
   }
   
   .info-value {
     color: #6c757d;
   }
   
   .badge-custom {
     padding: 0.5rem 1rem;
     border-radius: 2rem;
     font-weight: 600;
     font-size: 0.85rem;
     animation: fadeIn 1s ease-out;
   }
   
   .danger-zone {
     background: #fff5f5;
     border: 2px dashed #ef4444;
     border-radius: 1rem;
     padding: 1.5rem;
     margin-top: 2rem;
   }
   
   .danger-zone h4 {
     color: #dc3545;
     font-size: 1.1rem;
     margin-bottom: 0.5rem;
   }
 </style>
</head>
<body>
 <?php include __DIR__ . '/../partials/navbar_student.php'; ?>

 <div class="profile-header">
   <div class="container text-center" style="position: relative; z-index: 1;">
     <div class="profile-avatar">
      <?php if ($avatarUrl): ?>
        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.25);" />
      <?php else: ?>
        <?php echo strtoupper(substr($sessionUsername, 0, 2)); ?>
      <?php endif; ?>
     </div>
     <h1 class="h3 mb-2"><?php echo htmlspecialchars($user['fullName'] ?? $sessionUsername); ?></h1>
     <p class="mb-0 opacity-75">
       <i class="bi bi-person-badge"></i> Student ID: <?php echo htmlspecialchars($user_id); ?>
     </p>
   </div>
 </div>

 <main class="container pb-5">
  <div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-2">
      <form action="../../Controllers/upload_avatar.php" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SessionManager::getCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="file" name="avatar" accept="image/png, image/jpeg, image/webp" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-primary btn-sm">Upload photo</button>
      </form>
      <small class="text-muted">JPG/PNG/WEBP, max 2MB.</small>
    </div>
  </div>

   <!-- Stats Row -->
   <div class="row g-4 mb-4">
     <div class="col-6 col-md-3">
       <div class="stat-card">
         <div class="stat-icon" style="background: #e3f2fd; color: #2196f3;">
           <i class="bi bi-journal-text"></i>
         </div>
         <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
         <div class="stat-label">Total Quizzes</div>
       </div>
     </div>
     <div class="col-6 col-md-3">
       <div class="stat-card">
         <div class="stat-icon" style="background: #fff3e0; color: #ff9800;">
           <i class="bi bi-star-fill"></i>
         </div>
         <div class="stat-value"><?php echo round($stats['avg_score'], 1); ?>%</div>
         <div class="stat-label">Average Score</div>
       </div>
     </div>
     <div class="col-6 col-md-3">
       <div class="stat-card">
         <div class="stat-icon" style="background: #e8f5e9; color: #4caf50;">
           <i class="bi bi-trophy-fill"></i>
         </div>
         <div class="stat-value"><?php echo round($stats['best_score'], 1); ?>%</div>
         <div class="stat-label">Best Score</div>
       </div>
     </div>
     <div class="col-6 col-md-3">
       <div class="stat-card">
         <div class="stat-icon" style="background: #fce4ec; color: #e91e63;">
           <i class="bi bi-graph-up"></i>
         </div>
         <div class="stat-value">
           <?php 
           $joined = strtotime($user['createdAt'] ?? 'now');
           $days = floor((time() - $joined) / 86400);
           echo max(1, $days);
           ?>
         </div>
         <div class="stat-label">Days Active</div>
       </div>
     </div>
   </div>

   <!-- Profile Information -->
   <div class="row g-4">
     <div class="col-lg-6">
       <div class="info-card">
         <h3 class="h5 mb-4"><i class="bi bi-person-lines-fill"></i> Personal Information</h3>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-person"></i></div>
           <div>
             <div class="info-label">Username</div>
             <div class="info-value"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-envelope"></i></div>
           <div>
             <div class="info-label">Email Address</div>
             <div class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-phone"></i></div>
           <div>
             <div class="info-label">Mobile</div>
             <div class="info-value"><?php echo !empty($user['mobile']) ? htmlspecialchars($user['mobile']) : 'Not provided'; ?></div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
           <div>
             <div class="info-label">Address</div>
             <div class="info-value"><?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not provided'; ?></div>
           </div>
         </div>
       </div>
     </div>
     
     <div class="col-lg-6">
       <div class="info-card">
         <h3 class="h5 mb-4"><i class="bi bi-bookmark-star-fill"></i> Academic Information</h3>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-award"></i></div>
           <div>
             <div class="info-label">Grade Level</div>
             <div class="info-value">
               <span class="badge badge-custom bg-primary">
                 <?php echo htmlspecialchars($user['gradeLevel'] ?? 'Not assigned'); ?>
               </span>
             </div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-calendar-check"></i></div>
           <div>
             <div class="info-label">Joined Date</div>
             <div class="info-value"><?php echo date('F j, Y', strtotime($user['createdAt'] ?? 'now')); ?></div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-clock-history"></i></div>
           <div>
             <div class="info-label">Last Login</div>
             <div class="info-value"><?php echo isset($user['lastLoginAt']) && $user['lastLoginAt'] ? date('M j, Y g:i A', strtotime($user['lastLoginAt'])) : 'N/A'; ?></div>
           </div>
         </div>
         
         <div class="info-row">
           <div class="info-icon"><i class="bi bi-shield-check"></i></div>
           <div>
             <div class="info-label">Account Status</div>
             <div class="info-value">
               <span class="badge badge-custom bg-success">
                 <i class="bi bi-check-circle"></i> Active
               </span>
             </div>
           </div>
         </div>
       </div>
     </div>
   </div>

   <!-- Danger Zone -->
   <div class="danger-zone">
     <h4><i class="bi bi-exclamation-triangle"></i> Danger Zone</h4>
     <p class="text-muted mb-3">Once you delete your account, there is no going back. Please be certain.</p>
     <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
       <i class="bi bi-trash"></i> Delete My Account
     </button>
   </div>
 </main>

 <!-- Delete Account Modal -->
 <div class="modal fade" id="deleteAccountModal" tabindex="-1">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header bg-danger text-white">
         <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Delete Account</h5>
         <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
       </div>
       <div class="modal-body">
         <div class="alert alert-warning">
           <strong>⚠️ Warning:</strong> This action cannot be undone. All your data will be permanently deleted.
         </div>
         <form id="deleteAccountForm">
           <div class="mb-3">
             <label for="confirmPassword" class="form-label">Enter your password to confirm:</label>
             <input type="password" class="form-control" id="confirmPassword" required>
           </div>
           <div class="mb-3">
             <div class="form-check">
               <input class="form-check-input" type="checkbox" id="confirmDelete" required>
               <label class="form-check-label" for="confirmDelete">
                 I understand this action is permanent
               </label>
             </div>
           </div>
         </form>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
         <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete My Account</button>
       </div>
     </div>
   </div>
 </div>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script>
   // Add animation on scroll
   window.addEventListener('scroll', function() {
     const cards = document.querySelectorAll('.stat-card, .info-card');
     cards.forEach(card => {
       const rect = card.getBoundingClientRect();
       if (rect.top < window.innerHeight - 100) {
         card.style.opacity = '1';
         card.style.transform = 'translateY(0)';
       }
     });
   });
   
   // Initialize cards as hidden for scroll animation
   document.querySelectorAll('.stat-card, .info-card').forEach(card => {
     card.style.opacity = '0';
     card.style.transform = 'translateY(30px)';
     card.style.transition = 'all 0.6s ease';
   });
   
   // Trigger animation on load
   setTimeout(() => {
     window.dispatchEvent(new Event('scroll'));
   }, 100);
 </script>
</body>
</html>


