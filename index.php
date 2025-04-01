<?php
session_start();
require_once 'config/database.php'; // Ensure this file exists and contains your PDO connection code

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user input
    $student_id = trim($_POST['student_id']);
    $password = $_POST['password'];

    // Query the database for the user
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables and redirect to dashboard
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['student_id'] = $user['student_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Student ID or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Management - Login</title>
  <style>
    /* Basic reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #D0E6F1; /* Secondary color */
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-container {
      background-color: #FFFFFF;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 30px 40px;
      width: 350px;
    }
    .login-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .login-container form input[type="text"],
    .login-container form input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border 0.3s;
    }
    .login-container form input[type="text"]:focus,
    .login-container form input[type="password"]:focus {
      border: 1px solid #4A90E2; /* Primary color */
      outline: none;
    }
    .login-container form button {
      width: 100%;
      padding: 10px;
      background-color: #4A90E2; /* Primary color */
      border: none;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
    }
    .login-container form button:hover {
      background-color: #367ac6;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
    .signup-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }
    .signup-link a {
      color: #4A90E2;
      text-decoration: none;
      font-weight: bold;
    }
    .signup-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Student Login</h2>
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <input type="text" name="student_id" placeholder="Student ID" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Log In</button>
    </form>
    <div class="signup-link">
      Don't have an account? <a href="register.php">Sign Up</a>
    </div>
  </div>
</body>
</html>
