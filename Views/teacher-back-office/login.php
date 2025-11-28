<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Teacher Login</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Floating shapes animation */
    .shape {
      position: absolute;
      opacity: 0.1;
      animation: float 20s infinite ease-in-out;
    }
    .shape:nth-child(1) {
      width: 80px;
      height: 80px;
      background: #fff;
      border-radius: 50%;
      top: 10%;
      left: 10%;
      animation-delay: 0s;
    }
    .shape:nth-child(2) {
      width: 60px;
      height: 60px;
      background: #fff;
      border-radius: 30%;
      top: 60%;
      left: 80%;
      animation-delay: 4s;
    }
    .shape:nth-child(3) {
      width: 100px;
      height: 100px;
      background: #fff;
      transform: rotate(45deg);
      top: 80%;
      left: 20%;
      animation-delay: 2s;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-30px) rotate(180deg); }
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      padding: 2.5rem;
      max-width: 450px;
      width: 100%;
      animation: slideDown 0.6s ease-out;
      position: relative;
      z-index: 10;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-header h1 {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .login-header p {
      color: #6c757d;
      font-size: 0.95rem;
    }
    
    .form-floating input {
      height: 50px;
      border-radius: 10px;
      border: 2px solid #e0e0e0;
      transition: all 0.3s ease;
    }
    
    .form-floating input:focus {
      border-color: #11998e;
      box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
      transform: scale(1.02);
    }
    
    .form-floating label {
      color: #6c757d;
    }
    
    .btn-login {
      height: 50px;
      border-radius: 10px;
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      border: none;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
    }
    
    .btn-register {
      height: 50px;
      border-radius: 10px;
      border: 2px solid #11998e;
      color: #11998e;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-register:hover {
      background: #11998e;
      color: white;
      transform: translateY(-2px);
    }
    
    .btn-home {
      height: 50px;
      border-radius: 10px;
      border: 2px solid #11998e;
      background: transparent;
      color: #11998e;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-home:hover {
      background: rgba(17, 153, 142, 0.1);
      color: #11998e;
      transform: translateY(-2px);
    }
    
    .icon-wrapper {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      animation: pulse 2s infinite;
    }
    
    .icon-wrapper i {
      font-size: 1.8rem;
      color: white;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
  </style>
</head>
<body data-page="teacher-login">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  
  <div class="login-card">
    <div class="login-header">
      <div class="icon-wrapper">
        <i class="bi bi-person-workspace"></i>
      </div>
      <h1>👨‍🏫 Teacher Portal</h1>
      <p>Access your teaching dashboard</p>
    </div>
    
    <form method="POST" action="../../Controllers/teacher_login_handler.php" class="needs-validation" novalidate>
      <div class="form-floating mb-3">
      <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
        <label for="username"><i class="bi bi-person me-2"></i>Username</label>
        <div class="invalid-feedback">Please enter your username.</div>
      </div>
      
      <div class="form-floating mb-4">
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
        <div class="invalid-feedback">Please enter your password.</div>
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-login text-white">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
        <a class="btn btn-register" href="register.php">
          <i class="bi bi-person-plus me-2"></i>Create Teacher Account
        </a>
        <a class="btn btn-home" href="../../Views/index.php">
          <i class="bi bi-house me-2"></i>Back to Home
        </a>
      </div>
    </form>
  </div>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  
  <script>
    // Button ripple effect
    document.querySelectorAll('.btn').forEach(button => {
      button.addEventListener('click', function(e) {
        let ripple = document.createElement('span');
        ripple.classList.add('ripple');
        this.appendChild(ripple);
        let x = e.clientX - e.target.offsetLeft;
        let y = e.clientY - e.target.offsetTop;
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        setTimeout(() => ripple.remove(), 600);
      });
    });
    
    // Input focus animation
    document.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    });
  </script>
</body>
</html>
