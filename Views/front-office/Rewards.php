<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Rewards.php';
require_once __DIR__ . '/../../Models/Points.php';
$studentID = $_SESSION['userID'] ?? 1;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rewards | Student</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet"> <!-- Merged CSS link -->
  <style>
    #toast-container { position: fixed; top: 10px; right: 10px; z-index: 9999; }
    .toast { background: #4caf50; color: white; padding: 10px 15px; margin-bottom: 5px; border-radius: 5px; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
    @keyframes fadein { from {opacity:0;} to {opacity:1;} }
    @keyframes fadeout { from {opacity:1;} to {opacity:0;} }
  </style>
</head>
<body data-page="student-rewards">
  <main class="container py-4">
    <h1>🎁 Rewards</h1>
    <div class="alert alert-info">Your Points Balance: <span id="balance"><?php echo Points::getBalance($pdo, $studentID); ?></span> pts</div>
    <div id="rewardsList">
      <?php
      $rewards = Rewards::getAll($pdo);
      // Enhancement: Add sample rewards if DB is empty (for demo, non-destructive)
      if (!$rewards) {
          $rewards = [
              ['id' => 1, 'title' => 'Gold Badge', 'pointsCost' => 100, 'type' => 'Badge'],
              ['id' => 2, 'title' => 'Completion Certificate', 'pointsCost' => 150, 'type' => 'Certificate'],
              ['id' => 3, 'title' => '10% Discount Voucher', 'pointsCost' => 200, 'type' => 'Discount'],
              ['id' => 4, 'title' => 'Extra Break Time', 'pointsCost' => 50, 'type' => 'Perk'],
          ];
      }
      foreach ($rewards as $r) {
        echo "<div class='card mb-3 reward-card'>
                <div class='card-body'>
                  <h5>🏅 {$r['title']}</h5>
                  <p>Cost: {$r['pointsCost']} points</p>
                  <button class='btn btn-primary redeem-btn' data-id='{$r['id']}'>🎉 Redeem</button>
                </div>
              </div>";
      }
      ?>
    </div>
  </main>
  <div id="toast-container"></div>
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
  <script>
    function showToast(message) {
      const toast = document.createElement('div');
      toast.classList.add('toast');
      toast.textContent = message;
      document.getElementById('toast-container').appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
    function showConfetti() {
      confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
    }
    document.querySelectorAll('.redeem-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const rewardID = btn.dataset.id;
        fetch('../../Controllers/RewardsController.php?action=redeem&id=' + rewardID)
          .then(response => response.json())
          .then(data => {
            if(data.success) {
              showToast(`🎉 Redeemed: ${data.reward}`);
              showConfetti();
              document.getElementById('balance').textContent = data.newBalance;
            } else {
              showToast('⚠️ ' + data.message);
            }
          })
          .catch(() => showToast('⚠️ Error redeeming reward.'));
      });
    });
  </script>
</body>
</html>