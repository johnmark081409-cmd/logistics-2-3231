<?php
session_start();
include 'db.php';

if(isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = $_POST['pass'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    if(mysqli_num_rows($res) > 0) {
        $_SESSION['auth'] = $user;
        header("Location: home.php");
    } else { $error = "Invalid Username or Password"; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Logistics 2 Login</title>
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#004085;">
    <div class="card" style="width:350px; text-align:center;">
        <h2 style="color:#004085;">Logistics 2</h2>
        <form method="POST">
            <input type="text" name="user" placeholder="Username" required style="width:90%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;"><br>
            <input type="password" name="pass" placeholder="Password" required style="width:90%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ccc;"><br>
            <button type="submit" name="login" class="btn" style="width:100%;">Login</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>
</body>
</html>