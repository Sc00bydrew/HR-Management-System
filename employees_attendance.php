<?php
date_default_timezone_set('Asia/Manila');
session_start(); // Must be at the very top

require_once "attendance.php";
$attObj = new Attendance();

$today = date('Y-m-d');
$employees = $attObj->getAllEmployees();

// 1. Grab the message from the session
$message = $_SESSION['message'] ?? "";
$success = $_SESSION['success'] ?? false;

// 2. Clear it immediately so refreshing makes it go away!
unset($_SESSION['message'], $_SESSION['success']);

// Handle Clock-In Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clock_in') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'] ?? $today;
    
    $result = $attObj->clockIn($employee_id, $date);
    if ($result === true) {
        $success = true;
        $message = "Clock-in recorded successfully for Employee ID #" . htmlspecialchars($employee_id) . "!";
    } else {
        $message = $result;
    }
}

// Handle Clock-Out Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clock_out') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'] ?? $today;
    
    $result = $attObj->clockOut($employee_id, $date);
    if ($result === true) {
        $success = true;
        $message = "Clock-out recorded successfully for Employee ID #" . htmlspecialchars($employee_id) . "!";
    } else {
        $message = $result;
    }
}

$logs = $attObj->getAttendanceLogs($today);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Daily Attendance</title>
    <link rel="stylesheet" href="attendance/employees_attendance_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
</head>
<body>

<header>
    <nav class="nav container">
        <a href="#">LOREM IPSUM</a>

        <div class="nav_menu">
            <ul class="nav_list">
                <li><a href="#" class="active">Attendance</a></li>
                <li><a href="employee_leave.php">Leave Request</a></li>
                <li><a href="recruitment.php">Recruitment</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="attendance-container">
    <div class="attendance-header">
        <h1>Daily Attendance</h1>
        <p>Track your attendance and stay on top of your day.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="attendance-card">
    <div class="card-header">
        <i class="ri-time-line"></i> Clock-in
    </div>
        <form method="POST">
            <input type="hidden" name="action" value="clock_in">
            <div class="form-grid">
                <div class="field-group">
                    <label>Current Date</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($today) ?>" required>
                </div>
                <div class="field-group">
                    <label>Employee ID</label>
                    <select name="employee_id" id="clockin_employee" required onchange="updatePosition(this, 'clockin_position')">
                        <option value="" disabled selected>Employee ID</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" data-position="<?= htmlspecialchars($emp['position']) ?>">
                                EMP-<?= sprintf('%03d', $emp['id']) ?> (<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group">
                    <label>Position</label>
                    <input type="text" id="clockin_position" placeholder="--------" readonly>
                </div>
            </div>
            <button type="submit" class="btn-green">Check-in</button>
        </form>
</div>

<div class="attendance-card">
    <div class="card-header">
        <i class="ri-time-line"></i> Clock-out
    </div>
        <form method="POST">
            <input type="hidden" name="action" value="clock_out">
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="field-group">
                        <label>Current Date</label>
                        <input type="date" name="date" value="<?= htmlspecialchars($today) ?>" required>
                    </div>
                    <div class="field-group">
                        <label>Employee ID</label>
                        <select name="employee_id" required>
                            <option value="" disabled selected>Select ID</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>">
                                    EMP-<?= sprintf('%03d', $emp['id']) ?> (<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <button type="submit" class="btn-red">Check-out</button>
        </form>
</div>

<script src="attendance/employees_attendance_script.js"></script>
</body>
</html>