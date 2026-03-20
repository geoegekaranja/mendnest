<?php
session_start();
include '../database/connection.php';

// ADD TESTIMONIAL
if(isset($_POST['add'])){
    $name = $_POST['name'];
    $message = $_POST['message'];

    mysqli_query($conn,"INSERT INTO testimonials (name,message) VALUES ('$name','$message')");
}

// DELETE TESTIMONIAL
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM testimonials WHERE id=$id");
}

$result = mysqli_query($conn,"SELECT * FROM testimonials ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Testimonials</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<h3>Add Testimonial</h3>

<form method="POST" class="mb-4">
    <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
    <textarea name="message" class="form-control mb-2" placeholder="Message" required></textarea>
    <button name="add" class="btn btn-success">Add</button>
</form>

<h3>All Testimonials</h3>

<table class="table table-bordered">
<tr>
    <th>Name</th>
    <th>Message</th>
    <th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['message']; ?></td>
    <td>
        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>