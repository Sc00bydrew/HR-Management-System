<?php
require_once "applicant.php";
$empObj = new Employee();

$message = "";
$success = false;

if (isset($_POST['submit_application'])) {
    $first_name   = $_POST['first_name'];
    $last_name    = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $email        = $_POST['email'];
    $position     = $_POST['position'];

    $result = $empObj->add($first_name, $last_name, $phone_number, $email, $position);

    if ($result === true) {
        $success = true;
        $message = "Application submitted successfully!";
    } else {
        $message = $result; // Shows error message (e.g., email already taken)
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Recruitment Form</title>
    <link rel="stylesheet" href="recruitment/recruitment_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
</head>
<body>

<header>
    <nav class="nav container">
        <a href="#">LOREM IPSUM</a>

        <div class="nav_menu">
            <ul class="nav_list">
                <li><a href="employees_attendance.php">Attendance</a></li>
                <li><a href="employee_leave.php">Leave Request</a></li>
                <li><a href="#" class="active">Recruitment</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="recruitment-container">
    <div class="recruitment-header">
        <h1>Apply Now!</h1>
        <p>Apply today and take the next step in your career!</p>
    </div>
</div>

    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= $message ?></div>
    <?php endif; ?>

<div class="recruitment-card">
    <div class="card-header">
        <i class="ri-briefcase-2-line"></i> Application Form
    </div>
        <form method="POST">
            <input type="hidden" name="action" value="clock_in">
            <div class="form-grid">
                <div class="input-group">
                    <label>First name</label>
                    <input type="text" name="first_name" placeholder="First name" required>
                </div>

                <div class="input-group">
                    <label>Last name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </div>

                <div class="input-group">
                    <label>Mobile</label>
                    <input type="text" name="phone_number" placeholder="Phone Number" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group full-width">
                    <label>Position</label>
                    <select name="position" required>
                        <option value="" disabled selected>Select Position</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Developer">Developer</option>
                        <option value="HR Manager">HR Manager</option>
                        <option value="Sales">Sales</option>
                    </select>
                </div>

                <button type="submit" name="submit_application" class="signup-btn full-width">
                    Submit Application
                </button>
            </div>
        </form>
</div>

<script src="recruitment/recruitment_script.js"></script>
</body>
</html>