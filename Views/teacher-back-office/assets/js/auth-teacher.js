(function(){
  const API_URL = '/edumind/Controllers/AuthController.php';

  const TAuth = {
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
            role: 'teacher'
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
    
    async register(formData){
      if(!formData){
        alert('Please fill in all required fields - formData is null');
        return;
      }
      
      if(!formData.login || formData.login.trim() === ''){
        alert('Please fill in all required fields - login is missing');
        return;
      }
      
      if(!formData.password || formData.password.trim() === ''){
        alert('Please fill in all required fields - password is missing');
        return;
      }
      
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'register_teacher',
            data: {
              username: formData.login.trim(),
              password: formData.password,
              fullName: formData.fullName || formData.login,
              email: formData.email || `${formData.login}@teacher.local`,
              mobile: formData.mobile || '',
              address: formData.address || '',
              specialty: formData.subject || 'Unassigned',
              nationalId: formData.nationalId || ''
            }
          })
        });
        
        const result = await response.json();
        
        if(result.success){
          alert('Account created successfully!');
          window.location.href = 'login.php';
        } else {
          alert('Registration failed: ' + (result.error || 'Unknown error'));
        }
      } catch(error) {
        console.error('Registration error:', error);
        alert('Registration failed. Please try again. Error: ' + error.message);
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

  window.TAuth = TAuth;
})();
