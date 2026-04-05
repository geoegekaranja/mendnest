<?php 
session_start();
include '../database/connection.php';

/* PROTECT PAGE */
if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

/* SUBMIT BLOG */
if(isset($_POST['submit'])){

    $title = $_POST['title'];
    $content = $_POST['content'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp, "../uploads/".$image);

    mysqli_query($conn,"INSERT INTO blogs(title,content,image)
    VALUES('$title','$content','$image')");
}
?>

<?php include '../includes/header.php'; ?>

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