/**
 * Modern Admin Dashboard JavaScript
 * Handles bulk operations, API calls, and UI interactions
 */

class AdminAPI {
    constructor() {
        this.baseUrl = '/edumind/Controllers/AdminApiController.php';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }
    
    async request(endpoint, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-Token': this.csrfToken,
            ...options.headers
        };
        
        const response = await fetch(this.baseUrl + endpoint, {
            ...options,
            headers
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Request failed');
        }
        
        return data;
    }
    
    // Users
    async getUsers() {
        return this.request('/users');
    }
    
    async bulkDeleteUsers(ids, role) {
        return this.request('/users/bulk-delete', {
            method: 'POST',
            body: JSON.stringify({ ids, role })
        });
    }
    
    async detectFakeAccounts() {
        return this.request('/users/detect-fake');
    }
    
    // Courses
    async getCourses() {
        return this.request('/courses');
    }
    
    async bulkDeleteCourses(ids) {
        return this.request('/courses/bulk-delete', {
            method: 'POST',
            body: JSON.stringify({ ids })
        });
    }
    
    // Events
    async getEvents() {
        return this.request('/events');
    }
    
    async bulkDeleteEvents(ids) {
        return this.request('/events/bulk-delete', {
            method: 'POST',
            body: JSON.stringify({ ids })
        });
    }
    
    // Quizzes
    async getQuizzes() {
        return this.request('/quizzes');
    }
    
    async bulkDeleteQuizzes(ids) {
        return this.request('/quizzes/bulk-delete', {
            method: 'POST',
            body: JSON.stringify({ ids })
        });
    }
}

class BulkOperationsUI {
    constructor(api) {
        this.api = api;
        this.selectedItems = new Set();
    }
    
    renderTable(containerId, data, columns, idField = 'id') {
        const container = document.getElementById(containerId);
        if (!container || !data || data.length === 0) {
            container.innerHTML = '<p class="text-muted">No data available</p>';
            return;
        }
        
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="bulkUI.selectAll('${containerId}')">
                        <i class="bi bi-check-square"></i> Select All
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="bulkUI.deselectAll()">
                        <i class="bi bi-x-square"></i> Deselect All
                    </button>
                </div>
                <div>
                    <span class="badge bg-info" id="selectedCount">0 selected</span>
                    <button class="btn btn-sm btn-danger" id="bulkDeleteBtn" onclick="bulkUI.confirmBulkDelete()" disabled>
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllCheckbox" onchange="bulkUI.toggleAll(this)"></th>
        `;
        
        columns.forEach(col => {
            html += `<th>${col.label}</th>`;
        });
        
        html += `</tr></thead><tbody>`;
        
        data.forEach(item => {
            html += `<tr>
                <td><input type="checkbox" class="item-checkbox" data-id="${item[idField]}" onchange="bulkUI.toggleItem(this)"></td>
            `;
            
            columns.forEach(col => {
                let value = item[col.field] || '-';
                if (col.format) {
                    value = col.format(value, item);
                }
                html += `<td>${value}</td>`;
            });
            
            html += `</tr>`;
        });
        
        html += `</tbody></table></div>`;
        container.innerHTML = html;
    }
    
    toggleItem(checkbox) {
        const id = checkbox.dataset.id;
        if (checkbox.checked) {
            this.selectedItems.add(id);
        } else {
            this.selectedItems.delete(id);
        }
        this.updateSelectedCount();
    }
    
    toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
            this.toggleItem(cb);
        });
    }
    
    selectAll(containerId) {
        const checkboxes = document.querySelectorAll(`#${containerId} .item-checkbox`);
        checkboxes.forEach(cb => {
            cb.checked = true;
            this.selectedItems.add(cb.dataset.id);
        });
        document.getElementById('selectAllCheckbox').checked = true;
        this.updateSelectedCount();
    }
    
    deselectAll() {
        this.selectedItems.clear();
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        this.updateSelectedCount();
    }
    
    updateSelectedCount() {
        const count = this.selectedItems.size;
        document.getElementById('selectedCount').textContent = `${count} selected`;
        document.getElementById('bulkDeleteBtn').disabled = count === 0;
    }
    
    async confirmBulkDelete() {
        if (this.selectedItems.size === 0) return;
        
        const confirmed = confirm(`Are you sure you want to delete ${this.selectedItems.size} items? This action cannot be undone.`);
        
        if (!confirmed) return;
        
        try {
            // Show loading
            const btn = document.getElementById('bulkDeleteBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Deleting...';
            
            // Determine resource type from page
            const page = document.body.dataset.page;
            let result;
            
            if (page === 'admin-users') {
                const role = document.querySelector('input[name="role"]:checked')?.value || 'student';
                result = await this.api.bulkDeleteUsers([...this.selectedItems], role);
            } else if (page === 'admin-courses') {
                result = await this.api.bulkDeleteCourses([...this.selectedItems]);
            } else if (page === 'admin-events') {
                result = await this.api.bulkDeleteEvents([...this.selectedItems]);
            } else if (page === 'admin-quizzes') {
                result = await this.api.bulkDeleteQuizzes([...this.selectedItems]);
            }
            
            // Show success message
            alert(`Successfully deleted ${result.deleted} items`);
            
            // Reload page
            window.location.reload();
            
        } catch (error) {
            alert('Error: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i> Delete Selected';
        }
    }
}

// Initialize
const adminAPI = new AdminAPI();
const bulkUI = new BulkOperationsUI(adminAPI);

// Auto-detect fake accounts feature
async function detectFakeAccounts() {
    try {
        const result = await adminAPI.detectFakeAccounts();
        
        if (result.count === 0) {
            alert('No fake accounts detected!');
            return;
        }
        
        const confirmed = confirm(`Found ${result.count} potential fake accounts. View them?`);
        if (confirmed) {
            // Render fake accounts in modal or table
            const fakeData = result.fake_accounts;
            bulkUI.renderTable('fakeAccountsTable', fakeData, [
                { label: 'Username', field: 'username' },
                { label: 'Email', field: 'email' },
                { label: 'Created', field: 'createdAt', format: (v) => new Date(v).toLocaleDateString() },
                { label: 'Last Login', field: 'lastLoginAt', format: (v) => v ? new Date(v).toLocaleDateString() : 'Never' }
            ]);
        }
    } catch (error) {
        alert('Error detecting fake accounts: ' + error.message);
    }
}

// Google Sign-In button handler
function handleGoogleSignIn() {
    const role = document.body.dataset.role || 'student';
    // Store role in session for callback
    fetch('/edumind/Controllers/set_oauth_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role })
    }).then(() => {
        window.location.href = '/edumind/Controllers/google_oauth_start.php';
    });
}
