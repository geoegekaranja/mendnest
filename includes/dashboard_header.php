<?php
/* Shared dashboard layout (sidebar + top bar) for BOTH admin and patient areas.
   The menu is picked from the signed-in user's role.
   Set $pageTitle and $active before including this file. */
require_once __DIR__ . '/auth.php';

$dashUser = current_user();
$isAdmin  = ($dashUser['role'] ?? '') === 'admin';
$area     = $isAdmin ? 'admin' : 'patient';
$areaDir  = dirname(__DIR__) . '/' . $area . '/';

// [active key, label, file inside the area folder, icon]
$menuDef = $isAdmin ? [
    ['dashboard',     'Dashboard',     'dashboard.php',     'bi-speedometer2'],
    ['bookings',      'Bookings',      'bookings.php',      'bi-calendar-check'],
    ['consultations', 'Consultations', 'consultations.php', 'bi-chat-dots'],
    ['blogs',         'Blog Posts',    'blogs.php',         'bi-journal-text'],
    ['testimonials',  'Testimonials',  'testimonials.php',  'bi-star'],
] : [
    ['dashboard', 'Dashboard',        'dashboard.php',   'bi-speedometer2'],
    ['book',      'Book Appointment', 'book.php',        'bi-calendar-plus'],
    ['bookings',  'My Bookings',      'my_bookings.php', 'bi-calendar-check'],
    ['profile',   'My Profile',       'profile.php',     'bi-person'],
];
// Pages that don't exist yet are skipped, so the menu never has dead links
$menu = array_filter($menuDef, fn($m) => is_file($areaDir . $m[2]));

$pageTitle = $pageTitle ?? ($isAdmin ? 'Admin Dashboard' : 'My Dashboard');
$active    = $active ?? '';

$cssFile = dirname(__DIR__) . '/css/dashboard.css';
$cssVer  = is_file($cssFile) ? filemtime($cssFile) : 0;
$initial = mb_strtoupper(mb_substr((string)($dashUser['name'] ?? '?'), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Mend Nest KE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(BASE_URL) ?>/css/dashboard.css?v=<?= (int)$cssVer ?>">
</head>
<body class="dash <?= h($area) ?>-area">

<!-- SIDEBAR -->
<aside class="dash-sidebar" id="dashSidebar">
    <div class="dash-brand">
        <a href="<?= h(BASE_URL) ?>/index.php">Mend Nest KE</a>
        <small><?= $isAdmin ? 'Admin Panel' : 'Patient Portal' ?></small>
    </div>

    <ul class="dash-menu">
        <?php foreach ($menu as [$key, $label, $file, $icon]): ?>
            <li>
                <a href="<?= h(BASE_URL . '/' . $area . '/' . $file) ?>"
                   class="<?= $active === $key ? 'active' : '' ?>">
                    <i class="bi <?= h($icon) ?>"></i><span><?= h($label) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="dash-side-footer">
        <a href="<?= h(BASE_URL) ?>/index.php"><i class="bi bi-house"></i> View Website</a>
        <form method="POST" action="<?= h(BASE_URL) ?>/logout.php">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
    </div>
</aside>
<div class="dash-backdrop" id="dashBackdrop"></div>

<!-- MAIN -->
<div class="dash-main">
    <header class="dash-topbar">
        <button class="dash-toggle" id="dashToggle" type="button" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="dash-title"><?= h($pageTitle) ?></h1>
        <div class="dash-user">
            <div class="dash-avatar"><?= h($initial) ?></div>
            <div class="dash-user-info">
                <div class="dash-user-name"><?= h($dashUser['name'] ?? '') ?></div>
                <div class="dash-user-role"><?= h($dashUser['role'] ?? '') ?></div>
            </div>
        </div>
    </header>

    <main class="dash-content">
        <?= flash_render() ?>