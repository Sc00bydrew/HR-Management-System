<?php
require_once "leave_request.php";
$leaveObj = new LeaveRequest();

$today = date('Y-m-d');
$employees = $leaveObj->getAllEmployees();
$message = "";
$success = false;

// Handle Leave Status Update (Approve / Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = $_GET['action'] === 'approve' ? 'Approved' : 'Rejected';
    $leaveObj->updateStatus($_GET['id'], $status);
    header("Location: leave_request.php");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $current_date = $_POST['current_date'];
    $employee_id  = $_POST['employee_id'];
    $reason       = trim($_POST['reason']);
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];

    $result = $leaveObj->submit($employee_id, $current_date, $reason, $start_date, $end_date);
    if ($result === true) {
        $success = true;
        $message = "Leave request submitted successfully!";
    } else {
        $message = $result;
    }
}

$requests = $leaveObj->getAllRequests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Leave Request</title>
    <link rel="stylesheet" href="leave/employee_leave_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
</head>
<body>

<header>
    <nav class="nav container">
        <a href="#">LOREM IPSUM</a>

        <div class="nav_menu">
            <ul class="nav_list">
                <li><a href="employees_attendance.php">Attendance</a></li>
                <li><a href="#" class="active">Leave Request</a></li>
                <li><a href="recruitment.php">Recruitment</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="leave-container">
    <div class="leave-header">
        <h1>Leave Request</h1>
        <p>Submit leave requests and track approvals with ease.</p>
    </div>
</div>

    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

<div class="leave-card">
    <div class="card-header">
        <i class="ri-file-edit-line"></i> Application Form
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="clock_in">
        <div class="form-grid">
            <div class="field-group">
                <label>Current Date</label>
                <input type="date" name="current_date" value="<?= htmlspecialchars($today) ?>" required>
            </div>

            <div class="field-group">
                <label>Employee ID</label>
                <select name="employee_id" required onchange="updatePosition(this)">
                    <option value="" disabled selected>Select ID</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" data-position="<?= htmlspecialchars($emp['position']) ?>">
                            EMP-<?= sprintf('%03d', $emp['id']) ?> (<?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                    <label>Position</label>
                    <input type="text" id="position_input" placeholder="--------" readonly>
                </div>

            <div class="field-group full-width">
                <label>Reason</label>
                <textarea name="reason" placeholder="Enter reason for leave..." required></textarea>
            </div>

            <div class="date-row">
                <div class="field-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="field-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" required>
                </div>
            </div>

            <button type="submit" name="submit_leave" class="submit-btn full-width">Submit</button>
        </div>
    </form>
</div>

<script src="leave/employee_leave_script.js"></script>
</body>
</html>