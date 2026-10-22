<?php
/* Handles the testimonial form on index.php. Lives in its own file so it can
   redirect cleanly (a redirect fails if the page has already printed HTML). */
require_once '../includes/auth.php';
require_once '../database/connection.php';

const RETURN_URL       = BASE_URL . '/index.php#testimonials';
const COOLDOWN_SECONDS = 60;

function back() {
    header('Location: ' . RETURN_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back();
}

$errors  = [];
$name    = trim((string)($_POST['t_name'] ?? ''));
$message = trim((string)($_POST['t_message'] ?? ''));

// Honeypot: bots fill it, humans never see it. Pretend success.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    $_SESSION['t_success'] = 'Thank you! Your testimonial will appear once it has been reviewed.';
    back();
}

if (!csrf_valid()) {
    $errors[] = 'Your session expired. Please try again.';
}

if (!$errors && (time() - ($_SESSION['last_testimonial'] ?? 0)) < COOLDOWN_SECONDS) {
    $errors[] = 'Please wait a moment before submitting again.';
}

if ($name === '') {
    $errors[] = 'Please enter your name.';
} elseif (mb_strlen($name) > 100) {
    $errors[] = 'Name must be 100 characters or fewer.';
}

if ($message === '') {
    $errors[] = 'Please share your experience.';
} elseif (mb_strlen($message) < 10) {
    $errors[] = 'Your testimonial is too short (at least 10 characters).';
} elseif (mb_strlen($message) > 1000) {
    $errors[] = 'Your testimonial must be 1000 characters or fewer.';
}

if (!$errors) {
    try {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $conn->prepare("INSERT INTO testimonials (name, message, ip_address) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $message, $ip);
        $stmt->execute();
        $stmt->close();

        $_SESSION['last_testimonial'] = time();
        $_SESSION['t_success'] = 'Thank you! Your testimonial will appear once it has been reviewed.';
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        back();
    } catch (mysqli_sql_exception $ex) {
        error_log('Testimonial insert failed: ' . $ex->getMessage());
        $errors[] = 'Unable to save your testimonial. Please try again.';
    }
}

// Failed: send errors and the typed text back to the form
$_SESSION['t_errors'] = $errors;
$_SESSION['t_old']    = ['name' => $name, 'message' => $message];
back();