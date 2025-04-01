<?php
session_start();
require_once 'config/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];

// Check if attendance is already marked for today
$date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
$stmt->execute([$user_id, $date]);
$attendance_today = $stmt->fetch(PDO::FETCH_ASSOC);

// If the form is submitted and attendance is not already marked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$attendance_today) {
    $status = 'present'; // Set attendance status to 'present' by default

    // Insert new attendance record
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $date, $status]);

    $message = "Attendance marked successfully for today!";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = "You have already marked your attendance for today.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mark Attendance</title>
  <style>
    /* Basic reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #EEF2F7;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      min-height: 100vh;
    }
    .attendance-container {
      background-color: #FFFFFF;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 500px;
      padding: 30px;
      text-align: center;
    }
    .attendance-container h2 {
      color: #333;
      margin-bottom: 20px;
    }
    .attendance-message {
      margin-bottom: 20px;
      font-size: 16px;
      color: green;
    }
    button {
      padding: 12px 20px;
      background-color: #4A90E2;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #367ac6;
    }
  </style>
</head>
<body>
  <div class="attendance-container">
    <h2>Mark Attendance</h2>

    <?php if (isset($message)): ?>
      <div class="attendance-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!$attendance_today): ?>
      <form method="POST" action="">
        <button type="submit">Mark Attendance</button>
      </form>
    <?php else: ?>
      <p>You have already marked your attendance for today.</p>
    <?php endif; ?>
  </div>
</body>
</html>
