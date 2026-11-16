<?php
session_start();
require_once "Database.php";
$conn = (new Database())->connect();

// Get logged-in user ID from session (fallback to ID 1 if not set)
$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $user = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Admin Profile</title>
    <link rel="stylesheet" href="sup_profile/superadmin_profile_style.css">
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
        <a class="nav-item active" href="superadmin_index.php">
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
                        <h3 class="role-tag">Admin</h3>
                        <h1><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'User')) ?></h1>
                        <div class="user-id">ID: <?= sprintf('%04d', $user['id'] ?? $user_id) ?></div>
                    </div>
                <a href="edit_superadmin.php" class="edit-profile-btn">
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

    <script src="sup_profile/superadmin_profile_script.js"></script>
</body>
</html>