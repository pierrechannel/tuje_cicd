<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'npsoft')</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="{{ asset('assets\vendor\apexcharts\apexcharts.css') }}" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/DataTables/datatables.min.css') }}" rel="stylesheet">




  <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.8/af-2.7.0/b-3.2.0/date-1.5.4/r-3.0.3/datatables.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

  <style>
    body {
        background-color: #f8f9fa; /* Light background color */
    }

    header {
        background-color: #003366; /* Dark blue for the header */
        color: #ffffff; /* White text */
    }

    #sidebar {
        background-color: #004080; /* Darker blue for the sidebar */
        color: #ffffff; /* White text */
        height: 100vh; /* Full height */
        position: fixed; /* Fix sidebar on the left */
        width: 250px; /* Set width */
        transition: 0.3s; /* Transition effect for hover */
    }

    .sidebar .nav-item {
        list-style: none; /* Remove bullet points */
    }

    .sidebar-nav .nav-item a {
        display: flex; /* Align items */
        align-items: center; /* Center vertically */
        padding: 15px; /* Padding for links */
        color: #ffffff; /* White text */
        text-decoration: none; /* Remove underline */
    }

    .sidebar-nav .nav-item a:hover {
        background-color: #0056b3; /* Change background color on hover */
    }

    .sidebar-nav .nav-link.active {
        background-color: #007bff; /* Blue for active links */
    }

    .nav-content {
        padding-left: 20px; /* Indent submenus */
    }

    .footer {
        background-color: #003366; /* Dark blue for the footer */
        color: #ffffff; /* White text */
        text-align: center;
        padding: 10px 0;
        position: relative;
        bottom: 0;
        width: 100%;
    }

    /* Back to top button styling */
    .back-to-top {
        background-color: #007bff; /* Blue for back to top button */
        color: white;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: none; /* Hidden by default */
        align-items: center;
        justify-content: center;
        cursor: pointer; /* Change cursor to pointer */
    }
  </style>
</head>

<body>

  <!-- ======= Header ======= -->
  @include('layouts.sidebar')



  <main id="main" class="main">

    @yield('content')

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  @include('layouts.footer')

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  @yield('scripts')
  <!-- Vendor JS Files -->
  <script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
  <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('assets/vendor/DataTables/datatables.min.js') }}"></script>
  <script src="{{ asset('assets\vendor\apexcharts\apexcharts.min.js') }}"></script>



  <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.8/af-2.7.0/b-3.2.0/date-1.5.4/r-3.0.3/datatables.min.js"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script>
    $(document).ready(function() {
        function clearCache() {
            $.ajax({
                url: '/api/clear-cache', // The API endpoint
                method: 'POST',
                success: function(response) {
                    console.log(response.message); // Log success message
                    // Optionally, show an alert
                },
                error: function(xhr) {
                    console.error('Error: ' + xhr.responseJSON.message); // Log error message
                    // Optionally, show an alert
                }
            });
        }

        // Clear cache initially when the page loads
        clearCache();

        // Set interval to clear cache every 30 seconds
        setInterval(clearCache, 30000); // 30000 milliseconds = 30 seconds
    });
    </script>

</body>

</html>
