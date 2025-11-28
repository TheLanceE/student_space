<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Admin Dashboard | EduMind+</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
   body {
     background: #f8f9fa;
     min-height: 100vh;
   }
   
   .navbar {
     background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
     box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
     border-bottom: 3px solid #1e3c72;
   }
   
   .navbar-brand {
     font-weight: 700;
     font-size: 1.2rem;
     display: flex;
     align-items: center;
     gap: 0.5rem;
     letter-spacing: 0.5px;
   }
   
   .nav-link {
     position: relative;
     transition: all 0.2s ease;
     font-weight: 500;
   }
   
   .nav-link::after {
     content: '';
     position: absolute;
     bottom: 0;
     left: 50%;
     width: 0;
     height: 2px;
     background: white;
     transition: all 0.2s ease;
     transform: translateX(-50%);
   }
   
   .nav-link:hover::after,
   .nav-link.active::after {
     width: 70%;
   }
   
   .stat {
     background: white;
     padding: 1.5rem;
     border-radius: 10px;
     box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
     transition: all 0.3s ease;
     border-left: 4px solid #1e3c72;
     position: relative;
     overflow: hidden;
   }
   
   .stat::before {
     content: '';
     position: absolute;
     top: 0;
     right: 0;
     width: 60px;
     height: 60px;
     background: linear-gradient(135deg, rgba(30, 60, 114, 0.1), transparent);
     border-radius: 0 10px 0 100%;
   }
   
   .stat:hover {
     transform: translateY(-3px);
     box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
   }
   
   .col-6:nth-child(1) .stat { border-left-color: #007bff; animation: fadeInUp 0.4s ease-out; }
   .col-6:nth-child(2) .stat { border-left-color: #28a745; animation: fadeInUp 0.5s ease-out; }
   .col-6:nth-child(3) .stat { border-left-color: #ffc107; animation: fadeInUp 0.6s ease-out; }
   .col-6:nth-child(4) .stat { border-left-color: #dc3545; animation: fadeInUp 0.7s ease-out; }
   
   .stat .text-muted {
     font-size: 0.85rem;
     font-weight: 600;
     text-transform: uppercase;
     letter-spacing: 0.5px;
     margin-bottom: 0.5rem;
   }
   
   .stat .h4 {
     font-weight: 700;
     color: #2c3e50;
     font-size: 2rem;
   }
   
   .btn-outline-light {
     border-radius: 6px;
     transition: all 0.2s ease;
     border: 2px solid white;
     font-weight: 500;
   }
   
   .btn-outline-light:hover {
     transform: translateY(-1px);
     background: white;
     color: #1e3c72;
     box-shadow: 0 4px 10px rgba(255, 255, 255, 0.3);
   }
   
   @keyframes fadeInUp {
     from {
       opacity: 0;
       transform: translateY(15px);
     }
     to {
       opacity: 1;
       transform: translateY(0);
     }
   }
 </style>
</head>
<body data-page="admin-dashboard">
 <nav class="navbar navbar-expand-lg navbar-dark">
 <div class="container-fluid">
 <a class="navbar-brand" href="#">
   <i class="bi bi-shield-check"></i>
   EduMind+ Admin
 </a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php"><i class="bi bi-journal-text me-1"></i>Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm">
   <i class="bi bi-box-arrow-right me-1"></i>Logout
 </button>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="row g-3">
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-people-fill me-1"></i>Students
     </div>
     <div id="sCount" class="h4 mb-0">-</div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-person-workspace me-1"></i>Teachers
     </div>
     <div id="tCount" class="h4 mb-0">-</div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-book-half me-1"></i>Courses
     </div>
     <div id="cCount" class="h4 mb-0">-</div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-hourglass-split me-1"></i>Pending Approvals
     </div>
     <div id="pCount" class="h4 mb-0">-</div>
   </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/data-admin.js"></script>
 <script src="assets/js/auth-admin.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>