<?php
session_start();

$error="";

if(isset($_POST['login'])){

$user=$_POST['username'];
$pass=$_POST['password'];

if($user=="admin" && $pass=="admin123"){

$_SESSION['admin']=$user;
header("Location: dashboard.php");
exit();

}else{
$error="Invalid username or password";
}

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Login | Mend Nest KE</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.login-card{
width:400px;
border:none;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
border-radius:10px;
}

.brand{
font-size:24px;
font-weight:bold;
color:#28a745;
text-align:center;
}

</style>

</head>

<body>

<div class="card login-card p-4">

<div class="brand mb-3">
Mend Nest KE
</div>

<h4 class="text-center mb-4">Admin Login</h4>

<?php if($error!=""){ ?>

<div class="alert alert-danger">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-success w-100" name="login">
Login
</button>

</form>

</div>

</body>
</html>