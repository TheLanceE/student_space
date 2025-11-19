(function(){
  const UI = {
    requireAuth(){
      if(!Storage.get('currentUser')){
        window.location.replace('login.php');
      }
    },
    bindLogout(id){
      const btn = document.getElementById(id);
      if(btn){ btn.addEventListener('click', ()=> Auth.logout()); }
    },
    renderSuggestions(listId, suggestions){
      const el = document.getElementById(listId);
      if(!el) return;
      el.innerHTML = '';
      if(!suggestions || !suggestions.length){
        const li = document.createElement('li');
        li.className = 'list-group-item placeholder-text';
        li.textContent = 'No suggestions yet. Take a quiz to get started!';
        el.appendChild(li);
        return;
      }
      suggestions.forEach(s => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = s;
        el.appendChild(li);
      });
    },
    renderRecentResults(containerId, scores){
      const el = document.getElementById(containerId);
      if(!el) return;
      const user = Storage.get('currentUser');
      const myScores = (scores||[]).filter(s=>s.userId===user?.id).sort((a,b)=>new Date(b.timestamp)-new Date(a.timestamp)).slice(0,10);
      if(!myScores.length){
        el.innerHTML = '<p class="placeholder-text m-0">No results yet. Take your first quiz!</p>';
        return;
      }
      const html = [`<table class="table table-striped table-sm"><thead><tr>
        <th>Date</th><th>Course</th><th>Quiz</th><th>Score</th><th>Attempts</th><th>Duration</th>
      </tr></thead><tbody>`];
      for(const s of myScores){
        const pct = Math.round((s.score/s.total)*100);
        const course = Data.courses.find(c=>c.id===s.courseId)?.title || s.courseId;
        const quiz = Data.getQuizById(s.quizId)?.title || s.quizId;
        html.push(`<tr><td>${new Date(s.timestamp).toLocaleString()}</td><td>${course}</td><td>${quiz}</td><td>${pct}% (${s.score}/${s.total})</td><td>${s.attempt}</td><td>${s.durationSec}s</td></tr>`);
      }
      html.push('</tbody></table>');
      el.innerHTML = html.join('');
    },
    renderCourses(containerId, courses){
      const el = document.getElementById(containerId);
      if(!el) return;
      el.innerHTML = '';
      courses.forEach(c => {
        const quizzes = Data.getQuizzesForCourse(c.id);
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6 col-lg-4';
        col.innerHTML = `
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h2 class="h5">${c.title}</h2>
              <p class="text-muted flex-grow-1">${c.description}</p>
              <div class="d-grid gap-2">
                ${quizzes.map(q => `<a class="btn btn-primary" href="quiz.html?quizId=${q.id}">${q.title}</a>`).join('')}
              </div>
            </div>
          </div>
        `;
        el.appendChild(col);
      });
    }
  };

  window.UI = UI;
})();