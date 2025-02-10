<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 60px;
        }

        /* Header Styles */
        .header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #eee;
            padding: 0 1rem;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
        }

        .search-bar {
            max-width: 400px;
            width: 100%;
            margin: 0 2rem;
        }

        .search-form {
            position: relative;
        }

        .search-form button {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #6c757d;
        }

        .nav-profile img {
            width: 36px;
            height: 36px;
            object-fit: cover;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }

        .nav-link {
            border-radius: 8px;
            transition: all 0.2s ease;
            color: #6c757d;
            padding: 0.8rem 1rem;
            margin: 2px 0;
        }

        .nav-link:hover {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .nav-link.active {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            font-weight: 500;
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 24px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .header {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .search-bar {
                display: none;
            }
        }

        /* Dropdown Menus */
        .dropdown-menu {
            border: 0;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
        }

        .notifications, .messages {
            min-width: 280px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: start;
            padding: 1rem;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar bg-white shadow-lg">
        <div class="d-flex flex-column h-100">
            <div class="sidebar-brand p-3 border-bottom">
                <a href="/" class="d-flex align-items-center text-decoration-none">
                    <i class="bi bi-printer-fill text-primary fs-4 me-2"></i>
                    <span class="fs-5 fw-semibold text-dark">NpSoft</span>
                </a>
            </div>

            <div class="sidebar-content p-3 flex-grow-1 overflow-auto">
                <!-- Main Menu -->
                <div class="mb-4">
                    <span class="section-title text-uppercase fw-semibold text-muted d-block mb-3">Menu principal</span>
                    <ul class="nav flex-column" id="sidebarNav">
                        <!-- Menu items will be loaded via JavaScript -->
                    </ul>
                </div>
            </div>

            <!-- Logout Button -->
            <div class="mt-auto p-3 border-top">
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light d-flex align-items-center justify-content-center w-100 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        <span>Se déconnecter</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Header -->
    <header class="header d-flex align-items-center">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-lg-none" id="toggleSidebar">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>

        <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="GET" action="#">
                <input type="text" name="query" class="form-control" placeholder="Search">
                <button type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>


            <div class="header-toggler d-flex align-items-center">
                <button class="toggle-sidebar-btn btn btn-link d-lg-none px-2 py-1">
                    <i class="bi bi-list fs-4 text-white"></i>
                </button>
                <a href="/" class="logo d-flex align-items-center ms-2 ms-lg-3">
                    <i class="bi bi-printer-fill text-white fs-4 me-2"></i>
                    <span class="d-none d-lg-block text-white">NpSoft</span>
                </a>

            <ul class="d-flex align-items-center mb-0">
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-primary badge-number notification-count">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notifications" id="notificationsDropdown">
                        <!-- Notifications will be loaded via JavaScript -->
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="badge bg-success badge-number messages-count">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end messages" id="messagesDropdown">
                        <!-- Messages will be loaded via JavaScript -->
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <img src="/assets/img/avatar.jpg" alt="Profile" class="rounded-circle" id="userAvatar">
                        <span class="d-none d-md-block dropdown-toggle ps-2" id="userName">Loading...</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end profile">
                        <li class="dropdown-header">
                            <h6 id="profileName">Loading...</h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="/profile">
                                <i class="bi bi-person me-2"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main class="main-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar-btn');

            // Create overlay element
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            // Toggle sidebar function
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                // Toggle aria-expanded attribute
                const isExpanded = sidebar.classList.contains('show');
                toggleBtn.setAttribute('aria-expanded', isExpanded);
            }

            // Event listeners
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }

            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                if (sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });

            // Close sidebar when pressing Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });

            // Close sidebar when window is resized to desktop size
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992 && sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });
        });
        </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup CSRF token for all AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

            // Toggle sidebar on mobile
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.querySelector('.sidebar');

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (event) => {
                if (window.innerWidth < 992 &&
                    !sidebar.contains(event.target) &&
                    !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            });

            // Load user profile data
            const loadUserProfile = async () => {
                try {
                    const response = await fetch('/api/profile');
                    if (!response.ok) throw new Error('Failed to load profile');
                    const data = await response.json();

                    document.getElementById('userAvatar').src = data.avatar_url || '/assets/img/avatar.jpg';
                    document.getElementById('userName').textContent = data.name || 'Guest User';
                    document.getElementById('profileName').textContent = data.name || 'Guest User';
                } catch (error) {
                    console.error('Error loading profile:', error);
                }
            };

            // Load notifications
            const loadNotifications = async () => {
                try {
                    const response = await fetch('/api/notifications');
                    if (!response.ok) throw new Error('Failed to load notifications');
                    const data = await response.json();

                    const count = data.length;
                    document.querySelector('.notification-count').textContent = count;

                    const dropdown = document.getElementById('notificationsDropdown');
                    dropdown.innerHTML = data.map(notification => `
                        <li class="notification-item">
                            <i class="bi ${notification.icon} text-${notification.type}"></i>
                            <div>
                                <h4>${notification.title}</h4>
                                <p>${notification.message}</p>
                                <p>${notification.time_ago}</p>
                            </div>
                        </li>
                    `).join('');
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            };

            // Load messages
            const loadMessages = async () => {
                try {
                    const response = await fetch('/api/messages');
                    if (!response.ok) throw new Error('Failed to load messages');
                    const data = await response.json();

                    const count = data.length;
                    document.querySelector('.messages-count').textContent = count;

                    const dropdown = document.getElementById('messagesDropdown');
                    dropdown.innerHTML = data.map(message => `
                        <li class="message-item">
                            <div>
                                <h4>${message.sender}</h4>
                                <p>${message.content}</p>
                                <p>${message.time_ago}</p>
                            </div>
                        </li>
                    `).join('');
                } catch (error) {
                    console.error('Error loading messages:', error);
                }
            };

            // Initialize
            loadUserProfile();
            loadNotifications();
            loadMessages();

            // Refresh data periodically
            setInterval(() => {
                loadNotifications();
                loadMessages();
            }, 60000); // Refresh every minute
        });
    </script>
</body>
</html>
