<?php
session_start();
require_once '../database/connection.php';

const COOLDOWN_SECONDS = 30; // minimum gap between submissions from one session

function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$errors = [];
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    }

    $old['name']    = trim((string)($_POST['name'] ?? ''));
    $old['email']   = trim((string)($_POST['email'] ?? ''));
    $old['message'] = trim((string)($_POST['message'] ?? ''));

    // Honeypot: real users never see or fill this field; bots usually do.
    // Pretend success so the bot learns nothing.
    if (trim((string)($_POST['website'] ?? '')) !== '') {
        $_SESSION['flash_success'] = 'Thank you! Your message has been sent.';
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    // Simple rate limit
    $last = $_SESSION['last_contact'] ?? 0;
    if (!$errors && (time() - $last) < COOLDOWN_SECONDS) {
        $errors[] = 'Please wait a moment before sending another message.';
    }

    if ($old['name'] === '') {
        $errors[] = 'Please enter your name.';
    } elseif (mb_strlen($old['name']) > 100) {
        $errors[] = 'Name must be 100 characters or fewer.';
    }

    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($old['email']) > 150) {
        $errors[] = 'Email must be 150 characters or fewer.';
    }

    if ($old['message'] === '') {
        $errors[] = 'Please enter a message.';
    } elseif (mb_strlen($old['message']) < 10) {
        $errors[] = 'Your message is too short (at least 10 characters).';
    } elseif (mb_strlen($old['message']) > 3000) {
        $errors[] = 'Your message must be 3000 characters or fewer.';
    }

    if (!$errors) {
        try {
            $ip   = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmt = $conn->prepare(
                "INSERT INTO contact_messages (name, email, message, ip_address)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('ssss', $old['name'], $old['email'], $old['message'], $ip);
            $stmt->execute();
            $stmt->close();

            $_SESSION['last_contact']  = time();
            $_SESSION['flash_success'] = 'Thank you! Your message has been sent.';
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } catch (mysqli_sql_exception $ex) {
            error_log('Contact insert failed: ' . $ex->getMessage());
            $errors[] = 'Unable to send your message. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">

<h2 class="text-center mb-4">Contact Us</h2>

<div class="row justify-content-center">
<div class="col-md-6">

<?php if ($success !== ''): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" class="card shadow p-4">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

<!-- Honeypot field (hidden from humans) -->
<div style="position:absolute; left:-9999px;" aria-hidden="true">
    <label for="website">Leave this field empty</label>
    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
</div>

<label for="name" class="form-label">Your Name *</label>
<input type="text" id="name" name="name" class="form-control mb-3"
       maxlength="100" value="<?= e($old['name']) ?>"
       placeholder="Your Name" required>

<label for="email" class="form-label">Email *</label>
<input type="email" id="email" name="email" class="form-control mb-3"
       maxlength="150" value="<?= e($old['email']) ?>"
       placeholder="Email" required>

<label for="message" class="form-label">Message *</label>
<textarea id="message" name="message" class="form-control mb-3" rows="5"
          minlength="10" maxlength="3000" placeholder="Message"
          required><?= e($old['message']) ?></textarea>

<button type="submit" class="btn btn-success w-100">
    Send Message
</button>

</form>

</div>
</div>

</div>

<?php include '../includes/footer.php'; ?>