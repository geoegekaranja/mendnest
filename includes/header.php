<?php
require_once __DIR__ . '/auth.php';   // starts the session; gives us current_user(), h(), BASE_URL

if (!function_exists('page_url')) {
    /* Web address of a site page. Looks in /pages first, then the site root.
       Returns null when the file doesn't exist, so we never print a dead link. */
    function page_url(string $file): ?string {
        $root = dirname(__DIR__);
        foreach (['/pages/', '/'] as $dir) {
            if (is_file($root . $dir . $file)) {
                return BASE_URL . $dir . $file;
            }
        }
        return null;
    }
}

$navUser = current_user();
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Version number on the CSS link so the browser never shows an old cached copy
$cssFile = dirname(__DIR__) . '/css/style.css';
$cssVer  = is_file($cssFile) ? filemtime($cssFile) : 0;

// Main menu. Add or rename pages here; missing files are skipped automatically.
$navItems = [
    'Home'         => BASE_URL . '/index.php',
    'About'        => page_url('about.php'),
    'Services'     => page_url('services.php'),
    'Consultation' => page_url('consultation.php'),
    'Blog'         => page_url('blog.php'),
    'Contact'      => page_url('contact.php'),
];

// "Book Appointment" is only for signed-in patients (it opens their booking form).
// Visitors use the Consultation page; admins don't book.
$bookUrl   = null;
$bookLabel = 'Book Appointment';
if ($navUser && $navUser['role'] === 'patient') {
    $bookUrl = BASE_URL . '/patient/book.php';
}
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
    <link rel="stylesheet" href="<?= h(BASE_URL) ?>/css/style.css?v=<?= (int)$cssVer ?>">

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

        <a class="navbar-brand fw-bold" href="<?= h(BASE_URL) ?>/index.php">
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

                <?php foreach ($navItems as $label => $url): ?>
                    <?php
                    if (!$url) { continue; }
                    $isActive = basename((string)parse_url($url, PHP_URL_PATH)) === $current;
                    ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= h($url) ?>"><?= h($label) ?></a>
                    </li>
                <?php endforeach; ?>

                <?php if ($bookUrl): ?>
                    <li class="nav-item ms-lg-2 my-2 my-lg-0">
                        <a class="btn btn-success btn-sm" href="<?= h($bookUrl) ?>"><?= h($bookLabel) ?></a>
                    </li>
                <?php endif; ?>

                <?php if ($navUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= h(ROLE_HOME[$navUser['role']] ?? LOGIN_URL) ?>">My Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="<?= h(BASE_URL) ?>/logout.php" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= h(BASE_URL) ?>/login.php">Login</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>