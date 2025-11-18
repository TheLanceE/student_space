(function(){
  const CURRENT_KEY = 'ADMIN_currentUser';
  const allowedUsername = 'admin';
  const nowISO = () => new Date().toISOString();

  function getAdminRecord(){
    if(!window.Database){ return null; }
    const admins = Database.table('admins');
    return admins.find(a => a.username.toLowerCase() === allowedUsername);
  }

  const AAuth = {
    login(username){
      if(!username){ return; }
      if(username.trim().toLowerCase() !== allowedUsername){
        alert('Only the Admin account can access this area.');
        return;
      }
      const admin = getAdminRecord();
      if(!admin){
        alert('Admin record missing from database.');
        return;
      }
      const lastLogin = nowISO();
      Database.updateWhere('admins', entry => entry.id === admin.id, entry => ({ ...entry, lastLoginAt: lastLogin }));
      Storage.set(CURRENT_KEY, { ...admin, lastLoginAt: lastLogin, role: 'admin' });
      window.location.href = 'dashboard.php';
    },
    logout(){ localStorage.removeItem(CURRENT_KEY); window.location.href = 'login.php'; },
    current(){ return Storage.get(CURRENT_KEY, null); }
  };

  window.AAuth = AAuth;
})();