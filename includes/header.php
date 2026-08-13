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
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
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

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold" href="/mendnest/index.php">
            Mend Nest KE
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="/mendnest/index.php">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/pages/consultation.php">
                        Consultation
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/pages/services.php">
                        Services
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/pages/contact.php">
                        Contact
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/mendnest/pages/blog.php">
                        Blog
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>