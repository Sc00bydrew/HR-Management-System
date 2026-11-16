<?php
session_start();
require_once "Database.php";
$conn = (new Database())->connect();

$user_id = $_SESSION['user_id'] ?? 1;
$message = "";
$success = false;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username          = trim($_POST['username'] ?? '');
    $email             = trim($_POST['email'] ?? '');
    $first_name        = trim($_POST['first_name'] ?? '');
    $last_name         = trim($_POST['last_name'] ?? '');
    $gender            = trim($_POST['gender'] ?? '');
    $mobile            = trim($_POST['mobile'] ?? '');
    
    // Fix: Convert empty date to null so MySQL doesn't throw a datetime format error
    $date_joined_input = trim($_POST['date_joined'] ?? '');
    $date_joined       = !empty($date_joined_input) ? $date_joined_input : null;

    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $relative_name     = trim($_POST['relative_name'] ?? '');
    $salary            = trim($_POST['salary'] ?? 0.00);
    $bank              = trim($_POST['bank'] ?? '');
    $account_number    = trim($_POST['account_number'] ?? '');

    // Government & Statutory ID inputs
    $sss_number        = trim($_POST['sss_number'] ?? '');
    $philhealth_number = trim($_POST['philhealth_number'] ?? '');
    $tin_number        = trim($_POST['tin_number'] ?? '');

    // Handle avatar upload if provided
    $avatarQueryPart = "";
    $params = [
        $username, $email, $first_name, $last_name, $gender, $mobile, 
        $date_joined, $emergency_contact, $relative_name, $salary, 
        $bank, $account_number, $sss_number, $philhealth_number, $tin_number
    ];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . "_" . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fileName)) {
            $avatarQueryPart = ", avatar = ?";
            $params[] = $fileName;
        }
    }

    $params[] = $user_id;
    $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, gender = ?, mobile = ?, date_joined = ?, emergency_contact = ?, relative_name = ?, salary = ?, bank = ?, account_number = ?, sss_number = ?, philhealth_number = ?, tin_number = ? $avatarQueryPart WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        // Redirect to admin profile view page immediately upon success
        header("Location: admin_profile.php");
        exit();
    } else {
        $message = "Failed to update profile.";
    }
}

// Fetch user data for form population and session header
$isSuperAdmin = false;
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';

try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (!empty($user['last_name'])) {
                $adminLastName = $user['last_name'];
            }

            if (!empty($user['avatar'])) {
                $filename = basename($user['avatar']); 
                $adminAvatar = 'uploads/' . rawurlencode($filename);
            }

            // Check if the user role is superadmin
            if (!empty($user['role']) && (strtolower(trim($user['role'])) === 'superadmin' || strtolower(trim($user['role'])) === 'super_admin')) {
                $isSuperAdmin = true;
            }
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
    <title>Edit HR Admin Profile</title>
    <link rel="stylesheet" href="admin/edit_admin_style.css">
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

        <section class="welcome-row">
            <div>
                <h1>Update <?= $isSuperAdmin ? 'Admin' : 'HR Admin' ?> Profile</h1>
            </div>
        </section>

        <section class="panel employees-panel">
            <?php if ($message): ?>
                <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="grid">
                    <div class="field-group field-full">
                        <?php if (!empty($user['avatar']) && file_exists("uploads/" . $user['avatar'])): ?>
                            <div><img src="uploads/<?= htmlspecialchars($user['avatar']) ?>" class="current-avatar" alt="Avatar"></div>
                        <?php endif; ?>
                        
                        <span id="file-name-display" class="file-name-text"></span>
                        <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="updateFileName(this)">
                        <label for="avatar" class="btn-browse">Browse Files</label>
                    </div>
                    
                    <div class="field-groups">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="field-groups">
                        <label>Date Joined</label>
                        <input type="date" name="date_joined" value="<?= htmlspecialchars($user['date_joined'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Emergency Contact</label>
                        <input type="text" name="emergency_contact" value="<?= htmlspecialchars($user['emergency_contact'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Relative Name</label>
                        <input type="text" name="relative_name" value="<?= htmlspecialchars($user['relative_name'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Monthly Salary</label>
                        <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($user['salary'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Bank Name</label>
                        <input type="text" name="bank" value="<?= htmlspecialchars($user['bank'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>">
                    </div>
                    <div class="field-groups">
                        <label>SSS Number</label>
                        <input type="text" name="sss_number" value="<?= htmlspecialchars($user['sss_number'] ?? '') ?>" placeholder="00-0000000-0">
                    </div>
                    <div class="field-groups">
                        <label>PhilHealth Number</label>
                        <input type="text" name="philhealth_number" value="<?= htmlspecialchars($user['philhealth_number'] ?? '') ?>" placeholder="00-000000000-0">
                    </div>
                    <div class="field-groups">
                        <label>TIN (Tax ID)</label>
                        <input type="text" name="tin_number" value="<?= htmlspecialchars($user['tin_number'] ?? '') ?>" placeholder="000-000-000-000">
                    </div>
                </div>
                <button type="submit" name="update_admin" class="submit-btn">Save Changes</button>
            </form>
        </section>
    </main>

    <script src="admin/edit_admin_script.js"></script>
</body>
</html>
