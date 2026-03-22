<?php
include '../database/connection.php';

/* HANDLE SUBMISSION */
if(isset($_POST['submit_testimonial'])){

$name = $_POST['name'];
$message = $_POST['message'];

$sql = "INSERT INTO testimonials(name, message) VALUES('$name','$message')";
mysqli_query($conn, $sql);

echo "<div class='alert alert-success text-center'>Thank you for your feedback!</div>";
}
?>

<link rel="stylesheet" href="/mendnest/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<section class="testimonial-section">

<div class="container">

<!-- TITLE -->
<h2 class="text-center fw-bold mb-5">
What Our Clients Say
</h2>

<!-- TESTIMONIALS -->
<div class="row g-4 mb-5">

<?php
$result = mysqli_query($conn,"SELECT * FROM testimonials ORDER BY id DESC");

while($row = mysqli_fetch_assoc($result)){
?>

<div class="col-md-4">
<div class="card testimonial-card h-100 p-4">

<h5 class="fw-bold">
<?php echo $row['name']; ?>
</h5>

<p class="text-muted">
"<?php echo $row['message']; ?>"
</p>

</div>
</div>

<?php } ?>

</div>

<!-- ADD TESTIMONIAL FORM -->
<div class="testimonial-form-wrapper">

<h4 class="fw-bold mb-3">Leave a Testimonial</h4>

<form method="POST">

<div class="mb-3">
<label>Your Name *</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
<label>Your Experience *</label>
<textarea name="message" class="form-control" rows="4" required></textarea>
</div>

<button type="submit" name="submit_testimonial" class="btn btn-success w-100">
Submit Testimonial
</button>

</form>

</div>

</div>

</section>

<?php include '../includes/footer.php'; ?>