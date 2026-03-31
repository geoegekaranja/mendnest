<?php
// header.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mend Nest KE - Home Nursing</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/mendnest/css/style.css">

    <!-- Google Fonts (optional but professional) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background:#198754;">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand fw-bold" href="/mendnest/index.php">
            Mend Nest KE
        </a>

        <!-- TOGGLE BUTTON (MOBILE) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/about.php">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/services.php">Services</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/packages.php">Packages</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/blog.php">Blog</a>
                </li>

                <!-- CTA BUTTON -->
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-light fw-semibold px-4" href="/mendnest/consultation.php">
                        Request Care
                    </a>
                </li>

            </ul>
        </div>

    </div>
</nav>
<!-- =============== END NAVBAR =============== -->