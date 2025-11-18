(function(){
  const body = () => document.body || null;
  const auth = () => window.AAuth;
  const data = () => window.AData;
  const storage = () => window.Storage;

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
    const current = auth()?.current();
    window.location.replace(current ? 'dashboard.php' : 'login.php');
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
    const studentsEl = document.getElementById('sCount');
    const teachersEl = document.getElementById('tCount');
    const coursesEl = document.getElementById('cCount');
    const pendingEl = document.getElementById('pCount');
    if(!studentsEl || !teachersEl || !coursesEl || !pendingEl) return;
    const studentCount = data().students.length;
    const teacherCount = data().teachers.length;
    const courses = data().courses;
    const pending = courses.filter(c=> (c.status || 'pending') === 'pending').length;
    studentsEl.textContent = studentCount;
    teachersEl.textContent = teacherCount;
    coursesEl.textContent = courses.length;
    pendingEl.textContent = pending;
  };

  const handleCourses = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const container = document.getElementById('courseTable');
    if(!container) return;
    const render = () => {
      const courses = data().courses;
      const teachersById = data().teachers.reduce((acc, teacher)=>{
        acc[teacher.id] = teacher.fullName || teacher.username;
        return acc;
      }, {});
      const rows = courses.map(c=> {
        const status = c.status || 'pending';
        const teacherName = teachersById[c.teacherId] || 'Unassigned';
        return `
        <tr>
          <td>
            <div class='fw-semibold'>${c.title}</div>
            <div class='small text-muted'>${teacherName}</div>
          </td>
          <td><span class='badge ${status==='approved' ? 'bg-success' : 'bg-warning text-dark'} text-capitalize'>${status}</span></td>
          <td class='text-end'>
            ${status!=='approved' ? `<button class='btn btn-sm btn-primary me-2' data-approve='${c.id}'>Approve</button>` : ''}
            <button class='btn btn-sm btn-outline-danger' data-remove='${c.id}'>Remove</button>
          </td>
        </tr>`;
      }).join('') || '<tr><td colspan="3" class="text-center text-muted">No courses found.</td></tr>';
      container.innerHTML = `<table class='table table-striped'><thead><tr><th>Course</th><th>Status</th><th></th></tr></thead><tbody>${rows}</tbody></table>`;
      container.querySelectorAll('[data-approve]').forEach(btn=> btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-approve');
        data().updateCourse(id, course => ({ ...course, status: 'approved', approvedAt: new Date().toISOString() }));
        render();
      }));
      container.querySelectorAll('[data-remove]').forEach(btn=> btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-remove');
        data().removeCourse(id);
        render();
      }));
    };
    render();
  };

  const handleUsers = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const table = document.getElementById('userTable');
    if(!table) return;
    const form = document.getElementById('addUserForm');
    const usernameInput = document.getElementById('uName');
    const roleInput = document.getElementById('uRole');
    const nowISO = () => new Date().toISOString();
    const usernameExists = (username) => {
      const lower = username.toLowerCase();
      return data().users.some(u => u.username.toLowerCase() === lower);
    };
    const deleteUser = (id, role) => {
      if(role === 'admin'){
        if(data().admins.length <= 1){
          alert('At least one admin account is required.');
          return false;
        }
        data().removeAdmin(id);
        return true;
      }
      if(role === 'student'){ data().removeStudent(id); return true; }
      if(role === 'teacher'){ data().removeTeacher(id); return true; }
      return false;
    };
    const render = () => {
      const adminCount = data().admins.length;
      const rows = data().users.map(u=>{
        const isProtectedAdmin = u.role === 'admin' && adminCount <= 1;
        const disableAttr = isProtectedAdmin ? 'disabled' : '';
        return `<tr>
          <td>
            <div class='fw-semibold text-capitalize'>${u.username}</div>
            <div class='small text-muted'>${u.fullName || ''}</div>
          </td>
          <td class='text-capitalize'>${u.role}</td>
          <td class='text-end'>
            <button class='btn btn-sm btn-outline-danger' data-id='${u.id}' data-role='${u.role}' ${disableAttr}>Delete</button>
          </td>
        </tr>`;
      }).join('') || '<tr><td colspan="3" class="text-center text-muted">No users yet.</td></tr>';
      table.innerHTML = `<table class='table table-striped'><thead><tr><th>User</th><th>Role</th><th></th></tr></thead><tbody>${rows}</tbody></table>`;
      table.querySelectorAll('[data-id]').forEach(btn=> btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-id');
        const role = btn.getAttribute('data-role');
        if(deleteUser(id, role)){
          render();
        }
      }));
    };
    form?.addEventListener('submit', (e)=>{
      e.preventDefault();
      const username = usernameInput?.value.trim();
      const role = roleInput?.value;
      if(!username) return;
      if(usernameExists(username)){
        alert('Username already exists.');
        return;
      }
      const baseRecord = {
        id: data().nextId(role?.slice(0,3) || 'usr'),
        username,
        fullName: username,
        email: `${username}@edumind.local`,
        createdAt: nowISO(),
        lastLoginAt: null
      };
      if(role === 'student'){
        data().saveStudent({ ...baseRecord, gradeLevel: 'Grade 8' });
      } else if(role === 'teacher'){
        const emailInput = document.getElementById('uEmail');
        const mobileInput = document.getElementById('uMobile');
        const addressInput = document.getElementById('uAddress');
        const subjectInput = document.getElementById('uSubject');
        const nationalIdInput = document.getElementById('uNationalId');
        data().saveTeacher({
          ...baseRecord,
          email: emailInput?.value.trim() || baseRecord.email,
          mobile: mobileInput?.value.trim() || '',
          address: addressInput?.value.trim() || '',
          specialty: subjectInput?.value.trim() || 'General Studies',
          nationalId: nationalIdInput?.value.trim() || ''
        });
      } else if(role === 'admin'){
        data().saveAdmin({ ...baseRecord, name: baseRecord.fullName });
      }
      form.reset();
      document.getElementById('teacherFields').style.display = 'none';
      render();
    });
    render();
  };

  const handleLogs = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const table = document.getElementById('logTable');
    if(!table) return;
    const rows = data().logs.map(l=> `<tr><td>${new Date(l.ts).toLocaleString()}</td><td><span class='badge bg-secondary text-uppercase'>${l.level}</span></td><td>${l.message}</td></tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">No log entries.</td></tr>';
    table.innerHTML = `<table class='table table-striped'><thead><tr><th>Time</th><th>Level</th><th>Message</th></tr></thead><tbody>${rows}</tbody></table>`;
  };

  const handleRoles = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const userSelect = document.getElementById('userId');
    const roleSelect = document.getElementById('role');
    const assignBtn = document.getElementById('assignBtn');
    if(!userSelect || !roleSelect || !assignBtn) return;
    const renderUsers = () => {
      userSelect.innerHTML = '';
      data().users.forEach(u=>{
        const opt = document.createElement('option');
        opt.value = u.id;
        opt.textContent = `${u.username} (${u.role})`;
        userSelect.appendChild(opt);
      });
    };
    renderUsers();
    assignBtn.addEventListener('click', ()=>{
      const id = userSelect.value;
      const role = roleSelect.value;
      if(!id || !role) return;
      const userMeta = data().users.find(u => u.id === id);
      if(!userMeta){
        alert('User not found.');
        return;
      }
      if(userMeta.role === role){
        alert('User already has that role.');
        return;
      }
      if(userMeta.role === 'admin' && data().admins.length <= 1){
        alert('Cannot change the role of the last admin account.');
        return;
      }
      const sourceRecord = (()=>{
        if(userMeta.role === 'student') return data().students.find(s => s.id === id);
        if(userMeta.role === 'teacher') return data().teachers.find(t => t.id === id);
        if(userMeta.role === 'admin') return data().admins.find(a => a.id === id);
        return null;
      })();
      if(!sourceRecord){
        alert('Record data missing.');
        return;
      }
      const now = new Date().toISOString();
      const base = {
        id: sourceRecord.id,
        username: sourceRecord.username,
        fullName: sourceRecord.fullName || sourceRecord.name || sourceRecord.username,
        email: sourceRecord.email || '',
        createdAt: sourceRecord.createdAt || now,
        lastLoginAt: sourceRecord.lastLoginAt || null
      };
      if(userMeta.role === 'student') data().removeStudent(id);
      if(userMeta.role === 'teacher') data().removeTeacher(id);
      if(userMeta.role === 'admin') data().removeAdmin(id);
      if(role === 'student'){
        data().saveStudent({ ...base, gradeLevel: sourceRecord.gradeLevel || 'Grade 8' });
      } else if(role === 'teacher'){
        data().saveTeacher({ ...base, specialty: sourceRecord.specialty || 'General Studies' });
      } else if(role === 'admin'){
        data().saveAdmin({ ...base, name: base.fullName });
      }
      renderUsers();
      alert('Role updated.');
    });
  };

  const handleReports = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!data()) return;
    const toCSV = (rows) => rows.map(r=> r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    const download = (name, csv) => {
      const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = name;
      a.click();
      URL.revokeObjectURL(url);
    };
    const exportUsersBtn = document.getElementById('exportUsers');
    const exportCoursesBtn = document.getElementById('exportCourses');
    if(!exportUsersBtn || !exportCoursesBtn) return;
    exportUsersBtn.addEventListener('click', ()=>{
      const rows = [['id','username','role','fullName','email'], ...data().users.map(u=> [u.id,u.username,u.role,u.fullName || '',u.email || ''])];
      download('edumind_users.csv', toCSV(rows));
    });
    exportCoursesBtn.addEventListener('click', ()=>{
      const teacherLookup = data().teachers.reduce((acc, teacher)=>{
        acc[teacher.id] = teacher.fullName || teacher.username;
        return acc;
      }, {});
      const rows = [['id','title','teacher','status'], ...data().courses.map(c=> [c.id,c.title,teacherLookup[c.teacherId] || '',c.status || 'pending'])];
      download('edumind_courses.csv', toCSV(rows));
    });
  };

  const handleSettings = () => {
    if(!ensureAuth()) return;
    bindLogout();
    if(!storage()) return;
    const KEY = 'ADMIN_settings';
    const defaults = { inactivityDays: 7, exportPrefix: 'edumind' };
    const form = document.getElementById('settingsForm');
    const inact = document.getElementById('inactDays');
    const prefix = document.getElementById('exportPrefix');
    if(!form || !inact || !prefix) return;
    const saved = storage().get(KEY, defaults);
    inact.value = saved.inactivityDays;
    prefix.value = saved.exportPrefix;
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      const inactivityDays = parseInt(inact.value, 10) || defaults.inactivityDays;
      const exportPrefix = prefix.value.trim() || defaults.exportPrefix;
      storage().set(KEY, { inactivityDays, exportPrefix });
      alert('Settings saved.');
    });
  };

  const handlers = {
    'admin-index': handleIndex,
    'admin-login': handleLogin,
    'admin-dashboard': handleDashboard,
    'admin-courses': handleCourses,
    'admin-users': handleUsers,
    'admin-logs': handleLogs,
    'admin-roles': handleRoles,
    'admin-reports': handleReports,
    'admin-settings': handleSettings
  };

  document.addEventListener('DOMContentLoaded', ()=>{
    const page = body()?.dataset?.page;
    if(page && handlers[page]){
      handlers[page]();
    }
  });
})();
