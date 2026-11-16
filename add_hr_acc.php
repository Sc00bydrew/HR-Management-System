<?php
session_start();
require_once "Database.php";
require_once "Auth.php";
$auth = new Auth();

$conn = (new Database())->connect();
$isSuperAdmin = false;

// Verify if the logged-in user is a superadmin
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($currentUser['role']) && strtolower(trim($currentUser['role'])) === 'superadmin') {
            $isSuperAdmin = true;
        }
    } catch (Exception $e) {
        $isSuperAdmin = false;
    }
}

// // Block access if not a superadmin
// if (!$isSuperAdmin) {
//     header("Location: index.php");
//     exit;
// }

$message = "";
$error = false;

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Passwords do not match!";
        $error = true;
    } else {
        $result = $auth->register($username, $email, $password);

        if ($result === true) {
            header("Location: superadmin_index.php?registered=1");
            exit;
        } else {
            $message = $result; // e.g. "Username already taken"
            $error = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Sign Up</title>

    <link rel="stylesheet" href="register/add_hr_acc_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
</head>
<body>
        <aside class="sidebar" id="sidebar">
        <div class="brand-row">
            <div class="brand">LOREM IPSUM</div>
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                <i class="ri-layout-line"></i>
            </button>
        </div>
        
        <div class="nav-group-title">Main Menu</div>
        <a class="nav-item" href="index.php">
            <i class="ri-home-5-line"></i><span>HR Management</span>
        </a>
        <a class="nav-item" href="#">
            <i class="ri-file-text-line"></i><span>Payroll</span>
        </a>
        <a class="nav-item" href="#">
            <i class="ri-logout-box-line"></i><span>Finance</span>
        </a>
        <a class="nav-item" href="#">
            <i class="ri-briefcase-2-line"></i><span>Manufacturing</span>
        </a>
        <a class="nav-item" href="#">
            <i class="ri-secure-payment-line"></i><span>Supply</span>
        </a>
        <a class="nav-item" href="#">
            <i class="ri-secure-payment-line"></i><span>Selling Page</span>
        </a>
        
        <a class="nav-item logout" href="logout.php">
            <i class="ri-logout-box-r-line"></i><span>Log out</span>
        </a>
    </aside>

    <main class="main">
        <div class="signup-container">
            <div class="signup-header">
                <h1>Create an HR Account</h1>
                <p>Set up your HR Admin account to get started.</p>
            </div>

            <form class="signup-form" method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <input type="email" name="email" placeholder="Enter your email" class="<?= $error ? 'input-error' : '' ?>" required>
                </div>

                <div class="input-group password-group">
                    <input type="password" name="password" id="passwordInput" placeholder="Enter your password" class="<?= $error ? 'input-error' : '' ?>" required>
                    <span id="togglePassword" class="eye-icon" title="Toggle password visibility">
                        <i class="ri-eye-close-line" id="eyeIcon"></i>
                    </span>
                </div>

                <div class="input-group password-group">
                    <input type="password" name="confirm" id="confirmInput" placeholder="Confirm your password" class="<?= $error ? 'input-error' : '' ?>" required>
                    <span id="toggleConfirm" class="eye-icon" title="Toggle password visibility">
                        <i class="ri-eye-close-line" id="confirmEyeIcon"></i>
                    </span>
                </div>

                <button type="submit" name="register" class="signup-btn">
                    Sign Up
                </button>
            </form>
        </div>
    </main>


<script src="register/add_hr_acc_script.js"></script>
</body>
</html>