<?php
session_start();
include 'database/connection.php';

$error = "";
$success = "";

// REGISTER
if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = mysqli_query($conn,"SELECT * FROM patients WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $error = "Email already exists!";
    } else {
        mysqli_query($conn,"INSERT INTO patients (name,email,password)
                            VALUES ('$name','$email','$password')");
        $success = "Account created! You can now login.";
    }
}

// LOGIN
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM patients WHERE email=? AND password=?");
    $stmt->bind_param("ss",$email,$password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $_SESSION['patient'] = $email;
        header("Location: patient/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login / Register</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#198754,#20c997);
    height: 100vh;
}

.auth-box {
    max-width: 400px;
    margin: 80px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.toggle-btn {
    cursor: pointer;
    color: #198754;
}
</style>
</head>

<body>

<div class="auth-box">

<h3 class="text-center mb-3" id="form-title">Login</h3>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if($success){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<!-- LOGIN FORM -->
<form method="POST" id="loginForm">
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button name="login" class="btn btn-success w-100">Login</button>
</form>

<!-- REGISTER FORM -->
<form method="POST" id="registerForm" style="display:none;">
    <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
    <button name="register" class="btn btn-primary w-100">Register</button>
</form>

<p class="text-center mt-3">
    <span id="toggleText">Don't have an account?</span>
    <span class="toggle-btn" onclick="toggleForm()"> Register</span>
</p>

</div>

<script>
function toggleForm(){
    let login = document.getElementById("loginForm");
    let register = document.getElementById("registerForm");
    let title = document.getElementById("form-title");
    let text = document.getElementById("toggleText");

    if(login.style.display === "none"){
        login.style.display = "block";
        register.style.display = "none";
        title.innerText = "Login";
        text.innerText = "Don't have an account?";
    } else {
        login.style.display = "none";
        register.style.display = "block";
        title.innerText = "Register";
        text.innerText = "Already have an account?";
    }
}
</script>

</body>
</html>