
<?php
session_start();

include '../database/connection.php';

/* HANDLE TESTIMONIAL SUBMISSION */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_testimonial'])) {

    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $message === '') {
        $_SESSION['testimonial_error'] =
            'Please enter your name and experience.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO testimonials (name, message, status)
             VALUES (?, ?, 'pending')"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $name, $message);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['testimonial_success'] =
                    'Thank you! Your testimonial has been submitted for review.';
            } else {
                $_SESSION['testimonial_error'] =
                    'Unable to submit your testimonial. Please try again.';
            }

            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['testimonial_error'] =
                'Something went wrong. Please try again.';
        }
    }

    /* Prevent duplicate submissions on refresh */
    header('Location: ' . $_SERVER['PHP_SELF'] . '#testimonials');
    exit;
}

/* DISPLAY ONLY APPROVED TESTIMONIALS */
$result = mysqli_query(
    $conn,
    "SELECT name, message
     FROM testimonials
     WHERE status = 'approved'
     ORDER BY id DESC"
);

$success = $_SESSION['testimonial_success'] ?? '';
$error = $_SESSION['testimonial_error'] ?? '';

unset(
    $_SESSION['testimonial_success'],
    $_SESSION['testimonial_error']
);
?>

<link rel="stylesheet" href="/mendnest/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet">

<section class="testimonial-section" id="testimonials">
    <div class="container">

        <h2 class="text-center fw-bold mb-5">
            Testimonials
        </h2>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- TESTIMONIAL FORM -->
        <div class="testimonial-form-wrapper mx-auto mb-5"
             style="max-width: 700px;">

            <form method="POST"
                  action="#testimonials">

                <div class="mb-3">
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Your Name"
                        maxlength="100"
                        required>
                </div>

                <div class="mb-3">
                    <textarea
                        name="message"
                        class="form-control"
                        placeholder="Your Experience"
                        rows="3"
                        maxlength="2000"
                        required></textarea>
                </div>

                <div class="text-center">
                    <button
                        type="submit"
                        name="submit_testimonial"
                        value="1"
                        class="btn btn-success">
                        Submit
                    </button>
                </div>

            </form>
        </div>

        <!-- APPROVED TESTIMONIALS -->
        <div class="row justify-content-center g-4">

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4">
                        <div class="card testimonial-card h-100 p-4">

                            <p class="text-muted mb-3">
                                &ldquo;<?= htmlspecialchars(
                                    $row['message'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>&rdquo;
                            </p>

                            <h6 class="fw-bold mb-0">
                                &mdash; <?= htmlspecialchars(
                                    $row['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h6>

                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12 text-center">
                    <p class="text-muted">
                        No approved testimonials yet.
                        Be the first to share your experience!
                    </p>
                </div>

            <?php endif; ?>

        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>
