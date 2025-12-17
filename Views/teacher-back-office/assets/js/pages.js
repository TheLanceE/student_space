(function(){
  const body = () => document.body || null;
  const auth = () => window.TAuth;
  const data = () => window.TData;
  const charts = () => window.TCharts;
  const ui = () => window.TUI;
  const randomId = () => window.crypto?.randomUUID?.() ?? `id_${Date.now().toString(36)}${Math.random().toString(36).slice(2,8)}`;
  const slugify = (text, fallback='item') => {
    const base = (text || fallback).toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
    return base || `${fallback}_${Date.now().toString(36)}`;
  };
  
  // HTML escape helper for XSS prevention
  const escapeHtml = (str) => {
    if (str == null) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
  };

  const ensureAuth = () => {
    if(!auth()) return false;
    if(!auth().current()){
      window.location.replace('login.php');
      return false;
    }
    return true;
  };

  const bindLogout = () => {
    const btn = document.getElementById('logoutBtn');
    if(btn && auth()){
      btn.addEventListener('click', ()=> auth().logout());
    }
  };

  const handleIndex = () => {
    const user = auth()?.current();
    window.location.replace(user ? 'dashboard.php' : 'login.php');
  };

  const handleLogin = () => {
    if(!auth()) return;
    const form = document.getElementById('loginForm');
    if(!form) return;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const username = document.getElementById('username').value.trim();
      const passwordField = document.getElementById('password');
      const password = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      
      await auth().login(username, password);
    });
  };

  const handleRegister = () => {
    if(!auth()) return;
    const form = document.getElementById('registerForm');
    if(!form) return;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      // Explicitly capture password field from hidden input
      const passwordField = document.getElementById('regPassword');
      const passwordValue = passwordField ? (passwordField.value || passwordField.getAttribute('value') || '') : '';
      
      const formData = {
        login: document.getElementById('regLogin').value.trim(),
        password: passwordValue,
        fullName: document.getElementById('regFullName').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        mobile: document.getElementById('regMobile').value.trim(),
        address: document.getElementById('regAddress').value.trim(),
        subject: document.getElementById('regSubject').value,
        nationalId: document.getElementById('regNationalId').value.trim()
      };
      auth().register(formData);
    });
  };

  const handleDashboard = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    const scores = teacher ? data().getScoresForTeacher(teacher.id) : [];
    charts()?.renderCourseAverages('courseAverages', scores);
    charts()?.renderAttemptsOverTime('attemptsChart', scores);
  };

  const handleCourses = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const form = document.getElementById('addCourseForm');
    const render = () => {
      const courses = data().getTeacherCourses(teacher.id);
      const quizzes = data().getTeacherQuizzes(teacher.id);
      ui()?.renderCourses('courseList', courses, quizzes, (courseId)=>{
        data().removeCourse(courseId);
        render();
      });
    };
    form?.addEventListener('submit', (e)=>{
      e.preventDefault();
      const title = document.getElementById('cTitle').value.trim();
      const description = document.getElementById('cDesc').value.trim();
      if(!title) return;
      const list = data().courses;
      const baseId = slugify(title, 'course');
      const exists = list.some(c=> c.id === baseId);
      const uniqueId = exists ? `${baseId}_${randomId().slice(-4)}` : baseId;
      const newCourse = { id: uniqueId, title, description, teacherId: teacher.id, status: 'pending', createdAt: new Date().toISOString() };
      if(typeof data().saveCourse === 'function'){
        data().saveCourse(newCourse);
      }
      form.reset();
      render();
    });
    render();
  };

  const handleStudents = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const students = data().students;
    const scores = data().getScoresForTeacher(teacher.id);
    const courses = data().getTeacherCourses(teacher.id);
    ui()?.renderStudentsTable('studentsTable', students, scores);
    const insightsList = document.getElementById('insights');
    if(!insightsList) return;
    insightsList.innerHTML = '';
    const addInsight = (text) => {
      const li = document.createElement('li');
      li.textContent = text;
      insightsList.appendChild(li);
    };
    const totalAttempts = scores.length;
    const allAvg = totalAttempts ? Math.round(scores.map(s=> (s.score/s.total)*100).reduce((a,b)=>a+b,0)/totalAttempts) : 0;
    const topCourse = (()=>{
      const map = {};
      scores.forEach(s=>{ map[s.courseId] = (map[s.courseId]||0) + 1; });
      const entries = Object.entries(map).sort((a,b)=> b[1]-a[1]);
      if(!entries.length) return null;
      const [courseId, count] = entries[0];
      const course = courses.find(c=> c.id === courseId);
      return course ? `${course.title} (${count} attempts)` : `${courseId} (${count} attempts)`;
    })();
    addInsight(`Total quiz attempts: ${totalAttempts}`);
    addInsight(`Average score (all quizzes): ${allAvg}%`);
    if(topCourse) addInsight(`Most active course: ${topCourse}`);
  };

  const handleReports = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const button = document.getElementById('exportBtn');
    if(!button) return;
    const scores = data().getScoresForTeacher(teacher.id);
    const toCSV = (rows) => rows.map(r=> r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    button.addEventListener('click', ()=>{
      const header = ['userId','courseId','quizId','score','total','timestamp'];
      const rows = [header, ...scores.map(s=> [s.userId,s.courseId,s.quizId,s.score,s.total,s.timestamp])];
      const blob = new Blob([toCSV(rows)], { type:'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'edumind_teacher_report.csv';
      a.click();
      URL.revokeObjectURL(url);
    });
  };

  const handleQuizBuilder = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const courseSelect = document.getElementById('courseId');
    const questionsWrap = document.getElementById('questions');
    const addQuestionBtn = document.getElementById('addQuestion');
    const form = document.getElementById('quizForm');
    if(!courseSelect || !questionsWrap || !addQuestionBtn || !form) return;
    courseSelect.innerHTML = '';
    const teacherCourses = data().getTeacherCourses(teacher.id);
    teacherCourses.forEach(c=>{
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.title;
      courseSelect.appendChild(opt);
    });
    if(!teacherCourses.length){
      courseSelect.innerHTML = '<option value="">Create a course first</option>';
      courseSelect.disabled = true;
      addQuestionBtn.disabled = true;
      const submitBtn = form.querySelector('button[type="submit"]');
      if(submitBtn) submitBtn.disabled = true;
      return;
    }
    const urlParams = new URLSearchParams(location.search);
    const preselect = urlParams.get('courseId');
    if(preselect){
      const optionExists = Array.from(courseSelect.options).some(opt => opt.value === preselect);
      if(optionExists) courseSelect.value = preselect;
    }
    const buildQuestionBlock = (index) => {
      const block = document.createElement('div');
      block.className = 'border rounded p-3 mb-3';
      block.innerHTML = `
        <div class="mb-2"><label class="form-label">Question ${index}</label><input class="form-control" name="q_text" placeholder="Question text" required></div>
        <div class="row g-2">
          ${[0,1,2,3].map(i=> `<div class='col-md-6'><input class='form-control' name='q_opt_${i}' placeholder='Option ${i+1}' required></div>`).join('')}
        </div>
        <div class="mt-2">
          <label class="form-label">Correct Option</label>
          <select class="form-select" name="q_correct">${[0,1,2,3].map(i=>`<option value='${i}'>Option ${i+1}</option>`).join('')}</select>
        </div>`;
      return block;
    };
    const addQuestion = () => {
      const block = buildQuestionBlock(questionsWrap.children.length + 1);
      questionsWrap.appendChild(block);
    };
    addQuestionBtn.addEventListener('click', addQuestion);
    addQuestion();
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      const titleInput = document.getElementById('quizTitle');
      const durationInput = document.getElementById('duration');
      if(!titleInput || !durationInput) return;
      const title = titleInput.value.trim() || 'Untitled Quiz';
      const durationSec = parseInt(durationInput.value, 10) || 60;
      const courseId = courseSelect.value;
      const questions = Array.from(questionsWrap.children).map((block)=>{
        const text = block.querySelector('[name="q_text"]').value.trim();
        const options = [0,1,2,3].map(i=> block.querySelector(`[name="q_opt_${i}"]`).value.trim());
        const correctIndex = parseInt(block.querySelector('[name="q_correct"]').value, 10);
        return { id: randomId(), text, options, correctIndex };
      });
      const allQuizzes = data().getTeacherQuizzes(teacher.id);
      const baseId = slugify(title, 'quiz');
      const quizId = allQuizzes.some(q => q.id === baseId) ? `${baseId}_${randomId().slice(-4)}` : baseId;
      const quizPayload = { id: quizId, courseId, title, durationSec, questions, createdBy: teacher.id };
      if(typeof data().overwriteQuiz === 'function'){
        data().overwriteQuiz(quizId, quizPayload);
      } else if(typeof data().saveQuiz === 'function'){
        data().saveQuiz(quizPayload);
      }
      alert('Quiz saved.');
      window.location.href = 'courses.php';
    });
  };

  const handleEvents = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const form = document.getElementById('addEventForm');
    const typeSelect = document.getElementById('type');
    const locationWrapper = document.getElementById('locationWrapper');
    const renderEvents = () => {
      const events = data().getTeacherEvents(teacher.id);
      const list = document.getElementById('eventsList');
      if(!list) return;
      if(!events.length){
        list.innerHTML = '<p class="text-muted">No events yet. Create one above.</p>';
        return;
      }
      list.innerHTML = events.map(e => `
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h3 class="h5">${e.title}</h3>
                <p class="mb-1"><strong>Date:</strong> ${e.date} | <strong>Time:</strong> ${e.startTime} - ${e.endTime}</p>
                <p class="mb-1"><strong>Type:</strong> ${e.type} | <strong>Course:</strong> ${e.course}</p>
                ${e.location ? `<p class="mb-1"><strong>Location:</strong> ${e.location}</p>` : ''}
                <p class="mb-0 text-muted">${e.description || 'No description'}</p>
              </div>
              <button class="btn btn-danger btn-sm" onclick="deleteEvent('${e.id}')">Delete</button>
            </div>
          </div>
        </div>
      `).join('');
    };
    window.deleteEvent = (id) => {
      if(confirm('Delete this event?')){
        data().removeEvent(id);
        renderEvents();
      }
    };
    if(typeSelect && locationWrapper){
      typeSelect.addEventListener('change', () => {
        locationWrapper.style.display = typeSelect.value === 'Lecture' ? 'block' : 'none';
      });
    }
    form?.addEventListener('submit', (e) => {
      e.preventDefault();
      const eventData = {
        id: randomId(),
        title: document.getElementById('title').value.trim(),
        date: document.getElementById('date').value,
        startTime: document.getElementById('startTime').value,
        endTime: document.getElementById('endTime').value,
        course: document.getElementById('course').value.trim(),
        type: document.getElementById('type').value,
        location: document.getElementById('type').value === 'Lecture' ? document.getElementById('location').value.trim() : '',
        maxParticipants: parseInt(document.getElementById('maxParticipants').value, 10),
        description: document.getElementById('description').value.trim(),
        teacherId: teacher.id,
        createdAt: new Date().toISOString()
      };
      data().saveEvent(eventData);
      form.reset();
      renderEvents();
    });
    renderEvents();
  };

  const handleQuizReports = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const teacher = auth()?.current();
    if(!teacher) return;
    const list = document.getElementById('reportsList');
    const pendingBadge = document.getElementById('pendingCount');
    if(!list) return;
    let currentFilter = 'all';
    const render = () => {
      const allReports = data().quizReports || [];
      const teacherQuizzes = data().getTeacherQuizzes(teacher.id);
      const teacherQuizIds = teacherQuizzes.map(q => q.id);
      const filtered = allReports.filter(r => teacherQuizIds.includes(r.quizId) && (currentFilter === 'all' || r.status === currentFilter));
      const pending = allReports.filter(r => teacherQuizIds.includes(r.quizId) && r.status === 'pending').length;
      if(pendingBadge) pendingBadge.textContent = `${pending} pending`;
      if(!filtered.length){
        list.innerHTML = '<p class="text-muted">No quiz reports found.</p>';
        return;
      }
      const students = data().students.reduce((acc, s) => { acc[s.id] = s.fullName || s.username; return acc; }, {});
      const quizzes = teacherQuizzes.reduce((acc, q) => { acc[q.id] = q.title; return acc; }, {});
      const statusBadge = (status) => {
        const map = { pending: 'warning text-dark', reviewed: 'info', resolved: 'success', dismissed: 'secondary' };
        return `<span class="badge bg-${map[status] || 'secondary'} text-capitalize">${status}</span>`;
      };
      const typeLabel = (type) => type.replace('_', ' ');
      list.innerHTML = filtered.map(r => `
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h3 class="h6 mb-2">${quizzes[r.quizId] || 'Unknown Quiz'} - Question ${(r.questionId || '').substring(0,6)}</h3>
                <p class="mb-1 small"><strong>Type:</strong> ${typeLabel(r.reportType)}</p>
                <p class="mb-1 small"><strong>Student:</strong> ${students[r.reportedBy] || 'Unknown'}</p>
                <p class="mb-1 small"><strong>Description:</strong> ${r.description || 'No details'}</p>
                <p class="mb-1 small text-muted">Reported: ${new Date(r.createdAt).toLocaleString()}</p>
                <div class="mt-2">${statusBadge(r.status)}</div>
              </div>
              <div class="btn-group-vertical ms-3">
                ${r.status === 'pending' ? `<button class="btn btn-sm btn-info" onclick="updateReportStatus('${r.id}', 'reviewed')">Mark Reviewed</button>` : ''}
                ${r.status !== 'resolved' ? `<button class="btn btn-sm btn-success" onclick="updateReportStatus('${r.id}', 'resolved')">Resolve</button>` : ''}
                ${r.status !== 'dismissed' ? `<button class="btn btn-sm btn-secondary" onclick="updateReportStatus('${r.id}', 'dismissed')">Dismiss</button>` : ''}
              </div>
            </div>
          </div>
        </div>
      `).join('');
    };
    window.updateReportStatus = (reportId, newStatus) => {
      if(!data().quizReports) return;
      const report = data().quizReports.find(r => r.id === reportId);
      if(!report) return;
      report.status = newStatus;
      report.reviewedBy = teacher.id;
      report.reviewedAt = new Date().toISOString();
      data().saveQuizReport(report);
      render();
    };
    document.querySelectorAll('[name="statusFilter"]').forEach(radio => {
      radio.addEventListener('change', (e) => {
        currentFilter = e.target.value;
        render();
      });
    });
    render();
  };

  const handlers = {
    'teacher-index': handleIndex,
    'teacher-login': handleLogin,
    'teacher-register': handleRegister,
    'teacher-dashboard': handleDashboard,
    'teacher-courses': handleCourses,
    'teacher-students': handleStudents,
    'teacher-reports': handleReports,
    'teacher-quiz-builder': handleQuizBuilder,
    'teacher-events': handleEvents,
    'teacher-quiz-reports': handleQuizReports
  };

  document.addEventListener('DOMContentLoaded', ()=>{
    const page = body()?.dataset?.page;
    if(page && handlers[page]){
      handlers[page]();
    }
  });
})();
