<?php
require_once '../../Controllers/config.php';
$csrfToken = SessionManager::getCSRFToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Admin Login</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Subtle grid pattern for professional look */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
      background-size: 50px 50px;
      animation: gridMove 20s linear infinite;
    }
    
    @keyframes gridMove {
      0% { transform: translate(0, 0); }
      100% { transform: translate(50px, 50px); }
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.98);
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      padding: 2.5rem;
      max-width: 450px;
      width: 100%;
      animation: fadeInUp 0.6s ease-out;
      position: relative;
      z-index: 10;
      border-top: 4px solid #1e3c72;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
      border-bottom: 2px solid #f0f0f0;
      padding-bottom: 1.5rem;
    }
    
    .login-header h1 {
      color: #1e3c72;
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      letter-spacing: -0.5px;
    }
    
    .login-header p {
      color: #6c757d;
      font-size: 0.9rem;
      margin: 0;
    }
    
    .icon-wrapper {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
    }
    
    .icon-wrapper i {
      font-size: 2rem;
      color: white;
    }
    
    .form-floating input {
      height: 50px;
      border-radius: 8px;
      border: 1px solid #d0d0d0;
      transition: all 0.3s ease;
      background: #f8f9fa;
    }
    
    .form-floating input:focus {
      border-color: #1e3c72;
      box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
      background: white;
    }
    
    .form-floating label {
      color: #6c757d;
    }
    
    .btn-login {
      height: 50px;
      border-radius: 8px;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      border: none;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(30, 60, 114, 0.4);
    }
    
    .btn-home {
      height: 50px;
      border-radius: 8px;
      border: 1px solid #1e3c72;
      background: transparent;
      color: #1e3c72;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-home:hover {
      background: rgba(30, 60, 114, 0.05);
      color: #1e3c72;
      border-color: #2a5298;
    }
    
    .security-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      color: #28a745;
      font-size: 0.85rem;
      margin-top: 1rem;
      padding: 0.5rem;
      background: rgba(40, 167, 69, 0.05);
      border-radius: 6px;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body data-page="admin-login">
  <div class="login-card">
    <div class="login-header">
      <div class="icon-wrapper">
        <i class="bi bi-shield-lock"></i>
      </div>
      <h1>🔐 Administrator Access</h1>
      <p>Secure system management portal</p>
    </div>
    
    <?php
    $error = $_GET['error'] ?? '';
    $errorMessages = [
        'rate_limited' => 'Too many login attempts. Please wait a minute before trying again.',
        'csrf' => 'Invalid session token. Please try again.',
        'invalid' => 'Invalid username or password.',
        'empty' => 'Please enter username and password.',
        'db' => 'A system error occurred. Please try again later.'
    ];
    if ($error && isset($errorMessages[$error])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <?php echo htmlspecialchars($errorMessages[$error], ENT_QUOTES, 'UTF-8'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="../../Controllers/admin_login_handler.php" class="needs-validation" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="form-floating mb-3">
      <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
        <label for="username"><i class="bi bi-person-badge me-2"></i>Administrator Username</label>
        <div class="invalid-feedback">Please enter your username.</div>
      </div>
      
      <div class="form-floating mb-4">
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        <label for="password"><i class="bi bi-key me-2"></i>Password</label>
        <div class="invalid-feedback">Please enter your password.</div>
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-login text-white">
          <i class="bi bi-shield-check me-2"></i>Secure Sign In
        </button>
        <a class="btn btn-home" href="../../Views/index.php">
          <i class="bi bi-house me-2"></i>Back to Home
        </a>
      </div>
      
      <div class="security-badge">
        <i class="bi bi-lock-fill"></i>
        <span>Encrypted Connection</span>
      </div>
    </form>
  </div>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  
  <script>
    // Subtle input focus effect
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateX(2px)';
      });
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateX(0)';
      });
    });
  </script>
</body>
</html>