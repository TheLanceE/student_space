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
    window.location.replace(user ? 'dashboard.html' : 'login.html');
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
      const username = document.getElementById('regUsername').value.trim();
      Auth.register(username);
    });
  };
  const renderSkillLevels = (scores) => {
    const list = document.getElementById('skillList');
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
  const handleDashboard = () => {
    requireAuth();
    safeBindLogout('logoutBtn');
    const currentUser = getCurrentUser();
    const scores = getScoresForCurrentUser();
    if(window.Charts){ Charts.renderProgress('progressChart', scores); }
    if(window.UI){ UI.renderRecentResults('recentResults', scores); }
    if(window.SuggestionEngine && currentUser){
      const suggestions = SuggestionEngine.generate(currentUser);
      UI.renderSuggestions('suggestionsList', suggestions);
    }
    renderSkillLevels(scores);
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
    if(!user){ window.location.replace('login.html'); return; }
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
