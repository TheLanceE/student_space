(function(){
  const API_URL = '/edumind/Controllers/AuthController.php';
  const nowISO = () => new Date().toISOString();

  // HTML escape helper for XSS prevention
  const escapeHtml = (str) => {
    if (str == null) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  };

  // Get CSRF token from page meta tag or hidden input
  const getCSRFToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    const input = document.querySelector('input[name="csrf_token"]');
    if (input) return input.value;
    return '';
  };

  const Auth = {
    async register(formData){
      // Validate required fields
      if (!formData.login || !formData.password) {
        alert('Username and password are required');
        return;
      }
      
      if (formData.password.length < 6) {
        alert('Password must be at least 6 characters');
        return;
      }
      
      try {
        const payload = {
          action: 'register_student',
          csrf_token: getCSRFToken(),
          data: {
            username: formData.login.trim(),
            password: formData.password,
            fullName: formData.fullName || formData.login,
            email: formData.email || '',
            mobile: formData.mobile || '',
            address: formData.address || '',
            gradeLevel: formData.gradeLevel || 'Unassigned'
          }
        };
        
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
          },
          body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        
        if(result.success){
          alert('Account created successfully!');
          window.location.href = 'login.php';
        } else {
          alert('Registration failed: ' + escapeHtml(result.error || 'Unknown error'));
        }
      } catch(error) {
        alert('Registration failed. Please try again.');
      }
    },
    
    async login(username, password){
      if(!username || !password){ 
        alert('Please enter username and password');
        return; 
      }
      
      try {
        const payload = {
          action: 'login',
          username: username.trim(),
          password: password,
          role: 'student',
          csrf_token: getCSRFToken()
        };
        
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
          },
          body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        
        if(result.success){
          window.location.href = 'dashboard.php';
        } else {
          alert('Login failed: ' + escapeHtml(result.error || 'Invalid credentials'));
        }
      } catch(error) {
        alert('Login failed. Please try again.');
      }
    },
    
    async logout(){
      try {
        await fetch(API_URL, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
          },
          body: JSON.stringify({ action: 'logout' })
        });
      } catch(error) {
        // Silent logout failure
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
        return false;
      }
    }
  };

  window.escapeHtml = escapeHtml;
  window.Auth = Auth;
})();