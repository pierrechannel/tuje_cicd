<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center" title="Go to the homepage">
            <i class="bi bi-printer-fill text-primary fs-4 me-2"></i>
            <span class="d-none d-lg-block">NpSoft</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn" aria-label="Toggle sidebar"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
        <form class="search-form d-flex align-items-center" method="GET" action="#">
            <input type="text" name="query" class="form-control" placeholder="Search" title="Enter search keyword" aria-label="Search">
            <button type="submit" title="Search" aria-label="Search">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <!-- Notification Nav -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-primary badge-number">4</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                    <li class="dropdown-header">
                        You have 4 new notifications
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="notification-item">
                        <i class="bi bi-exclamation-circle text-warning"></i>
                        <div>
                            <h4>Lorem Ipsum</h4>
                            <p>Quae dolorem earum veritatis oditseno</p>
                            <p>30 min. ago</p>
                        </div>
                    </li>
                    <li class="dropdown-footer">
                        <a href="#">Show all notifications</a>
                    </li>
                </ul>
            </li>

            <!-- Messages Nav -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" aria-label="Messages">
                    <i class="bi bi-chat-left-text"></i>
                    <span class="badge bg-success badge-number">3</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                    <li class="dropdown-header">
                        You have 3 new messages
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-footer">
                        <a href="#">Show all messages</a>
                    </li>
                </ul>
            </li>

            <!-- Profile Nav -->
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-label="Profile">
                    <img src="{{ asset('assets/img/avatar.jpg') }}" alt="Profile Picture" class="rounded-circle" aria-label="Profile Picture">
                    <span class="d-none d-md-block dropdown-toggle ps-2">Guest User</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6 id="profile-name">Kevin Anderson</h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="/profile"><i class="bi bi-person"></i><span>My Profile</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="#"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
                </ul>
            </li>
        </ul>
    </nav><!-- End Icons Navigation -->
</header><!-- End Header -->
<div class="overlay"> <!-- Add the overlay -->


<aside id="sidebar" class="sidebar bg-white shadow-sm border-end">
    <div class="d-flex flex-column min-vh-100">
        {{-- Sidebar Brand/Logo --}}

        {{-- Navigation Menu --}}
        <div class="sidebar-menu p-3 flex-grow-1 overflow-y-auto">

            <nav class="nav-menu">
                {{-- Primary Navigation --}}
                <div class="nav-section mb-4">
                    <small class="text-uppercase fw-semibold text-muted px-3 mb-2 d-block">Menu principal</small>
                    <ul class="nav flex-column">
                        <li class="nav-item" id="dashboard-item">
                            <a href="/stats" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('dashboard') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-grid-1x2-fill me-2"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/transactions" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('transactions.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-credit-card me-2"></i>
                                <span>Transactions</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/customers" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('customers.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-people me-2"></i>
                                <span>Clients</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/services" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('debts.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi-briefcase me-2 "></i>
                                <span>Services</span>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Financial Section --}}
                <div class="nav-section mb-4">
                    <small class="text-uppercase fw-semibold text-muted px-3 mb-2 d-block">Financier</small>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="/expenses" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('expenses.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-cash-stack me-2"></i>
                                <span>Dépenses</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/debts" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('debts.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-currency-dollar me-2"></i>
                                <span>Dettes</span>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="/payments" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('payments.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-wallet2 me-2"></i>
                                <span>Paiements</span>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- System Section --}}
                <div class="nav-section mb-4">
                    <small class="text-uppercase fw-semibold text-muted px-3 mb-2 d-block">Système</small>
                    <ul class="nav flex-column">
                        <li class="nav-item" id="user-item">
                            <a href="/users" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('users.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-person-gear me-2"></i>
                                <span>Utilisateurs</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/debts-report" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('reports.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                <span>Rapports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/settings" class="nav-link d-flex align-items-center py-2 px-3 {{ request()->routeIs('settings.*') ? 'active bg-primary-subtle text-primary' : 'text-body' }}">
                                <i class="bi bi-gear me-2"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>

                      <!-- In the sidebar menu, update the logout button -->
<li class="nav-item">
    <a href="javascript:void(0);" onclick="logout()" class="nav-link d-flex align-items-center py-2 px-3 text-danger">
        <i class="bi bi-box-arrow-right me-2"></i>
        <span>Déconnexion</span>
    </a>
</li>

<script>

</script>
                    </ul>

                </div>
            </nav>
        </div>

    </div>
</aside>
</div>

<style>
.sidebar {
    width: 280px;
    transition: all 0.3s ease-in-out;
}

.nav-link {
    transition: all 0.2s ease-in-out;
    border-radius: 0.5rem;
}

.nav-link:hover {
    background-color: var(--bs-primary-bg-subtle);
    color: var(--bs-primary) !important;
}

.nav-link.active {
    font-weight: 500;
}

.nav-link i {
    font-size: 1.1rem;
}





@media (max-width: 991.98px) {
        .sidebar {
            position: fixed;
            left: -280px;
            top: 0;
            bottom: 0;
            z-index: 1045;
            transition: left 0.3s ease-in-out; /* Smooth transition effect */
        }

        .sidebar.show {
            left: 0;
        }

        /* Add an overlay when the sidebar is open */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1040; /* Just below the sidebar */
            display: none; /* Initially hidden */
        }

        .overlay.show {
            display: block; /* Show overlay when sidebar is open */
        }
    }
.sidebar-menu {
    height: calc(100vh - 70px - 97px); /* Adjust based on your header and footer heights */
}
</style>
<div class="overlay"></div> <!-- Add the overlay -->


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Logout function
        window.logout = function() {
    const token = sessionStorage.getItem('accessToken'); // Assuming you are storing your token here
    if (!token) {
        alert("You are not authenticated!");
        return; // Prevent further execution if not authenticated
    }

    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        credentials: 'include' // Ensure any cookies are sent
    })
    .then(response => {
        if (response.status === 401) {
            throw new Error('Unauthorized. Please log in again.');
        }
        if (!response.ok) {
            throw new Error('Logout failed. Please try again.');
        }
        return response.json(); // Assuming your API returns JSON
    })
    .then(data => {
        alert(data.message || 'Logged out successfully!');
        window.location.href = '/login';
    })
    .catch(err => {
        console.error('Error during logout:', err);
        alert('Could not log out: ' + err.message);
    });
};
        // Retrieve user ID from sessionStorage
        const userId = sessionStorage.getItem('userId'); // or any other reliable way to get user ID
        // If userId exists, fetch user profile data from the API using the user ID
        if (userId) {
            fetch(`/api/profile/${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update the profile image and name in the header
                    const profileImage = document.querySelector('.nav-profile img');
                    const profileName = document.querySelector('.nav-profile span');

                    profileImage.src = data.image ? `/storage/${data.image}` : '{{ asset('assets/img/avatar.png') }}';
                    profileName.textContent = data.username || 'Guest User'; // Fallback if name is not available

                    // Also update the dropdown header
                    document.getElementById('profile-name').textContent = data.name || 'Guest User';
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    // Set default values in case of error
                    const profileImage = document.querySelector('.nav-profile img');
                    const profileName = document.querySelector('.nav-profile span');

                    profileImage.src = '{{ asset('assets/img/avatar.png') }}';
                    profileName.textContent = 'Guest User';
                    document.getElementById('profile-name').textContent = 'Guest User';
                });
        } else {
            console.warn('User ID not found in sessionStorage.');
            window.location.href = '/login';
        }

        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.querySelector('.toggle-sidebar-btn');
        const overlay = document.querySelector('.overlay');

        const toggleSidebar = () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show'); // Toggle overlay visibility
        };

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show'); // Hide overlay when sidebar is closed
        });
        // Updated logout function
        function logout() {
    const token = sessionStorage.getItem('accessToken');
    console.log('Logging out, current token:', token);

    // Clear all storage first
    if (sessionStorage.getItem('accessToken')) {
    sessionStorage.removeItem('accessToken');
} else {
    console.log('accessToken does not exist.');
}    sessionStorage.removeItem('userId');
    sessionStorage.removeItem('username');
    sessionStorage.clear();
  console.log(sessionStorage.getItem('accessToken'));
    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        }
    })
    .then(response => {
        console.log('Logout response:', response.status);
        // Force reload the login page
        window.location.replace('/login');
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Force reload even on error
        window.location.replace('/login');
    });

    return false; // Prevent any default behavior
}
// Add event listener for debugging - remove in production
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.querySelector('[onclick="logout()"]');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Logout button clicked');
            logout();
        });
    }
});
    });
    </script>

    <script>
    // Assume userRole comes from your backend or a global variable
    const userRole = sessionStorage.getItem('userRole');  // Change this value for testing ('user', 'manager', or 'admin')

    // Select the dashboard item element
    const dashboardItem = document.getElementById('dashboard-item');
    const userItem = document.getElementById('user-item')

    // Check the user role
    if (userRole === 'user') {
        // If the user role is "user", remove the dashboard item
        if (dashboardItem) {
            dashboardItem.style.display = 'none'; // Hide it
            userItem.style.display = 'none'; // Hide it
            // Alternatively, you could also remove it from the DOM
            // dashboardItem.remove();
        }
    }
</script>
