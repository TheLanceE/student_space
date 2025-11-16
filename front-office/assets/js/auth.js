(function(){
  const CURRENT_KEY = 'currentUser';
  const nowISO = () => new Date().toISOString();

  function normalizeAccount(record, role){
    return {
      id: record.id,
      username: record.username,
      role,
      fullName: record.fullName || record.username,
      email: record.email || '',
      gradeLevel: record.gradeLevel || null,
      specialty: record.specialty || null,
      createdAt: record.createdAt,
      lastLoginAt: record.lastLoginAt
    };
  }

  function findAccount(username){
    if(!window.Database) return null;
    const query = username.trim().toLowerCase();
    const student = Database.table('students').find(s => s.username.toLowerCase() === query);
    if(student){ return { record: student, role: 'student' }; }
    const teacher = Database.table('teachers').find(t => t.username.toLowerCase() === query);
    if(teacher){ return { record: teacher, role: 'teacher' }; }
    return null;
  }

  function updateLastLogin(table, id){
    Database.updateWhere(table, entry => entry.id === id, (entry) => {
      entry.lastLoginAt = nowISO();
      return entry;
    });
  }

  const Auth = {
    register(username){
      if(!username){ return; }
      if(findAccount(username)){
        alert('Username already exists. Please pick another or contact your administrator.');
        return;
      }
      const record = {
        id: Database.nextId('stu'),
        username: username.trim(),
        fullName: username.trim(),
        email: `${username.trim()}@student.local`,
        gradeLevel: 'Unassigned',
        createdAt: nowISO(),
        lastLoginAt: nowISO()
      };
      Database.insert('students', record);
      Storage.set(CURRENT_KEY, normalizeAccount(record, 'student'));
      window.location.href = 'dashboard.html';
    },
    login(username){
      if(!username){ return; }
      const account = findAccount(username);
      if(!account){
        alert('Account not found. Please reach out to the admin to create one.');
        return;
      }
      const updatedLogin = nowISO();
      updateLastLogin(account.role === 'student' ? 'students' : 'teachers', account.record.id);
      Storage.set(CURRENT_KEY, normalizeAccount({ ...account.record, lastLoginAt: updatedLogin }, account.role));
      window.location.href = 'dashboard.html';
    },
    logout(){
      localStorage.removeItem(CURRENT_KEY);
      window.location.href = 'login.html';
    },
    current(){ return Storage.get(CURRENT_KEY, null); }
  };

  window.Auth = Auth;
})();