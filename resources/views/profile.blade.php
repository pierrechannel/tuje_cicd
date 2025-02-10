@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Left Column - Profile Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center p-4">
                    <div class="position-relative mb-4 mx-auto" style="width: 150px;">
                        <div class="profile-image-container rounded-circle overflow-hidden" style="width: 150px; height: 150px;">
                            <img 
                                src="{{ asset('assets/img/avatar.png') }}" 
                                alt="Profile" 
                                id="profile-image"
                                class="w-100 h-100 object-fit-cover"
                            >
                        </div>
                        <label for="image-upload" class="position-absolute bottom-0 end-0 mb-2 me-2">
                            <div class="btn btn-primary btn-sm rounded-circle">
                                <i class="bi bi-camera"></i>
                            </div>
                            <input type="file" id="image-upload" class="d-none" accept="image/*">
                        </label>
                    </div>
                    <h4 class="mb-1 fw-bold" id="display-name"></h4>
                    <p class="text-muted mb-3" id="display-role"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-envelope me-2"></i>Message
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-2"></i>Settings
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Account Status</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Email Verified</h6>
                            <small class="text-muted" id="email-status">Verified on Sept 14, 2023</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Last Login</h6>
                            <small class="text-muted" id="last-login">2 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Main Content -->
        <div class="col-lg-8">
            <!-- Tab Navigation -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <ul class="nav nav-pills nav-fill p-2" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-4" data-bs-toggle="tab" data-bs-target="#profile-overview">
                                <i class="bi bi-person me-2"></i>Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#profile-edit">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#profile-change-password">
                                <i class="bi bi-lock me-2"></i>Password
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="profile-overview">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Profile Details</h5>
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                        <i class="bi bi-person text-primary me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Full Name</small>
                                            <span class="fw-medium" id="overview-name"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                        <i class="bi bi-at text-primary me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Username</small>
                                            <span class="fw-medium" id="overview-username"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                        <i class="bi bi-envelope text-primary me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Email Address</small>
                                            <span class="fw-medium" id="overview-email"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                        <i class="bi bi-briefcase text-primary me-3 fs-4"></i>
                                        <div>
                                            <small class="text-muted d-block">Role</small>
                                            <span class="fw-medium" id="overview-role"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-pane fade" id="profile-edit">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Edit Profile Information</h5>
                            <form id="profile-form" class="needs-validation" novalidate>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                            <div class="invalid-feedback">Please enter your full name.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                            <div class="invalid-feedback">Please choose a username.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                            <div class="invalid-feedback">Please enter a valid email.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="profile-change-password">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Change Password</h5>
                            <form id="password-form" class="needs-validation" novalidate>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="currentPassword" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="newPassword" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength mt-2" id="password-strength"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="confirmPassword" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-shield-lock me-2"></i>Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast for notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>
</div>


<style>
.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: #f8f9fa;
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: white;
}

.password-strength {
    height: 5px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin-top: 0.5rem;
}

.password-strength div {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.profile-image-container {
    border: 3px solid #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.profile-image-container:hover {
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
    setupPasswordStrengthMeter();
});

function initializeProfile() {
    const userId = sessionStorage.getItem('userId');
    if (!userId) {
        showToast('User not authenticated', 'error');
        return;
    }

    fetchUserData(userId);
    setupEventListeners();
}

function setupEventListeners() {
    // Image upload
    document.getElementById('image-upload').addEventListener('change', handleImageUpload);
    
    // Forms
    document.getElementById('profile-form').addEventListener('submit', handleProfileUpdate);
    document.getElementById('password-form').addEventListener('submit', handlePasswordUpdate);
    
   // Password toggles
   document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
}

async function fetchUserData(userId) {
    try {
        const response = await fetch(`/api/profile/${userId}`, {
            headers: {
                'Authorization': `Bearer ${sessionStorage.getItem('accessToken')}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to fetch user data');
        
        const userData = await response.json();
        updateUIWithUserData(userData);
    } catch (error) {
        showToast('Failed to load user data', 'error');
        console.error('Error:', error);
    }
}

function updateUIWithUserData(userData) {
    // Profile card
    document.getElementById('profile-image').src = userData.image 
        ? `/storage/${userData.image}` 
        : '/assets/img/avatar.png';
    document.getElementById('display-name').textContent = userData.name;
    document.getElementById('display-role').textContent = userData.role || 'User';

    // Overview tab
    document.getElementById('overview-name').textContent = userData.name;
    document.getElementById('overview-username').textContent = userData.username;
    document.getElementById('overview-email').textContent = userData.email;
    document.getElementById('overview-role').textContent = userData.role || 'User';

    // Edit form
    document.getElementById('name').value = userData.name;
    document.getElementById('username').value = userData.username;
    document.getElementById('email').value = userData.email;
}

async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        showToast('Image must be less than 2MB', 'error');
        return;
    }

    // Validate file type
    if (!file.type.match('image.*')) {
        showToast('Please upload an image file', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch('/api/profile/upload-image', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${sessionStorage.getItem('accessToken')}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        if (!response.ok) throw new Error('Failed to upload image');

        const data = await response.json();
        document.getElementById('profile-image').src = data.image;
        showToast('Profile image updated successfully', 'success');
    } catch (error) {
        showToast('Failed to upload image', 'error');
        console.error('Error:', error);
    }
}

async function handleProfileUpdate(event) {
    event.preventDefault();
    
    if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }

    const userId = sessionStorage.getItem('userId');
    const formData = {
        name: document.getElementById('name').value,
        username: document.getElementById('username').value,
        email: document.getElementById('email').value
    };

    try {
        const response = await fetch(`/api/profile/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${sessionStorage.getItem('accessToken')}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to update profile');
        }

        const data = await response.json();
        updateUIWithUserData(data.user);
        showToast('Profile updated successfully', 'success');
    } catch (error) {
        showToast(error.message, 'error');
        console.error('Error:', error);
    }
}

function setupPasswordStrengthMeter() {
    const passwordInput = document.getElementById('newPassword');
    const strengthMeter = document.getElementById('password-strength');
    
    passwordInput.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        updatePasswordStrengthUI(strength, strengthMeter);
    });
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]+/)) strength += 25;
    if (password.match(/[A-Z]+/)) strength += 25;
    if (password.match(/[0-9]+/)) strength += 25;
    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength += 25;

    return Math.min(100, strength);
}

function updatePasswordStrengthUI(strength, meterElement) {
    let color;
    if (strength <= 25) color = '#dc3545';
    else if (strength <= 50) color = '#ffc107';
    else if (strength <= 75) color = '#0dcaf0';
    else color = '#198754';

    meterElement.innerHTML = `<div style="width: ${strength}%; background-color: ${color};"></div>`;
}

async function handlePasswordUpdate(event) {
    event.preventDefault();
    
    if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }

    const userId = sessionStorage.getItem('userId');
    const formData = {
        currentPassword: document.getElementById('currentPassword').value,
        newPassword: newPassword,
        newPassword_confirmation: confirmPassword
    };

    try {
        const response = await fetch(`/api/profile/${userId}/password`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${sessionStorage.getItem('accessToken')}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to update password');
        }

        document.getElementById('password-form').reset();
        showToast('Password updated successfully', 'success');
    } catch (error) {
        showToast(error.message, 'error');
        console.error('Error:', error);
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('liveToast');
    const toastBody = toast.querySelector('.toast-body');
    const icon = toast.querySelector('.toast-header i');

    // Update toast styling based on type
    toast.classList.remove('bg-success', 'bg-danger');
    icon.classList.remove('bi-check-circle', 'bi-x-circle');
    
    if (type === 'success') {
        toast.classList.add('bg-success');
        icon.classList.add('bi-check-circle');
    } else {
        toast.classList.add('bg-danger');
        icon.classList.add('bi-x-circle');
    }

    toastBody.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}
</script>
@endsection