<?php include '../includes/header.php'; ?>

<?php
include '../assets/connection.php';
?>

<section class="py-5 bg-light">

<div class="container">
    <link rel="stylesheet" href="/mendnest/css/style.css">

<h2 class="text-center fw-bold mb-5">
What Our Clients Say
</h2>

<div class="row g-4">

<?php

$result = mysqli_query($conn,"SELECT * FROM testimonials");

while($row = mysqli_fetch_assoc($result)){

?>

<div class="col-md-4">

<div class="card shadow-sm border-0 h-100 p-4">

<h5 class="fw-bold text-success">
<?php echo $row['name']; ?>
</h5>

<p class="text-muted">
"<?php echo $row['message']; ?>"
</p>

</div>

</div>

<?php } ?>

</div>

</div>

</section>

<?php include '../includes/footer.php'; ?>