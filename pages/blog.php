

<?php
 include '../includes/header.php'; 
 include '../database/connection.php';
 ?>

<section class="py-5">
<div class="container">

<h2 class="fw-bold mb-4">Create Blog Post</h2>

<form method="POST" enctype="multipart/form-data" class="blog-form">

<input type="text" name="title" class="form-control mb-3" placeholder="Blog Title" required>

<textarea name="content" class="form-control mb-3" rows="6" placeholder="Write blog content..." required></textarea>

<input type="file" name="image" class="form-control mb-3">

<button name="submit" class="btn btn-success">Publish Blog</button>

</form>

</div>
</section>

<?php include '../includes/footer.php'; ?>