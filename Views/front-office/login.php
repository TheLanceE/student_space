<?php
// No session_start needed - handled by authentication flow
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EduMind+ | Login</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    body::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
      animation: moveBackground 15s ease-in-out infinite;
    }
    
    @keyframes moveBackground {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(-50px, -50px); }
    }
    
    .login-container {
      position: relative;
      z-index: 1;
      animation: slideInUp 0.8s ease-out;
      max-width: 450px;
      margin: 0 auto;
      padding: 1rem;
    }
    
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: none;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      max-width: 100%;
    }
    
    .login-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      border-radius: 0.75rem 0.75rem 0 0;
      text-align: center;
    }
    
    .login-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .login-header p {
      margin: 0.5rem 0 0 0;
      opacity: 0.9;
      font-size: 0.9rem;
    }
    
    .form-floating {
      position: relative;
      margin-bottom: 1rem;
    }
    
    .form-floating input {
      border: 2px solid #e2e8f0;
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      height: 50px;
      transition: all 0.3s ease;
    }
    
    .form-floating input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
      transform: translateY(-2px);
    }
    
    .form-floating label {
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
    }
    
    .btn-login {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 0.75rem 1rem;
      height: 50px;
      font-weight: 600;
      letter-spacing: 0.5px;
      position: relative;
      overflow: hidden;
    }
    
    .btn-login::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.4);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .btn-login:hover::after {
      width: 300px;
      height: 300px;
    }
    
    .btn-register {
      background: white;
      border: 2px solid #667eea;
      color: #667eea;
      padding: 0.75rem 1rem;
      height: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-register:hover {
      background: #667eea;
      color: white;
      transform: translateY(-2px);
    }
    
    .alert {
      border-radius: 0.5rem;
      animation: slideInDown 0.5s ease-out;
    }
    
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .footer-text {
      color: white;
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
      animation: fadeIn 1.5s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .floating-shapes {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
    }
    
    .shape {
      position: absolute;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      animation: float 20s ease-in-out infinite;
    }
    
    .shape:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 10%;
      left: 10%;
      animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
      width: 120px;
      height: 120px;
      top: 70%;
      right: 20%;
      animation-delay: 2s;
    }
    
    .shape:nth-child(3) {
      width: 60px;
      height: 60px;
      bottom: 10%;
      left: 30%;
      animation-delay: 4s;
    }
    
    @keyframes float {
      0%, 100% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-30px) rotate(180deg);
      }
    }
  </style>
</head>
<body>
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>
  
  <div class="login-container">
    <div class="card login-card">
      <div class="login-header">
        <h1>🎓 EduMind+</h1>
        <p>Student Portal</p>
      </div>
      
      <div class="card-body p-4">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger border-0">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_GET['timeout'])) {
            echo '<div class="alert alert-warning border-0">⏱️ Session expired. Please login again.</div>';
        }
        if (isset($_GET['registered'])) {
            echo '<div class="alert alert-success border-0">✅ Registration successful! Please login.</div>';
        }
        ?>
        
        <form method="POST" action="../../Controllers/login_handler.php">
          <input type="hidden" name="role" value="student">
          
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <label for="username">👤 Username</label>
          </div>
          
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">🔒 Password</label>
          </div>
          
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-login text-white">
              <span style="position: relative; z-index: 1;">Sign In</span>
            </button>
            <button type="button" class="btn btn-outline-light" onclick="loginWithGoogle()" style="color: #333; border-color: #ddd; background: rgba(255,255,255,0.9);">
              <i class="bi bi-google"></i> Sign in with Google
            </button>
            <a class="btn btn-register" href="register.php">Create New Account</a>
            <a class="btn btn-outline-secondary" href="../../Views/index.php" style="border: 2px solid rgba(255,255,255,0.8); color: rgba(255,255,255,0.95); background: rgba(0,0,0,0.2); font-weight: 500;">
              <i class="bi bi-house-door"></i> Back to Home
            </a>
          </div>
        </form>
      </div>
    </div>
    
    <p class="text-center mt-3 footer-text">
      <small>🌟 Excellence in Education | Powered by EduMind+</small>
    </p>
  </div>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script>
    function loginWithGoogle() {
      // Set OAuth role for student
      fetch('../../Controllers/set_oauth_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role: 'student' })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = '../../Controllers/google_oauth_start.php';
        }
      })
      .catch(err => console.error('Error setting OAuth role:', err));
    }
    
    // Add input focus effects
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    });
    
    // Add button click effect
    document.querySelector('.btn-login').addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.6);
        width: 100px;
        height: 100px;
        margin-top: -50px;
        margin-left: -50px;
        animation: ripple 0.6s;
        pointer-events: none;
      `;
      ripple.style.left = e.clientX - this.offsetLeft + 'px';
      ripple.style.top = e.clientY - this.offsetTop + 'px';
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  </script>
</body>
</html>
