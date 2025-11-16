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

  const ensureAuth = () => {
    if(!auth()) return false;
    if(!auth().current()){
      window.location.replace('login.html');
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
    window.location.replace(user ? 'dashboard.html' : 'login.html');
  };

  const handleLogin = () => {
    if(!auth()) return;
    const form = document.getElementById('loginForm');
    if(!form) return;
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
      }
      const username = document.getElementById('username').value.trim();
      auth().login(username);
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
      window.location.href = 'courses.html';
    });
  };

  const handlers = {
    'teacher-index': handleIndex,
    'teacher-login': handleLogin,
    'teacher-dashboard': handleDashboard,
    'teacher-courses': handleCourses,
    'teacher-students': handleStudents,
    'teacher-reports': handleReports,
    'teacher-quiz-builder': handleQuizBuilder
  };

  document.addEventListener('DOMContentLoaded', ()=>{
    const page = body()?.dataset?.page;
    if(page && handlers[page]){
      handlers[page]();
    }
  });
})();
