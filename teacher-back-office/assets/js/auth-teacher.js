(function(){
  const CURRENT_KEY = 'TEACHER_currentUser';
  const nowISO = () => new Date().toISOString();

  function findTeacher(username){
    if(!window.Database) return null;
    const query = username.trim().toLowerCase();
    return Database.table('teachers').find(t => t.username.toLowerCase() === query);
  }

  const TAuth = {
    login(username){
      if(!username){ return; }
      const teacher = findTeacher(username);
      if(!teacher){
        alert('Teacher account not found. Contact the administrator.');
        return;
      }
      const lastLogin = nowISO();
      Database.updateWhere('teachers', entry => entry.id === teacher.id, entry => ({ ...entry, lastLoginAt: lastLogin }));
      Storage.set(CURRENT_KEY, { ...teacher, lastLoginAt: lastLogin, role: 'teacher' });
      window.location.href = 'dashboard.html';
    },
    logout(){ localStorage.removeItem(CURRENT_KEY); window.location.href = 'login.html'; },
    current(){ return Storage.get(CURRENT_KEY, null); }
  };

  window.TAuth = TAuth;
})();