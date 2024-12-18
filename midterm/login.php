<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="universal.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

<?php
session_start();

// Include database connection
include("db.php"); // Include the db.php file that establishes the connection

if (isset($_SESSION['logout_message'])) {
    echo "<script>alert('{$_SESSION['logout_message']}');</script>";
    unset($_SESSION['logout_message']); // Clear the message after displaying it
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember']); // Check if remember me is selected

    try {
        // Prepare and execute the SQL query
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Check if a user with the given email exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['login_message'] = "Login successful! Welcome, {$user['name']}.";

                // Remember Me: Set cookies if selected
                if ($remember_me) {
                    setcookie("email", $email, time() + (86400 * 30), "/"); // 30-day cookie
                    setcookie("user_id", $user['id'], time() + (86400 * 30), "/"); // 30-day cookie for user ID
                } else {
                    // Clear cookies if "Remember Me" is not checked
                    setcookie("email", "", time() - 3600, "/");
                    setcookie("user_id", "", time() - 3600, "/");
                }

                // Redirect to mainpage.php
                header("Location: mainpage.php");
                exit();
            } else {
                // Incorrect password
                echo "<script>
                        alert('Incorrect password! Please try again.');
                        window.location.href = 'login.php';
                      </script>";
            }
        } else {
            // No user found with this email
            echo "<script>
                    alert('No user found with this email! Please try again.');
                    window.location.href = 'login.php';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: {$e->getMessage()}');</script>";
    }
}
?>

<div class="wrapper">
    <form action="login.php" method="POST">
        <h1><b>Login</b></h1>
        <div class="input-box">
            <input type="email" id="email" placeholder="Email" name="email" 
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
            <i class='bx bx-user'></i>
        </div>
        <div class="input-box">
            <input type="password" id="password" placeholder="Password" name="password" required>
            <i class='bx bx-lock-alt'></i>
        </div>

        <div class="remember-forgot">
            <label for="remember-me">
                <input type="checkbox" id="remember-me" name="remember" value="1"> Remember Me
            </label>
        </div>

        <button type="submit" class="btn">Login</button>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>
</body>

</html>