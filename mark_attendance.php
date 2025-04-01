<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $date = date('Y-m-d');

    // Check if attendance is already marked for today
    $check_stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
    $check_stmt->execute([$user_id, $date]);
    $already_marked = $check_stmt->fetch();

    if (!$already_marked) {
        try {
            // Insert new attendance record
            $insert_stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
            $result = $insert_stmt->execute([$user_id, $date, 'Present']);
            
            if ($result) {
                $_SESSION['message'] = "Attendance marked successfully!";
            } else {
                $_SESSION['error'] = "Failed to mark attendance.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Attendance already marked for today.";
    }
    
    header("Location: dashboard.php");
    exit();
}
?>
