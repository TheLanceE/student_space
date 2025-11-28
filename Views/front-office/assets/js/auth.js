(function(){
  console.log('AUTH.JS LOADED');
  const API_URL = '/edumind/Controllers/AuthController.php';
  const nowISO = () => new Date().toISOString();

  const Auth = {
    async register(formData){
      console.log('AUTH.JS: Register called');
      console.log('AUTH.JS: formData =', formData);
      console.log('AUTH.JS: password =', formData.password);
      console.log('AUTH.JS: password length =', formData.password ? formData.password.length : 'UNDEFINED');
      
      // SKIP ALL VALIDATION - JUST SEND TO SERVER
      try {
        const payload = {
          action: 'register_student',
          data: {
            username: formData.login.trim(),
            password: formData.password || 'DEFAULT_PASSWORD',
            fullName: formData.fullName || formData.login,
            email: formData.email || `${formData.login}@student.local`,
            mobile: formData.mobile || '',
            address: formData.address || '',
            gradeLevel: formData.gradeLevel || 'Unassigned'
          }
        };
        
        console.log('AUTH.JS: Sending to server:', payload);
        
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        console.log('Registration result:', result);
        
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
    
    async login(username, password){
      console.log('AUTH.JS LOGIN: username =', username, 'password =', password);
      
      if(!username || !password){ 
        alert('Please enter username and password');
        return; 
      }
      
      try {
        const payload = {
          action: 'login',
          username: username.trim(),
          password: password,
          role: 'student'
        };
        
        console.log('AUTH.JS LOGIN: Sending to server:', payload);
        
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        
        console.log('AUTH.JS LOGIN: Response status:', response.status);
        const result = await response.json();
        console.log('AUTH.JS LOGIN: Server response:', result);
        
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

  window.Auth = Auth;
})();