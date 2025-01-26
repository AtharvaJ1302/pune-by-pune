<?php
session_start();
include('connection.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $result = $conn ->query("SELECT * FROM admins WHERE username = '$username' AND password = '$password'");
    if($result -> num_rows == 1){
        $_SESSION ['username'] = $username;
        header('Location: admin_dashboard.php' ); 
}else{
    echo "Invalid username or password";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
    <h1>Admin Login</h1>
    
    <form action="" method="post">
        <input type="text" name="username" placeholder="Enter Username"><br>
        <input type="password" name="password" placeholder="Enter Password"><br>
        <button>Submit</button>
    </form>
</body>
</html>