(function(){
  const TUI = {
    requireAuth(){ if(!Storage.get('TEACHER_currentUser')){ window.location.replace('login.html'); } },
    bindLogout(id){ const btn = document.getElementById(id); if(btn){ btn.addEventListener('click', ()=> TAuth.logout()); } },
    renderCourses(containerId, courses, quizzes = [], onDelete){
      const el = document.getElementById(containerId); if(!el) return;
      el.innerHTML = '';
      if(!courses.length){
        el.innerHTML = '<p class="placeholder-text">No courses yet. Create one to get started.</p>';
        return;
      }
      courses.forEach(c=>{
        const div = document.createElement('div');
        div.className = 'col-12 col-md-6 col-lg-4';
        const courseQuizzes = quizzes.filter(q => q.courseId === c.id);
        div.innerHTML = `
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h2 class="h5">${c.title}</h2>
              <p class="text-muted">${c.description||''}</p>
              <div class="mt-auto d-flex gap-2">
                <a href="quiz-builder.html?courseId=${c.id}" class="btn btn-primary btn-sm">Build Quiz (${courseQuizzes.length})</a>
                <button class="btn btn-outline-danger btn-sm" data-id="${c.id}">Delete</button>
              </div>
            </div>
          </div>`;
        el.appendChild(div);
      });
      // bind deletes
      el.querySelectorAll('button[data-id]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const id = btn.getAttribute('data-id');
          if(typeof onDelete === 'function'){
            onDelete(id);
            return;
          }
          if(window.TData && typeof TData.removeCourse === 'function'){
            TData.removeCourse(id);
          }
          location.reload();
        });
      });
    },
    renderStudentsTable(containerId, students, scores){
      const el = document.getElementById(containerId); if(!el) return;
      if(!students.length){ el.innerHTML = '<p class="placeholder-text">No students.</p>'; return; }
      const avgPct = (uid)=>{
        const ss = scores.filter(s=>s.userId===uid);
        if(!ss.length) return '-';
        const p = Math.round(ss.map(x=> (x.score/x.total)*100).reduce((a,b)=>a+b,0)/ss.length);
        return p+'%';
      };
      const rows = students.map(st=> `<tr><td>${st.username}</td><td>${avgPct(st.id)}</td><td>${scores.filter(s=>s.userId===st.id).length}</td></tr>`).join('');
      el.innerHTML = `<table class="table table-striped"><thead><tr><th>Student</th><th>Avg %</th><th>Attempts</th></tr></thead><tbody>${rows}</tbody></table>`;
    }
  };
  window.TUI = TUI;
})();