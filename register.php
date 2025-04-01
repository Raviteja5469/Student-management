<?php
session_start();
require_once 'config/database.php'; // Ensure this file exists and holds your PDO connection

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $name = trim($_POST['name']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input fields
    if (empty($name) || empty($student_id) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required!";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address!";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Check if student_id or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? OR email = ?");
        $stmt->execute([$student_id, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "An account with that Student ID or Email already exists!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Insert new student record
            $stmt = $pdo->prepare("INSERT INTO students (student_id, name, email, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$student_id, $name, $email, $hashedPassword])) {
                $success = "Registration successful! You can now log in.";
                // Optionally, redirect to login page after registration:
                // header("Location: login.php");
                // exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Management - Sign Up</title>
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
      padding: 20px;
    }
    .signup-container {
      background-color: #FFFFFF;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 30px 40px;
      width: 400px;
    }
    .signup-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .signup-container form input[type="text"],
    .signup-container form input[type="email"],
    .signup-container form input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border 0.3s;
    }
    .signup-container form input[type="text"]:focus,
    .signup-container form input[type="email"]:focus,
    .signup-container form input[type="password"]:focus {
      border: 1px solid #4A90E2; /* Primary color */
      outline: none;
    }
    .signup-container form button {
      width: 100%;
      padding: 10px;
      background-color: #4A90E2; /* Primary color */
      border: none;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
      margin-top: 10px;
    }
    .signup-container form button:hover {
      background-color: #367ac6;
    }
    .message {
      text-align: center;
      margin-bottom: 15px;
      padding: 8px;
      border-radius: 4px;
    }
    .error {
      background-color: #f8d7da;
      color: #a94442;
      margin-bottom: 10px;
    }
    .success {
      background-color: #d4edda;
      color: #155724;
      margin-bottom: 10px;
    }
    .login-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }
    .login-link a {
      color: #4A90E2;
      text-decoration: none;
      font-weight: bold;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2>Create an Account</h2>
    <?php if (!empty($errors)): ?>
      <div class="message error">
        <?php 
          foreach ($errors as $error) {
              echo htmlspecialchars($error) . "<br>";
          }
        ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="message success">
          <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
      <input type="text" name="student_id" placeholder="Student ID" value="<?php echo isset($student_id) ? htmlspecialchars($student_id) : ''; ?>" required>
      <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Sign Up</button>
    </form>
    <div class="login-link">
      Already have an account? <a href="index.php">Login</a>
    </div>
  </div>
</body>
</html>
