(function(){
  console.log('ADMIN AUTH.JS LOADED');
  const API_URL = '/edumind/Controllers/AuthController.php';

  const AAuth = {
    async login(username, password){
      if(!username || !password){ 
        alert('Please enter username and password');
        return; 
      }
      
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'login',
            username: username.trim(),
            password: password,
            role: 'admin'
          })
        });
        
        const result = await response.json();
        
        if(result.success){
          localStorage.setItem('ADMIN_currentUser', JSON.stringify(result.user));
          window.location.href = 'dashboard.php';
        } else {
          alert('Login failed: ' + (result.error || 'Invalid credentials'));
        }
      } catch(error) {
        console.error('Login error:', error);
        alert('Login failed. Please try again.');
      }
    },
    
    logout(){
      localStorage.removeItem('ADMIN_currentUser');
      fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
      }).finally(() => {
        window.location.href = '../../index.php';
      });
    },
    
    current(){ 
      const stored = localStorage.getItem('ADMIN_currentUser');
      return stored ? JSON.parse(stored) : null;
    }
  };

  window.AAuth = AAuth;
})();
