<?php
session_start();

require_once "Database.php";
require_once "Employee.php";

$conn = (new Database())->connect();
$empObj = new Employee();

if (!isset($_GET['id'])) {
    header("Location: employees_list.php");
    exit;
}

$id = $_GET['id'];
$employee = $empObj->getById($id);

if (!$employee) {
    echo "Employee not found.";
    exit;
}

// Default Fallbacks for Admin Profile
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';

// Fetch logged-in user's details safely using a separate variable ($userStmt)
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

        // Check if the user role is superadmin
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
    <title>Lorem Ipsum | Employees Profile</title>
    <link rel="stylesheet" href="employee/view_employees_style.css">
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
        <div class="nav-section">
            <a class="nav-item" href="employees_list.php">
                <i class="ri-group-line"></i><span>Employees</span>
            </a>
            <div class="nav-submenu-static">
                <a class="nav-sub-item active" href="#">Employee Details</a>
                <a class="nav-sub-item" href="edit_employee.php?id=<?= $employee['id'] ?>">Update Profile</a>
            </div>
        </div>
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
        <a class="nav-item" href="payroll.php">
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

        <section class="panel employees-panel">
            <div class="profile-header">
                <?php if (!empty($employee['avatar']) && file_exists("uploads/" . $employee['avatar'])): ?>
                    <img src="uploads/<?= htmlspecialchars($employee['avatar']) ?>" class="profile-avatar" alt="Avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder"><i class="ri-user-3-line"></i></div>
                <?php endif; ?>
                
                <div class="profile-title">
                    <h3><?= htmlspecialchars($employee['position']) ?></h3>
                    <h2><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h2>
                    <p>ID: <?= str_pad($employee['id'], 4, '0', STR_PAD_LEFT) ?></p>
                </div>

                <a href="edit_employee.php?id=<?= $employee['id'] ?>" class="edit-profile-btn">
                    <i class="ri-edit-fill"></i> Edit Profile
                </a>
            </div>

            <div class="section-heading-personal">Personal & Contact Information</div>
            <div class="grid">
                <div class="info-group">
                    <span class="info-label">First name</span>
                    <span class="info-value"><?= htmlspecialchars($employee['first_name'] ?? '--') ?></span>
                </div>

                <div class="info-group">
                    <span class="info-label">Last name</span>
                    <span class="info-value"><?= htmlspecialchars($employee['last_name'] ?? '--') ?></span>
                </div>

                <div class="info-group">
                    <span class="info-label">Email Address</span>
                    <span class="info-value"><?= htmlspecialchars($employee['email']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Mobile Number</span>
                    <span class="info-value"><?= htmlspecialchars($employee['mobile']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Gender</span>
                    <span class="info-value"><?= htmlspecialchars($employee['gender']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Date Joined</span>
                    <span class="info-value"><?= htmlspecialchars($employee['date_joined']) ?></span>
                </div>
            </div>

            <div class="section-heading-emergency">Emergency Contact Details</div>
            <div class="grid">
                <div class="info-group">
                    <span class="info-label">Relative Full Name</span>
                    <span class="info-value"><?= htmlspecialchars($employee['relative_name']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Emergency Mobile Number</span>
                    <span class="info-value"><?= htmlspecialchars($employee['emergency_contact']) ?></span>
                </div>
            </div>

            <div class="section-heading-position">Position & Compensation</div>
            <div class="grid">
                <div class="info-group">
                    <span class="info-label">Salary</span>
                    <span class="info-value">₱<?= number_format($employee['salary'], 2) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Bank</span>
                    <span class="info-value"><?= htmlspecialchars($employee['bank']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Account Number</span>
                    <span class="info-value"><?= htmlspecialchars($employee['account_number']) ?></span>
                </div>
            </div>

            <div class="section-heading-gov">Government & Statutory ID Numbers</div>
            <div class="grid">
                <div class="info-group">
                    <span class="info-label">SSS Number</span>
                    <span class="info-value"><?= htmlspecialchars($employee['sss_number'] ?? '--') ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">PhilHealth Number</span>
                    <span class="info-value"><?= htmlspecialchars($employee['philhealth_number'] ?? '--') ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">TIN (Tax ID)</span>
                    <span class="info-value"><?= htmlspecialchars($employee['tin_number'] ?? '--') ?></span>
                </div>
            </div>
        </section>
    </main>

<script src="employee/view_employees_script.js"></script>
</body>
</html>