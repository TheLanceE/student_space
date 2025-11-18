(function(){
  const body = () => document.body || null;
  const getCurrentUser = () => (window.Storage ? Storage.get('currentUser') : null);
  const getScoresForCurrentUser = () => {
    const user = getCurrentUser();
    if(!user || !window.Data || typeof Data.getScoresForUser !== 'function') return [];
    return Data.getScoresForUser(user.id);
  };
  const requireAuth = () => {
    if(window.UI && typeof UI.requireAuth === 'function'){ UI.requireAuth(); }
  };
  const safeBindLogout = (id) => {
    if(window.UI && typeof UI.bindLogout === 'function'){ UI.bindLogout(id); }
  };
  const handleIndex = () => {
    const user = JSON.parse(localStorage.getItem('currentUser') || 'null');
    window.location.replace(user ? 'dashboard.php' : 'login.php');
  };
  const handleLogin = () => {
    const form = document.getElementById('loginForm');
    if(!form || !window.Auth) return;
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const username = document.getElementById('username').value.trim();
      Auth.login(username);
    });
  };
  const handleRegister = () => {
    const form = document.getElementById('registerForm');
    if(!form || !window.Auth) return;
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const formData = {
        login: document.getElementById('regLogin').value.trim(),
        fullName: document.getElementById('regFullName').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        mobile: document.getElementById('regMobile').value.trim(),
        address: document.getElementById('regAddress').value.trim(),
        gradeLevel: document.getElementById('regGrade').value
      };
      Auth.register(formData);
    });
  };
  const renderScoreLevels = (scores) => {
    const list = document.getElementById('scoresList');
    if(!list || !window.Data) return;
    const courses = Data.courses || [];
    if(!courses.length){
      list.innerHTML = '<li class="list-group-item">No subjects configured yet.</li>';
      return;
    }
    const aggregates = courses.map(course => {
      const courseScores = scores.filter(s => s.courseId === course.id);
      const pct = courseScores.length ? Math.round(courseScores.reduce((sum, s)=> sum + ((s.score / s.total) * 100), 0) / courseScores.length) : 0;
      let label = 'New';
      let badge = 'bg-secondary';
      if(pct >= 80){ label = 'Advanced'; badge = 'bg-success'; }
      else if(pct >= 60){ label = 'Intermediate'; badge = 'bg-info text-dark'; }
      else if(pct > 0){ label = 'Beginner'; badge = 'bg-warning text-dark'; }
      return { title: course.title, pct, label, badge };
    });
    list.innerHTML = '';
    aggregates.forEach(item => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      li.innerHTML = `<span>${item.title}</span><span><strong>${item.pct}%</strong> <span class="badge ${item.badge}">${item.label}</span></span>`;
      list.appendChild(li);
    });
  };

  const renderUpcomingEvents = () => {
    const container = document.getElementById('upcomingEvents');
    if(!container || !window.Database) return;
    const events = Database.table('events') || [];
    const today = new Date().toISOString().split('T')[0];
    const upcoming = events.filter(e => e.date >= today).sort((a, b) => {
      const dateCompare = a.date.localeCompare(b.date);
      return dateCompare !== 0 ? dateCompare : a.startTime.localeCompare(b.startTime);
    }).slice(0, 3);
    
    if(!upcoming.length){
      container.innerHTML = '<p class="text-muted small">No upcoming events scheduled.</p>';
      return;
    }
    
    container.innerHTML = upcoming.map(e => {
      const eventDate = new Date(e.date);
      const dateStr = eventDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
      return `
        <div class="card mb-2 border-start border-primary border-4">
          <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">${e.title}</h6>
                <p class="mb-0 small text-muted">
                  <span class="badge bg-${e.type === 'Lecture' ? 'info' : e.type === 'Quiz' ? 'warning' : 'secondary'}">${e.type}</span>
                  ${dateStr} · ${e.startTime.substring(0, 5)} - ${e.endTime.substring(0, 5)}
                  ${e.location ? ' · ' + e.location : ''}
                </p>
              </div>
            </div>
          </div>
        </div>`;
    }).join('');
  };

  const handleDashboard = () => {
    requireAuth();
    safeBindLogout('logoutBtn');
    const currentUser = getCurrentUser();
    const scores = getScoresForCurrentUser();
    
    // Populate student info
    const nameEl = document.getElementById('studentName');
    const gradeEl = document.getElementById('studentGrade');
    if(nameEl && currentUser) nameEl.textContent = currentUser.fullName || currentUser.username;
    if(gradeEl && currentUser) gradeEl.textContent = currentUser.gradeLevel || 'Grade N/A';
    
    if(window.Charts){ Charts.renderProgress('progressChart', scores); }
    if(window.UI){ UI.renderRecentResults('recentResults', scores); }
    if(window.SuggestionEngine && currentUser){
      const suggestions = SuggestionEngine.generate(currentUser);
      UI.renderSuggestions('suggestionsList', suggestions);
    }
    renderScoreLevels(scores);
    renderUpcomingEvents();
  };
  const handleCourses = () => {
    requireAuth();
    safeBindLogout('logoutBtn');
    if(window.UI && window.Data){ UI.renderCourses('courseList', Data.courses); }
  };
  const handleProfile = () => {
    requireAuth();
    safeBindLogout('logoutBtn');
    const user = getCurrentUser();
    if(!user){ window.location.replace('login.php'); return; }
    const scores = getScoresForCurrentUser();
    const info = document.getElementById('profileInfo');
    if(info){
      info.innerHTML = `
        <dt class="col-5">Username</dt><dd class="col-7">${user.username}</dd>
        <dt class="col-5">Joined</dt><dd class="col-7">${new Date(user.createdAt).toLocaleDateString()}</dd>
        <dt class="col-5">Last Login</dt><dd class="col-7">${new Date(user.lastLoginAt).toLocaleString()}</dd>
        <dt class="col-5">Quizzes Taken</dt><dd class="col-7">${scores.length}</dd>`;
    }
    if(window.Charts){ Charts.renderHistory('historyChart', scores); }
    if(window.UI){ UI.renderRecentResults('allResults', scores); }
  };
  const handleQuiz = () => {
    requireAuth();
    safeBindLogout('logoutBtn');
  };

  const handlers = {
    'front-index': handleIndex,
    'front-login': handleLogin,
    'front-register': handleRegister,
    'front-dashboard': handleDashboard,
    'front-courses': handleCourses,
    'front-profile': handleProfile,
    'front-quiz': handleQuiz
  };

  document.addEventListener('DOMContentLoaded', ()=>{
    const page = body()?.dataset?.page;
    if(page && handlers[page]){
      handlers[page]();
    }
  });
})();
