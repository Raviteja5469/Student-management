<?php
session_start();
require_once 'config/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database using the session user_id
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If no user found, redirect to login page
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle attendance marking
$date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
$stmt->execute([$user['student_id'], $date]);
$already_marked = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance']) && !$already_marked) {
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
    $stmt->execute([$user['student_id'], $date, 'Present']);
    $already_marked = true;
}

// Fetch attendance records
$attendance_stmt = $pdo->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC");
$attendance_stmt->execute([$user['student_id']]);
$attendance_records = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f4f7fc;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .dashboard-container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 800px;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
    <form method="POST" action="register.php"> 
        <button type="submit" class="btn btn-danger" >Logout</button>
      </form>
    </div>
    <p><strong>Student ID:</strong> <?php echo htmlspecialchars($user['student_id']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Registered On:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>

    <hr>
    <h4>Mark Attendance</h4>
    <?php if (!$already_marked): ?>
      <form id="attendanceForm" method="POST">
        <button type="submit" name="mark_attendance" class="btn btn-primary">Mark Attendance</button>
      </form>
      <p id="attendanceStatus" class="mt-2 text-success"></p>
    <?php else: ?>
      <p class="text-success">Attendance already marked for today.</p>
    <?php endif; ?>
    
    <hr>
    <h4>Attendance Records</h4>
<?php if (!empty($attendance_records)): ?>
  <ul class="list-group">
    <?php foreach ($attendance_records as $record): ?>
      <li class="list-group-item">Marked on: <?php echo htmlspecialchars($record['date']); ?> - Status: <?php echo htmlspecialchars($record['status']); ?></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="text-warning">No attendance records found.</p>
<?php endif; ?>

  </div>
  <script>
    $(document).ready(function() {
      $('#attendanceForm').submit(function(e) {
        e.preventDefault();
        $.post('', $(this).serialize(), function() {
          $('#attendanceStatus').text('Attendance marked successfully!');
          setTimeout(() => location.reload(), 1000);
        });
      });
    });
  </script>
</body>
</html>
