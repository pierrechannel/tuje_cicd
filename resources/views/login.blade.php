<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - TujeSoft</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }

        body {
            background: linear-gradient(135deg, #f8f9fc 0%, #e8eef9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 90%;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .logo img {
            height: 60px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #e1e5ea;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.15);
            border-color: var(--primary-color);
            background-color: #fff;
        }

        .input-group-text {
            border-radius: 12px 0 0 12px;
            border: 2px solid #e1e5ea;
            border-right: none;
            background-color: #f8f9fa;
            color: var(--secondary-color);
        }

        .btn-primary {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
            background-color: #4262c7;
        }

        .form-check-input {
            border-radius: 6px;
            border: 2px solid #e1e5ea;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toggle-password {
            border: none;
            background: none;
            padding: 0 15px;
            color: var(--secondary-color);
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .card-title {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .floating-shape {
            position: fixed;
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, var(--primary-color), #7289da);
            border-radius: 50%;
            filter: blur(20px);
            opacity: 0.1;
            animation: float 10s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(100px, 100px) rotate(180deg); }
        }
    </style>
</head>

<body>
    <!-- Decorative background shapes -->
    <div class="floating-shape" style="top: 10%; left: 10%;"></div>
    <div class="floating-shape" style="top: 70%; right: 10%;"></div>
    <div class="floating-shape" style="bottom: 20%; left: 50%;"></div>

    <main>
        <div class="container">
            <section class="min-vh-100 d-flex align-items-center justify-content-center py-5">
                <div class="login-container">
                    <div class="card">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <h5 class="card-title mb-2">Welcome Back! 👋</h5>
                                <p class="text-muted">Please sign in to continue</p>
                            </div>

                            <div id="response-message"></div>

                            <form id="login-form" class="needs-validation" novalidate>
                                @csrf
                                <div class="mb-4">
                                    <label for="yourUsername" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" name="username" class="form-control" id="yourUsername" 
                                               placeholder="Enter your username" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="yourPassword" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control" id="yourPassword" 
                                               placeholder="Enter your password" required>
                                        <button class="btn toggle-password" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">Remember me</label>
                                    </div>
                                    <a href="#" class="text-primary text-decoration-none">Forgot Password?</a>
                                </div>

                                <button class="btn btn-primary w-100 mb-3 d-flex align-items-center justify-content-center" type="submit">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                    Sign In
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            &copy; 2025 TujeSoft. Designed by <a href="https://npcode.com/" class="text-decoration-none">Npcode</a>
                        </small>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Toggle password visibility with animation
            $('#togglePassword').click(function() {
                const passwordInput = $('#yourPassword');
                const icon = $(this).find('i');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
                
                // Add animation to the icon
                icon.addClass('animate__animated animate__flipInX');
                setTimeout(() => icon.removeClass('animate__animated animate__flipInX'), 500);
            });

            // Form submission handler
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $(this).find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');
                
                // Show loading state
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: '/api/login',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === "success") {
                            // Store user data
                            sessionStorage.setItem('userId', response.data.user.id);
                            sessionStorage.setItem('username', response.data.user.username);
                            sessionStorage.setItem('userRole', response.data.user.role);
                            sessionStorage.setItem('accessToken', response.data.access_token);

                            // Show success message with animation
                            $('#response-message').html(`
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div>${response.message}</div>
                                </div>
                            `);

                            // Redirect with delay
                            setTimeout(() => {
                                window.location.href = '/stats';
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        $('#response-message').html(`
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>${xhr.responseJSON?.message || 'Login failed. Please check your credentials.'}</div>
                            </div>
                        `);
                        
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });

            // Input validation feedback
            $('input').on('input', function() {
                if (this.checkValidity()) {
                    $(this).addClass('is-valid').removeClass('is-invalid');
                } else {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                }
            });
        });
    </script>
</body>
</html>