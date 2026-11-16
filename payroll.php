<?php
session_start();
require_once "Database.php";
$conn = (new Database())->connect();

$message = "";
$success = false;

// Ensure uploads folder exists
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle CSV File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payroll_file'])) {
    $file = $_FILES['payroll_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($fileExt === 'csv') {
            $originalName = basename($file['name']);
            // Add timestamp prefix to avoid name collisions
            $safeFileName = time() . "_" . $originalName;
            $destination = $uploadDir . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Save the safe file name to the database so it matches the disk file
                $stmt = $conn->prepare("INSERT INTO payroll_files (file_name) VALUES (?)");
                $stmt->execute([$safeFileName]);

                $success = true;
                $message = "Payroll report file uploaded successfully!";
            } else {
                $message = "Failed to save the uploaded file.";
            }
        } else {
            $message = "Invalid file type. Please upload a valid .csv file.";
        }
    } else {
        $message = "Please select a valid payroll report file to upload.";
    }
}

// Fetch uploaded file records from the database
$uploadedFiles = $conn->query("SELECT * FROM payroll_files ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Default Fallbacks for Admin Profile
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';

// Fetch logged-in user's details safely
try {
    if (isset($_SESSION['user_id'])) {
        $userStmt = $conn->prepare("SELECT last_name, avatar FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (!empty($user['last_name'])) {
                $adminLastName = $user['last_name'];
            }

            if (!empty($user['avatar'])) {
                $filename = basename($user['avatar']); 
                $filePath = 'uploads/' . $filename;
                if (file_exists($filePath)) {
                    $adminAvatar = 'uploads/' . rawurlencode($filename);
                }
            }
        }
    }
} catch (Exception $e) {
    $adminAvatar = 'uploads/default-avatar.png';
}

$isSuperAdmin = false;

try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT last_name, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($user['last_name'])) {
            $adminLastName = $user['last_name'];
        }

        if (!empty($user['avatar'])) {
            $filename = basename($user['avatar']); 
            $adminAvatar = 'uploads/' . rawurlencode($filename);
        }

        if (!empty($user['role']) && strtolower(trim($user['role'])) === 'super_admin') {
            $isSuperAdmin = true;
        }
    }
} catch (Exception $e) {
    $adminLastName = 'Admin';
    $adminAvatar = 'uploads/default-avatar.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Payroll Report</title>
    <link rel="stylesheet" href="payroll/payroll_style.css">
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
        
        <?php if ($isSuperAdmin): ?>
            <div class="nav-group-title">System Administration</div>
            <a class="nav-item actives" href="superadmin_index.php">
                <i class="ri-shield-user-line"></i><span>Admin Dashboard</span>
            </a>
        <?php endif; ?>

        <div class="nav-group-title">Main Menu</div>
        <a class="nav-item active" href="index.php">
            <i class="ri-home-5-line"></i><span>Dashboard</span>
        </a>
        
        <div class="nav-group-title">HR Management</div>
        <a class="nav-item" href="employees_list.php">
            <i class="ri-group-line"></i><span>Employees</span>
        </a>
        <a class="nav-item" href="attendance_list.php">
            <i class="ri-file-text-line"></i><span>Attendance</span>
        </a>
        <a class="nav-item margin" href="leave_management.php">
            <i class="ri-logout-box-line"></i><span>Leave Request</span>
        </a>
        
        <div class="nav-group-title">Analytics &amp; Reports</div>
        <a class="nav-item" href="dashboard_recruitment.php">
            <i class="ri-briefcase-2-line"></i><span>Recruitment</span>
        </a>
        <a class="nav-item in" href="#">
            <i class="ri-secure-payment-line"></i><span>Payroll</span>
        </a>
        
        <a class="nav-item logout" href="logout.php">
            <i class="ri-logout-box-r-line"></i><span>Log out</span>
        </a>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="top-actions">
                <a href="admin_profile.php" class="profile" aria-label="Profile">
                    <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="Admin Profile">
                </a>
            </div>
        </header>

        <section class="welcome-row">
            <div>
                <h1>Payroll Report</h1>
            </div>
        </section>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <section class="panel employees-panel">
            <form method="POST" enctype="multipart/form-data">
                <div class="upload-card-box">
                    <div class="upload-icon">
                        <i class="ri-upload-2-line"></i>
                    </div>
                    <div class="upload-text">Upload your Payroll Report</div>
                    <div class="upload-subtext">or</div>
                    
                    <input type="file" name="payroll_file" id="payroll_file" accept=".csv" style="display: none;" onchange="this.form.submit()">
                    <label for="payroll_file" class="btn-browse">Browse Files</label>
                </div>
            </form>
        </section>

        <section class="payroll-row">
            <h3>
                Payroll History File
            </h3>
        </section>

        <section class="panel">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Uploaded File Name</th>
                        <th>Upload Date & Time</th>
                        <!-- <th>Action</th> -->
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($uploadedFiles)): ?>
                        <?php foreach ($uploadedFiles as $file): ?>
                            <?php 
                                // Support both direct uploads in 'uploads/' and auto-synced attendance files in 'uploads/payroll_files/'
                                $fileName = $file['file_name'];
                                $filePath = file_exists("uploads/payroll_files/" . $fileName) 
                                    ? "uploads/payroll_files/" . $fileName 
                                    : "uploads/" . $fileName;
                            ?>
                            <tr>
                                <td>#<?= sprintf('%04d', $file['id']) ?></td>
                                <td>
                                    <span class="file-name-cell">
                                        <?= htmlspecialchars($fileName) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($file['uploaded_at']) ?></td>
                                <!-- <td>
                                    <a href="<?= htmlspecialchars($filePath) ?>" class="button secondary" download style="padding: 6px 14px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: 8px;">
                                        <i class="ri-download-2-line"></i> Download
                                    </a>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #9ca3af; padding: 25px;">No payroll files uploaded yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script src="payroll/payroll_script.js"></script>
</body>
</html>