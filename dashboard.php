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

// Check if attendance is marked for today
$date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
$stmt->execute([$user_id, $date]);
$already_marked = $stmt->fetch();

// Fetch all attendance records
$attendance_stmt = $pdo->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC");
$attendance_stmt->execute([$user_id]);
$attendance_records = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate attendance percentage
$total_stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?");
$total_stmt->execute([$user_id]);
$stats = $total_stmt->fetch(PDO::FETCH_ASSOC);
$attendance_percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 2) : 0;

// Get messages from session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages
unset($_SESSION['message']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: #f1f5f9;
            color: #1f2937;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-bg {
            background: linear-gradient(90deg, #3b82f6, #9333ea);
            color: white;
            border-radius: 8px;
        }
        .section-bg {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-table th, .attendance-table td {
            padding: 12px;
            text-align: left;
        }
        .attendance-table th {
            background: #e5e7eb;
            color: #4b5563;
            font-weight: 600;
        }
        .attendance-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .attendance-table tr:hover {
            background: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen py-12 px-6">
    <div class="container fade-in">
        <!-- Header -->
        <div class="header-bg p-6 mb-10 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <form method="POST" action="logout.php">
                <button type="submit" class="btn bg-red-500 text-white hover:bg-red-600">Logout</button>
            </form>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 fade-in">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 fade-in">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- User Info -->
        <div class="section-bg mb-8">
            <h2 class="text-xl font-semibold text-indigo-600 mb-4">Your Profile</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-base">
                <p><span class="font-medium text-gray-700">Student ID:</span> <?php echo htmlspecialchars($user['student_id']); ?></p>
                <p><span class="font-medium text-gray-700">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><span class="font-medium text-gray-700">Joined:</span> <?php echo htmlspecialchars($user['created_at']); ?></p>
                <p><span class="font-medium text-gray-700">Attendance:</span> <span class="text-green-600"><?php echo $attendance_percentage; ?>%</span></p>
            </div>
        </div>

        <!-- Attendance Section -->
        <div class="section-bg mb-8">
            <h2 class="text-xl font-semibold text-indigo-600 mb-4">Mark Attendance</h2>
            <?php if (!$already_marked): ?>
                <form method="POST" action="mark_attendance.php">
                    <button type="submit" name="mark_attendance" class="btn bg-indigo-500 text-white hover:bg-indigo-600">
                        Mark Present
                    </button>
                </form>
            <?php else: ?>
                <p class="text-base text-green-600 font-medium">✓ Attendance Marked for <?php echo $date; ?></p>
            <?php endif; ?>
        </div>

        <!-- Attendance Records -->
        <div class="section-bg">
            <h2 class="text-xl font-semibold text-indigo-600 mb-4">Attendance History</h2>
            <?php if (!empty($attendance_records)): ?>
                <table class="attendance-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                <td class="font-medium <?php echo $record['status'] === 'Present' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo htmlspecialchars($record['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-base text-yellow-600">No attendance records found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>