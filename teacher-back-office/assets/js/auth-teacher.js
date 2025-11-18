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
      window.location.href = 'dashboard.php';
    },
    register(formData){
      if(!formData || !formData.login){ return; }
      const login = formData.login.trim();
      const existing = Database.table('teachers').find(t => t.username.toLowerCase() === login.toLowerCase());
      if(existing){
        alert('Login ID already exists. Please pick another or contact your administrator.');
        return;
      }
      const record = {
        id: Database.nextId('tea'),
        username: login,
        fullName: formData.fullName || login,
        email: formData.email || `${login}@teacher.local`,
        mobile: formData.mobile || '',
        address: formData.address || '',
        specialty: formData.subject || 'Unassigned',
        nationalId: formData.nationalId || '',
        createdAt: nowISO(),
        lastLoginAt: nowISO()
      };
      Database.insert('teachers', record);
      Storage.set(CURRENT_KEY, { ...record, role: 'teacher' });
      window.location.href = 'dashboard.php';
    },
    logout(){ localStorage.removeItem(CURRENT_KEY); window.location.href = '../index.php'; },
    current(){ return Storage.get(CURRENT_KEY, null); }
  };

  window.TAuth = TAuth;
})();