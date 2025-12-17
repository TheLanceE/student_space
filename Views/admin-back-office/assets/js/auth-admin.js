(function(){
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
          // Session managed server-side, just redirect
          window.location.href = 'dashboard.php';
        } else {
          alert('Login failed: ' + (result.error || 'Invalid credentials'));
        }
      } catch(error) {
        console.error('Login error:', error);
        alert('Login failed. Please try again.');
      }
    },
    
    async logout(){
      try {
        await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'logout' })
        });
      } catch(error) {
        console.error('Logout error:', error);
      } finally {
        window.location.href = 'login.php';
      }
    },
    
    async current(){ 
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'current_user' })
        });
        const result = await response.json();
        return result.success ? result.user : null;
      } catch(error) {
        console.error('Get current user error:', error);
        return null;
      }
    },
    
    async checkAuth(){
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'check_auth' })
        });
        const result = await response.json();
        return result.authenticated;
      } catch(error) {
        console.error('Check auth error:', error);
        return false;
      }
    }
  };

  window.AAuth = AAuth;
})();
