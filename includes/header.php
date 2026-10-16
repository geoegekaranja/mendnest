<?php
require_once __DIR__ . '/auth.php';   // starts the session; gives us current_user(), h(), BASE_URL
$navUser = current_user();
$navBase = h(BASE_URL);
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
    <!-- AOS (animations) -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $navBase ?>/css/style.css">

    <!-- Google Fonts -->
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

        <a class="navbar-brand fw-bold" href="<?= $navBase ?>/index.php">
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
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link active" href="<?= $navBase ?>/index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $navBase ?>/pages/consultation.php">Consultation</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $navBase ?>/pages/services.php">Services</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $navBase ?>/pages/contact.php">Contact</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $navBase ?>/pages/blog.php">Blog</a>
                </li>

                <?php if ($navUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= h(ROLE_HOME[$navUser['role']] ?? LOGIN_URL) ?>">My Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="<?= $navBase ?>/logout.php" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $navBase ?>/login.php">Login</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>