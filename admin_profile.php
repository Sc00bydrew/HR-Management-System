<?php
session_start();
require_once "Database.php";
$conn = (new Database())->connect();

// Get logged-in user ID from session (fallback to ID 1 if not set)
$user_id = $_SESSION['user_id'] ?? 1;

$user = [];
$isSuperAdmin = false;
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';

try {
    // Fetch ALL columns for the logged-in user in one go
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Set admin last name if available
        if (!empty($user['last_name'])) {
            $adminLastName = $user['last_name'];
        }

        // Format avatar path safely
        if (!empty($user['avatar'])) {
            $filename = basename($user['avatar']); 
            $adminAvatar = 'uploads/' . rawurlencode($filename);
        }

        // Check if user role is superadmin
        if (!empty($user['role']) && strtolower(trim($user['role'])) === 'super_admin') {
            $isSuperAdmin = true;
        }
    } else {
        $user = [];
    }
} catch (Exception $e) {
    // Fallback values remain active if query fails
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | HR Admin Profile</title>
    <link rel="stylesheet" href="admin/admin_profile_style.css">
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
            </div>
        </header>

        <section class="panel employees-panel">
            <div class="profile-header">
                    <?php if (!empty($user['avatar']) && file_exists("uploads/" . $user['avatar'])): ?>
                        <img src="uploads/<?= htmlspecialchars($user['avatar']) ?>" class="profile-avatar" alt="Avatar">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder"><i class="ri-user-3-line"></i></div>
                    <?php endif; ?>
                    
                    <div class="profile-title">
                        <h3 class="role-tag"><?= $isSuperAdmin ? 'Admin' : 'HR Admin' ?></h3>
                        <h1><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'User')) ?></h1>
                        <div class="user-id">ID: <?= sprintf('%04d', $user['id'] ?? $user_id) ?></div>
                    </div>
                <a href="edit_admin.php" class="edit-profile-btn">
                    <i class="ri-edit-fill"></i> Edit Profile
                </a>
            </div>

            <div class="section-title-blue">Personal & Contact Information</div>
            <div class="info-grid">
                <div class="info-item"><label>First name</label><div class="value"><?= htmlspecialchars($user['first_name'] ?? '--') ?></div></div>
                <div class="info-item"><label>Last name</label><div class="value"><?= htmlspecialchars($user['last_name'] ?? '--') ?></div></div>
                <div class="info-item"><label>Gender</label><div class="value"><?= htmlspecialchars($user['gender'] ?? '--') ?></div></div>
                <div class="info-item"><label>Email</label><div class="value"><?= htmlspecialchars($user['email'] ?? '--') ?></div></div>
                <div class="info-item"><label>Mobile</label><div class="value"><?= htmlspecialchars($user['mobile'] ?? '--') ?></div></div>
                <div class="info-item"><label>Date Joined</label><div class="value"><?= htmlspecialchars($user['date_joined'] ?? '--') ?></div></div>
            </div>

            <div class="section-title-red">Emergency Contact</div>
            <div class="info-grid">
                <div class="info-item"><label>Emergency Contact</label><div class="value"><?= htmlspecialchars($user['emergency_contact'] ?? '--') ?></div></div>
                <div class="info-item"><label>Relative Name</label><div class="value"><?= htmlspecialchars($user['relative_name'] ?? '--') ?></div></div>
                <div></div>
            </div>

            <div class="section-title-green">Position & Compensation</div>
            <div class="info-grid">
                <div class="info-item"><label>Salary</label><div class="value">₱ <?= number_format($user['salary'] ?? 0, 2) ?></div></div>
                <div class="info-item"><label>Bank</label><div class="value"><?= htmlspecialchars($user['bank'] ?? '--') ?></div></div>
                <div class="info-item"><label>Account Number</label><div class="value"><?= htmlspecialchars($user['account_number'] ?? '--') ?></div></div>
            </div>
            
            <div class="section-title-orange">Government & Statutory ID Numbers</div>
            <div class="info-grid">
                <div class="info-item"><label>SSS Number</label><div class="value"><?= htmlspecialchars($user['sss_number'] ?? '--') ?></div></div>
                <div class="info-item"><label>PhilHealth Number</label><div class="value"><?= htmlspecialchars($user['philhealth_number'] ?? '--') ?></div></div>
                <div class="info-item"><label>TIN (Tax ID)</label><div class="value"><?= htmlspecialchars($user['tin_number'] ?? '--') ?></div></div>
            </div>
        </section>
    </main>

    <script src="admin/admin_profile_script.js"></script>
</body>
</html>