<?php
session_start();
require_once '../database/connection.php';

/* ---------- Access control ----------
   As written, anyone could publish posts. Uncomment and adapt to your login system:

if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
*/

/* ---------- Settings ---------- */
const UPLOAD_DIR   = __DIR__ . '/../uploads/blog/';
const MAX_IMG_SIZE = 2 * 1024 * 1024; // 2 MB
const ALLOWED_IMG  = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$errors = [];
$old = ['title' => '', 'content' => ''];

/* ---------- Handle submission ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    }

    $old['title']   = trim((string)($_POST['title'] ?? ''));
    $old['content'] = trim((string)($_POST['content'] ?? ''));

    if ($old['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($old['title']) > 200) {
        $errors[] = 'Title must be 200 characters or fewer.';
    }

    if ($old['content'] === '') {
        $errors[] = 'Content is required.';
    } elseif (mb_strlen($old['content']) > 20000) {
        $errors[] = 'Content must be 20,000 characters or fewer.';
    }

    /* ---------- Optional image upload ---------- */
    $newFileName = null;
    $file = $_FILES['image'] ?? null;

    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE)
                ? 'The image is too large (max 2 MB).'
                : 'The image could not be uploaded. Please try again.';
        } elseif ($file['size'] > MAX_IMG_SIZE) {
            $errors[] = 'The image is too large (max 2 MB).';
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid upload.';
        } else {
            // Check the real content type, not the client-supplied name/type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);

            if (!isset(ALLOWED_IMG[$mime]) || @getimagesize($file['tmp_name']) === false) {
                $errors[] = 'Only JPG, PNG or WebP images are allowed.';
            } else {
                // Random server-generated name; extension comes from the verified type
                $newFileName = bin2hex(random_bytes(16)) . '.' . ALLOWED_IMG[$mime];
            }
        }
    }

    /* ---------- Save ---------- */
    if (!$errors) {
        $moved = false;
        try {
            if ($newFileName !== null) {
                if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
                    throw new RuntimeException('Cannot create upload directory.');
                }
                if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newFileName)) {
                    throw new RuntimeException('Cannot move uploaded file.');
                }
                $moved = true;
            }

            $stmt = $conn->prepare("INSERT INTO blogs (title, content, image) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $old['title'], $old['content'], $newFileName);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_success'] = 'Your blog post has been published!';
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } catch (Throwable $ex) {
            // Don't leave an orphaned image if the DB insert failed
            if ($moved && is_file(UPLOAD_DIR . $newFileName)) {
                unlink(UPLOAD_DIR . $newFileName);
            }
            error_log('Create blog failed: ' . $ex->getMessage());
            $errors[] = 'Unable to publish your post. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="py-5">
<div class="container">

<h2 class="fw-bold mb-4">Create Blog Post</h2>

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

<form method="POST" enctype="multipart/form-data" class="blog-form">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

<label for="title" class="form-label">Blog Title *</label>
<input type="text" id="title" name="title" class="form-control mb-3"
       maxlength="200" value="<?= e($old['title']) ?>"
       placeholder="Blog Title" required>

<label for="content" class="form-label">Content *</label>
<textarea id="content" name="content" class="form-control mb-3" rows="6"
          maxlength="20000" placeholder="Write blog content..."
          required><?= e($old['content']) ?></textarea>

<label for="image" class="form-label">Featured Image (optional)</label>
<input type="file" id="image" name="image" class="form-control mb-1"
       accept="image/jpeg,image/png,image/webp">
<div class="form-text mb-3">JPG, PNG or WebP, up to 2 MB.</div>

<button type="submit" name="submit" value="1" class="btn btn-success">
    Publish Blog
</button>

</form>

</div>
</section>

<?php include '../includes/footer.php'; ?>