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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Retrieve user ID from sessionStorage
    const userId = sessionStorage.getItem('userId'); // Assuming userId is stored as a string

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
});
</script>
