document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('api', 'true');

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response Status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.message) {
            console.log('Login success:', data);
            console.log('Role received:', data.user.role);
            localStorage.setItem('token', data.token);
            localStorage.setItem('role', data.user.role);
            localStorage.setItem('username', data.user.username);

            if (data.user.role === 'admin') {
                console.log('Role is admin, redirecting to index.php');
                window.location.href = 'index.php';
            } else {
                console.log('Role is user, redirecting to dashboard.php');
                window.location.href = 'dashboard.php';
            }
        } else {
            console.error('Login failed:', data.error);
            alert('Login failed: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('An error occurred during login: ' + error.message);
    });
});

function configureRolePermissions() {
    const role = localStorage.getItem('role');
    const submitBtn = document.getElementById('submitBtn');
    const editBtn = document.getElementById('editBtn');

    if (submitBtn && editBtn) {
        submitBtn.disabled = false;
        editBtn.disabled = role !== 'admin'; // เฉพาะ admin แก้ไขได้
    }
}

window.addEventListener('load', function() {
    if (window.location.pathname.includes('dashboard.php') || 
        window.location.pathname.includes('index.php') ||
        window.location.pathname.includes('addgroup.php') ||
        window.location.pathname.includes('addmessage.php') ||
        window.location.pathname.includes('addtoken.php') ||
        window.location.pathname.includes('botsettings.php') ||
        window.location.pathname.includes('group.php') ||
        window.location.pathname.includes('message.php') ||
        window.location.pathname.includes('register.php') ||
        window.location.pathname.includes('token.php')) {
        configureRolePermissions();
    }
});