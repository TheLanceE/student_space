<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Launchpad</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
  <link href="../shared-assets/css/navbar-styles.css" rel="stylesheet">
  <style>
    body.launchpad {
      background: 
        radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.02), transparent 35%),
        radial-gradient(circle at 80% 0%, rgba(16, 185, 129, 0.02), transparent 30%),
        radial-gradient(circle at 50% 80%, rgba(124, 58, 237, 0.02), transparent 35%),
        var(--bg-body);
      color: var(--text-primary);
      min-height: 100vh;
    }

    /* Dark mode launchpad background */
    :root[data-theme="dark"] body.launchpad,
    body.launchpad[data-theme="dark"] {
      background: 
        radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.12), transparent 35%),
        radial-gradient(circle at 80% 0%, rgba(52, 211, 153, 0.1), transparent 30%),
        radial-gradient(circle at 50% 80%, rgba(139, 92, 246, 0.08), transparent 35%),
        var(--bg-body) !important;
    }

    .hero {
      position: relative;
      overflow: hidden;
      padding: 4rem 0 3rem;
      background: var(--gradient-primary);
      color: #fff;
      isolation: isolate;
    }

    .hero::before,
    .hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1), transparent 60%);
      opacity: 0.85;
      z-index: 0;
    }

    .hero .shape {
      position: absolute;
      width: 240px;
      height: 240px;
      border-radius: 30%;
      filter: blur(40px);
      opacity: 0.45;
      animation: floaty 18s ease-in-out infinite;
      z-index: 0;
    }

    .hero .shape.one { background: rgba(255,255,255,0.25); top: -60px; left: -40px; animation-delay: 0s; }
    .hero .shape.two { background: rgba(16, 185, 129, 0.28); bottom: -80px; right: -40px; animation-delay: 4s; }
    .hero .shape.three { background: rgba(255,255,255,0.18); top: 20%; right: 18%; animation-delay: 2s; }

    @keyframes floaty {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-20px) scale(1.03); }
    }

    .hero-content { position: relative; z-index: 1; }

    .accent-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.9rem;
      border-radius: 999px;
      background: rgba(255,255,255,0.12);
      color: #e2e8f0;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 600;
      font-size: 0.75rem;
      -webkit-backdrop-filter: blur(6px);
      backdrop-filter: blur(6px);
    }

    .glow-card {
      border: 1px solid rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.06);
      color: #fff;
      box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }

    .btn-ghost-light {
      border: 2px solid rgba(255,255,255,0.7);
      color: #fff;
      -webkit-backdrop-filter: blur(4px);
      backdrop-filter: blur(4px);
    }

    .btn-ghost-light:hover { background: rgba(255,255,255,0.12); color: #fff; }

    .cta-stack .btn { min-width: 220px; }

    .showcase-img { height: 200px; object-fit: cover; }

    .card.tile {
      border: 1px solid var(--border-color);
      background: var(--surface-1);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      transition: transform 0.35s ease, box-shadow 0.35s ease;
    }

    .card.tile:hover { 
      transform: translateY(-6px); 
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
      border-color: var(--primary-color);
    }

    .stagger > * { opacity: 0; animation: fadeUp 0.7s ease forwards; }
    .stagger > *:nth-child(1) { animation-delay: 0.05s; }
    .stagger > *:nth-child(2) { animation-delay: 0.15s; }
    .stagger > *:nth-child(3) { animation-delay: 0.25s; }
    .stagger > *:nth-child(4) { animation-delay: 0.35s; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .section-heading small { letter-spacing: 0.12em; text-transform: uppercase; }

    footer { background: var(--surface-1); border-top: 1px solid var(--border-color); }

    /* Dark-mode specific overrides for launchpad */
    :root[data-theme="dark"] .launchpad .card.tile,
    .launchpad[data-theme="dark"] .card.tile {
      background: var(--surface-1) !important;
      border-color: var(--border-color) !important;
    }
    
    /* Fix white cards in dark mode - get started section and trusted by section */
    :root[data-theme="dark"] .launchpad .bg-white,
    .launchpad[data-theme="dark"] .bg-white {
      background: var(--surface-1) !important;
      color: var(--text-primary) !important;
    }
    
    :root[data-theme="dark"] .launchpad .bg-white .text-muted,
    .launchpad[data-theme="dark"] .bg-white .text-muted {
      color: var(--text-muted) !important;
    }
    
    :root[data-theme="dark"] .launchpad .badge.bg-light,
    .launchpad[data-theme="dark"] .badge.bg-light {
      background: var(--surface-2) !important;
      color: var(--text-primary) !important;
    }
    
    .launchpad .text-muted { color: var(--text-secondary) !important; }
    .launchpad .card { 
      background: var(--surface-1) !important; 
      color: var(--text-primary) !important;
      border: 1px solid var(--border-color) !important;
    }
    .launchpad .card.border-0 { border: 1px solid var(--border-color) !important; }
    .launchpad .ad-tile { 
      background: var(--surface-1) !important; 
      color: var(--text-primary) !important; 
      border: 2px dashed var(--border-color) !important;
    }
    .launchpad .badge.text-bg-warning { color: #1f2937; }
    .launchpad .btn-link { color: var(--primary-color); }
    .launchpad h1, .launchpad h2, .launchpad h3, .launchpad h4, .launchpad h5, .launchpad h6 {
      color: var(--text-primary) !important;
    }

    @media (max-width: 991px) {
      .hero { padding: 3rem 0 2.5rem; }
      .cta-stack .btn { width: 100%; }
    }
  </style>
  
</head>
<body class="launchpad">
  <nav class="navbar navbar-expand-lg navbar-dark admin-nav shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">
        <img src="../logo.jpg" alt="EduMind+ Logo" height="45" class="d-inline-block align-middle me-2">
        <span class="d-inline-block align-middle">
          EduMind+ <span class="d-block text-uppercase text-white-50" style="font-size: 0.6rem;">By Weblynx</span>
        </span>
      </a>
      <div class="ms-auto d-none d-lg-flex gap-3 small text-white-50">
        <span>24/7 Support</span>
        <span>5K+ Learners</span>
      </div>
    </div>
  </nav>

  <header class="hero text-center text-lg-start">
    <div class="shape one"></div>
    <div class="shape two"></div>
    <div class="shape three"></div>
    <div class="container hero-content">
      <span class="accent-pill mb-3"><span>✨</span> New · AI-ready Learning OS</span>
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <h1 class="display-4 fw-bold mb-3">Launch immersive learning journeys for students, teachers, and admins in one click.</h1>
          <p class="lead mb-4 text-light">EduMind+ centralizes quizzes, insights, and governance so every campus can personalize education—even offline.</p>
          <div class="d-flex flex-wrap gap-3 cta-stack">
            <a class="btn btn-warning btn-lg shadow-sm" href="front-office/index.php">Explore Student Space</a>
            <a class="btn btn-ghost-light btn-lg" href="teacher-back-office/index.php">Teacher Workspace</a>
            <a class="btn btn-ghost-light btn-lg" href="admin-back-office/index.php">Admin Console</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card glow-card border-0">
            <div class="card-body">
              <p class="fw-semibold text-uppercase small mb-1 text-white-50">Live Performance Snapshot</p>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h2 class="h1 mb-0">92%</h2>
                  <small class="text-success">▲ Avg. mastery</small>
                </div>
                <img src="../shared-assets/img/dashboard-preview.jpg" alt="Dashboard preview" class="img-fluid rounded" style="max-width: 180px; border: 1px solid rgba(255,255,255,0.2);">
              </div>
              <div class="progress" style="height: .5rem;">
                <div class="progress-bar bg-warning" style="width: 78%;"></div>
              </div>
              <div class="d-flex justify-content-between small mt-2 text-white-50">
                <span>Curriculum coverage</span>
                <span>78%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container my-5 flex-grow-1">
    <section class="mb-5">
      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <div class="p-4 rounded-4 shadow-sm bg-white">
            <h3 class="h4 mb-2">Get started in minutes</h3>
            <p class="text-muted mb-3">Spin up your campus on EduMind+: unified roles, offline-first data, and export-ready insights.</p>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-primary" href="front-office/register.php">Create Student Account</a>
              <a class="btn btn-outline-primary" href="teacher-back-office/register.php">Invite a Teacher</a>
              <a class="btn btn-outline-dark" href="admin-back-office/login.php">Admin Login</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="p-4 rounded-4 shadow-sm bg-white h-100">
            <p class="text-uppercase small text-muted mb-2">Trusted by</p>
            <div class="d-flex flex-wrap align-items-center gap-3">
              <span style="font-weight:600;">Districts</span>
              <span class="badge bg-light text-dark">K-12</span>
              <span class="badge bg-light text-dark">STEM</span>
              <span class="badge bg-light text-dark">Adult Ed</span>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="mb-5">
      <div class="text-center mb-4">
        <p class="text-uppercase text-primary fw-semibold small">Trending learning journeys</p>
        <h2 class="fw-bold">Pick a workspace to get started</h2>
      </div>
      <div class="row g-4 stagger">
        <div class="col-12 col-md-4">
          <a class="card-link" href="front-office/index.php">
            <div class="card tile h-100">
              <img src="../shared-assets/img/student-portal.jpg" class="showcase-img" alt="Student portal" loading="lazy">
              <div class="card-body">
                <h3 class="h5">Student Space</h3>
                <p class="text-muted">Adaptive quizzes, streak-based motivation, and personalized recommendations.</p>
                <span class="btn btn-link px-0">Launch Student Experience →</span>
              </div>
            </div>
          </a>
        </div>
        <div class="col-12 col-md-4">
          <a class="card-link" href="teacher-back-office/index.php">
            <div class="card tile h-100">
              <img src="../shared-assets/img/teacher-workspace.jpg" class="showcase-img" alt="Teacher workspace" loading="lazy">
              <div class="card-body">
                <h3 class="h5">Teacher Workspace</h3>
                <p class="text-muted">Create quizzes in minutes, monitor cohorts, and export progress snapshots.</p>
                <span class="btn btn-link px-0">Go to Teacher Hub →</span>
              </div>
            </div>
          </a>
        </div>
        <div class="col-12 col-md-4">
          <a class="card-link" href="admin-back-office/index.php">
            <div class="card tile h-100">
              <img src="../shared-assets/img/admin-console.jpg" class="showcase-img" alt="Admin console" loading="lazy">
              <div class="card-body">
                <h3 class="h5">Admin Console</h3>
                <p class="text-muted">Approve content, track logs, manage roles, and export compliance-ready reports.</p>
                <span class="btn btn-link px-0">Open Admin Console →</span>
              </div>
            </div>
          </a>
        </div>
      </div>
    </section>

    <section class="mb-5">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <div class="ad-tile rounded-4 p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-uppercase text-muted small">Sponsored</span>
              <span class="badge text-bg-warning text-dark">Ad</span>
            </div>
            <h3 class="h4">Weblynx Studio · Campus Launch Pack</h3>
            <p class="text-muted">Deploy EduMind+ with branded themes, onboarding videos, and plug-and-play analytics in under 72 hours.</p>
            <ul class="list-unstyled small text-muted mb-3">
              <li>✔ Custom theming for front + back office</li>
              <li>✔ Optional cloud sync adapters</li>
              <li>✔ Dedicated implementation squad</li>
            </ul>
            <a class="btn btn-warning" href="mailto:contact@weblinx.studio?subject=EduMind+%20Launch%20Pack">Book a launch sprint</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <p class="text-uppercase text-primary fw-semibold small mb-2">Spotlight</p>
              <h3 class="h4">STEM Showcase: Project Nova</h3>
              <p class="text-muted">See how District 45 used EduMind+ challenges to double math mastery in one semester.</p>
              <div class="ratio ratio-16x9 rounded overflow-hidden mb-3">
                <img src="../shared-assets/img/stem-showcase.jpg" alt="STEM showcase" class="w-100 h-100 object-fit-cover">
              </div>
              <div class="d-flex gap-3 flex-wrap small text-muted">
                <span>📍 Austin, TX</span>
                <span>👩‍🏫 64 Teachers onboarded</span>
                <span>🧠 2300+ personalized quizzes</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mb-5">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <p class="text-uppercase text-primary small mb-2">Why EduMind+</p>
              <h3 class="h4">Unified roles, unified data</h3>
              <p class="text-muted">Students, teachers, and admins tap into the same local-first database, making reporting instant and reliable.</p>
              <ul class="small text-muted list-unstyled mb-0">
                <li>• Offline-first local database</li>
                <li>• Role-based dashboards</li>
                <li>• Export-ready analytics</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <p class="text-uppercase text-success small mb-2">Testimonials</p>
              <figure>
                <blockquote class="blockquote">“Weblynx shipped EduMind+ in a week. Our teachers now build quizzes between classes—and our district loves the score exports.”</blockquote>
                <figcaption class="blockquote-footer mb-0">Aisha Karim · Digital Learning Lead</figcaption>
              </figure>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <p class="text-uppercase text-warning small mb-2">Integrations</p>
              <div class="logo-grid d-flex flex-wrap gap-4">
                <span style="font-size: 1.5rem; font-weight: bold; color: #4285F4;">Google</span>
                <img src="../shared-assets/img/react-icon.svg" alt="React" height="30">
                <span style="font-size: 1.5rem; font-weight: bold; color: #00A4EF;">Microsoft</span>
                <span style="font-size: 1.5rem; font-weight: bold; color: #000;">Blackboard</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="py-4 mt-auto">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
      <div>
        &copy; <span id="year"></span> EduMind+ · Crafted by Weblynx. All rights reserved.
      </div>
      <div class="text-center text-lg-end">
        <div>Contact the team: <a href="mailto:contact@weblinx.studio">contact@weblinx.studio</a></div>
        <div class="small">GitHub: <a href="https://github.com/TheLanceE/student_space" target="_blank" rel="noopener">github.com/TheLanceE/student_space</a></div>
      </div>
    </div>
  </footer>

  <script>
    // Respect OS theme without storing in localStorage
    (function(){
      const root = document.documentElement;
      const apply = (isDark) => root.setAttribute('data-theme', isDark ? 'dark' : 'light');
      const mq = window.matchMedia('(prefers-color-scheme: dark)');
      apply(mq.matches);
      mq.addEventListener('change', (e)=> apply(e.matches));
    })();

    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
