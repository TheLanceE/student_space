(function(){
  console.log('TEACHER AUTH.JS LOADED');
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
          localStorage.setItem('TEACHER_currentUser', JSON.stringify(result.user));
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
      console.log('Teacher register called with formData:', formData);
      
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
        console.log('Teacher registration result:', result);
        
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
    
    logout(){
      localStorage.removeItem('TEACHER_currentUser');
      fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
      }).finally(() => {
        window.location.href = '../../index.php';
      });
    },
    
    current(){ 
      const stored = localStorage.getItem('TEACHER_currentUser');
      return stored ? JSON.parse(stored) : null;
    }
  };

  window.TAuth = TAuth;
})();
