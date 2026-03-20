<?php
session_start();
include '../database/connection.php';

// ADD BLOG
if(isset($_POST['add'])){
    $title = $_POST['title'];
    $content = $_POST['content'];

    mysqli_query($conn,"INSERT INTO blog (title,content) VALUES ('$title','$content')");
}

// DELETE BLOG
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM blog WHERE id=$id");
}

$result = mysqli_query($conn,"SELECT * FROM blog ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Blog</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<h3>Add Blog Post</h3>

<form method="POST" class="mb-4">
    <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
    <textarea name="content" class="form-control mb-2" placeholder="Content" rows="4" required></textarea>
    <button name="add" class="btn btn-success">Publish</button>
</form>

<h3>All Blog Posts</h3>

<table class="table table-bordered">
<tr>
    <th>Title</th>
    <th>Content</th>
    <th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['title']; ?></td>
    <td><?php echo substr($row['content'],0,100); ?>...</td>
    <td>
        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>