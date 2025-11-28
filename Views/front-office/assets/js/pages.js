(function(){
  console.log('PAGES.JS LOADED');
  const body = () => document.body || null;
  
  // Get current user from server session (async)
  const getCurrentUser = async () => {
    if(!window.Auth) return null;
    return await Auth.current();
  };
  
  const getScoresForCurrentUser = () => {
    // TODO: Fetch scores from server API
    return [];
  };
  
  const requireAuth = async () => {
    if(window.Auth){
      const isAuth = await Auth.checkAuth();
      if(!isAuth){
        window.location.href = 'login.php';
      }
    }
  };
  
  const safeBindLogout = (id) => {
    const btn = document.getElementById(id);
    if(btn && window.Auth){
      btn.addEventListener('click', () => Auth.logout());
    }
  };
  
  const handleIndex = async () => {
    if(!window.Auth) {
      window.location.href = 'login.php';
      return;
    }
    const isAuth = await Auth.checkAuth();
    window.location.replace(isAuth ? 'dashboard.php' : 'login.php');
  };
  
  const handleLogin = () => {
    const form = document.getElementById('loginForm');
    if(!form || !window.Auth) return;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const username = document.getElementById('username').value.trim();
      const passwordField = document.getElementById('password');
      const password = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      
      await Auth.login(username, password);
    });
  };
  
  const handleRegister = () => {
    const form = document.getElementById('registerForm');
    if(!form || !window.Auth) return;
    
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      
      const passwordField = document.getElementById('regPassword');
      const passwordValue = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      
      const formData = {
        login: document.getElementById('regLogin').value.trim(),
        password: passwordValue,
        fullName: document.getElementById('regFullName').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        mobile: document.getElementById('regMobile').value.trim(),
        address: document.getElementById('regAddress').value.trim(),
        gradeLevel: document.getElementById('regGrade').value
      };
      
      await Auth.register(formData);
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
    if(!container) return;
    // TODO: Fetch events from server API
    container.innerHTML = '<p class="text-muted small">Loading events...</p>';
  };

  const handleDashboard = async () => {
    await requireAuth();
    safeBindLogout('logoutBtn');
    const currentUser = await getCurrentUser();
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
  
  const handleCourses = async () => {
    await requireAuth();
    safeBindLogout('logoutBtn');
    if(window.UI && window.Data){ UI.renderCourses('courseList', Data.courses); }
  };
  
  const handleProfile = async () => {
    await requireAuth();
    safeBindLogout('logoutBtn');
    const user = await getCurrentUser();
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
    
    // Handle delete account
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if(confirmDeleteBtn && user){
      confirmDeleteBtn.addEventListener('click', async () => {
        const password = document.getElementById('confirmPassword').value;
        const checkbox = document.getElementById('confirmDelete');
        
        if(!password){
          alert('Please enter your password');
          return;
        }
        
        if(!checkbox.checked){
          alert('Please confirm you understand this action is permanent');
          return;
        }
        
        try {
          // Verify password first
          const verifyResponse = await fetch('/edumind/Controllers/UserController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'verify_password',
              id: user.id,
              password: password,
              role: 'student'
            })
          });
          
          const verifyResult = await verifyResponse.json();
          
          if(!verifyResult.success || !verifyResult.valid){
            alert('Incorrect password. Please try again.');
            return;
          }
          
          // Delete account
          const deleteResponse = await fetch('/edumind/Controllers/UserController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'delete',
              id: user.id,
              role: 'student'
            })
          });
          
          const deleteResult = await deleteResponse.json();
          
          if(deleteResult.success){
            alert('Your account has been deleted. You will now be logged out.');
            await Auth.logout();
          } else {
            alert('Error deleting account: ' + (deleteResult.error || 'Unknown error'));
          }
        } catch(error){
          console.error('Delete account error:', error);
          alert('An error occurred. Please try again.');
        }
      });
    }
  };
  
  const handleQuiz = async () => {
    await requireAuth();
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

  const handleLogin = () => {
    const form = document.getElementById('loginForm');
    if(!form || !window.Auth) return;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const username = document.getElementById('username').value.trim();
      const passwordField = document.getElementById('password');
      const password = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      
      console.log('LOGIN DEBUG: username =', username);
      console.log('LOGIN DEBUG: password =', password);
      console.log('LOGIN DEBUG: password length =', password.length);
      
      await Auth.login(username, password);
    });
  };
  const handleRegister = () => {
    const form = document.getElementById('registerForm');
    if(!form || !window.Auth) return;
    
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      
      const passwordField = document.getElementById('regPassword');
      const passwordValue = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      console.log('DEBUG: Password field:', passwordField);
      console.log('DEBUG: Raw password value:', passwordValue);
      console.log('DEBUG: Password length:', passwordValue.length);
      console.log('DEBUG: Password type:', typeof passwordValue);
      
      const formData = {
        login: document.getElementById('regLogin').value.trim(),
        password: passwordValue,
        fullName: document.getElementById('regFullName').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        mobile: document.getElementById('regMobile').value.trim(),
        address: document.getElementById('regAddress').value.trim(),
        gradeLevel: document.getElementById('regGrade').value
      };
      
      console.log('DEBUG: Complete formData:', formData);
      await Auth.register(formData);
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
