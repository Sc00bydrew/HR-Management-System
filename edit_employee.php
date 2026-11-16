<?php
session_start();

require_once "Database.php";
require_once "Employee.php";
$conn = (new Database())->connect();
$empObj = new Employee();

$message = "";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    $first_name        = trim($_POST['first_name']);
    $last_name         = trim($_POST['last_name']);
    $mobile            = trim($_POST['mobile']);
    $email             = trim($_POST['email']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $relative_name     = trim($_POST['relative_name']);
    $gender            = trim($_POST['gender']);
    $date_joined       = trim($_POST['date_joined']);
    $position          = trim($_POST['position']);
    $salary            = str_replace(',', '', trim($_POST['salary'])); // Clean comma formatting if any
    $bank              = trim($_POST['bank']);
    $account_number    = trim($_POST['account_number']);

    // Government / Statutory ID inputs
    $sss_number        = trim($_POST['sss_number'] ?? '');
    $philhealth_number = trim($_POST['philhealth_number'] ?? '');
    $tin_number        = trim($_POST['tin_number'] ?? '');

    $avatar_file       = $_FILES['avatar'] ?? null;

    $result = $empObj->update(
        $id, $first_name, $last_name, $mobile, $email, 
        $emergency_contact, $relative_name, $gender, 
        $date_joined, $position, $salary, $bank, $account_number, 
        $sss_number, $philhealth_number, $tin_number, 
        $avatar_file
    );

    if ($result === true) {
        header("Location: employees_list.php?updated=1");
        exit;
    } else {
        $message = $result;
    }
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
    <title>Lorem Ipsum | Update Profile</title>
    <link rel="stylesheet" href="employee/edit_employee_style.css">
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
                <a class="nav-sub-item" href="view_employees.php?id=<?= $employee['id'] ?>">Employee Details</a>
                <a class="nav-sub-item active" href="#">Update Profile</a>
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

        <section class="welcome-row">
            <div>
                <h1>Update Employee Profile</h1>
            </div>
        </section>

        <section class="panel employees-panel">
            <?php if ($message): ?>
                <div class="alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="grid">
                    <div class="field-group field-full">
                        <?php if (!empty($employee['avatar']) && file_exists("uploads/" . $employee['avatar'])): ?>
                            <div><img src="uploads/<?= htmlspecialchars($employee['avatar']) ?>" class="current-avatar" alt="Avatar"></div>
                        <?php endif; ?>
                        
                        <span id="file-name-display" class="file-name-text"></span>
                        <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="updateFileName(this)">
                        <label for="avatar" class="btn-browse">Browse Files</label>
                    </div>

                    <div class="field-groups">
                        <label>First name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Last name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Mobile</label>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($employee['mobile']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Emergency</label>
                        <input type="text" name="emergency_contact" value="<?= htmlspecialchars($employee['emergency_contact']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Relative Full name</label>
                        <input type="text" name="relative_name" value="<?= htmlspecialchars($employee['relative_name']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="Male" <?= $employee['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $employee['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $employee['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="field-groups">
                        <label>Date Joined</label>
                        <input type="date" name="date_joined" value="<?= htmlspecialchars($employee['date_joined']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Position</label>
                        <select name="position" required>
                            <!-- <option value="Marketing" <?= $employee['position'] === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                            <option value="Developer" <?= $employee['position'] === 'Developer' ? 'selected' : '' ?>>Developer</option>
                            <option value="HR Manager" <?= $employee['position'] === 'HR Manager' ? 'selected' : '' ?>>HR Manager</option>
                            <option value="Sales" <?= $employee['position'] === 'Sales' ? 'selected' : '' ?>>Sales</option> -->
                            <option value="" disabled <?= empty($employee['position']) ? 'selected' : '' ?>>Select Position</option>
                            <option value="Fashion Designer" <?= ($employee['position'] ?? '') === 'Fashion Designer' ? 'selected' : '' ?>>Fashion Designer</option>
                            <option value="Graphic Designer" <?= ($employee['position'] ?? '') === 'Graphic Designer' ? 'selected' : '' ?>>Graphic Designer</option>
                            <option value="Materials Researcher" <?= ($employee['position'] ?? '') === 'Materials Researcher' ? 'selected' : '' ?>>Materials Researcher</option>
                            <option value="Fashion Stylist" <?= ($employee['position'] ?? '') === 'Fashion Stylist' ? 'selected' : '' ?>>Fashion Stylist</option>
                            <option value="Product Manager" <?= ($employee['position'] ?? '') === 'Product Manager' ? 'selected' : '' ?>>Product Manager</option>
                            <option value="Sample Maker" <?= ($employee['position'] ?? '') === 'Sample Maker' ? 'selected' : '' ?>>Sample Maker</option>
                            <option value="Production Manager" <?= ($employee['position'] ?? '') === 'Production Manager' ? 'selected' : '' ?>>Production Manager</option>
                            <option value="Fashion Merchandiser" <?= ($employee['position'] ?? '') === 'Fashion Merchandiser' ? 'selected' : '' ?>>Fashion Merchandiser</option>
                            <option value="Sales Clerk" <?= ($employee['position'] ?? '') === 'Sales Clerk' ? 'selected' : '' ?>>Sales Clerk</option>
                            <option value="Store Manager" <?= ($employee['position'] ?? '') === 'Store Manager' ? 'selected' : '' ?>>Store Manager</option>
                            <option value="Retail Manager" <?= ($employee['position'] ?? '') === 'Retail Manager' ? 'selected' : '' ?>>Retail Manager</option>
                            <option value="Customer Service" <?= ($employee['position'] ?? '') === 'Customer Service' ? 'selected' : '' ?>>Customer Service</option>
                        </select>
                        </select>
                    </div>
                    <div class="field-groups">
                        <label>Salary</label>
                        <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($employee['salary']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Bank</label>
                        <input type="text" name="bank" value="<?= htmlspecialchars($employee['bank']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="<?= htmlspecialchars($employee['account_number']) ?>" required>
                    </div>
                    <div class="field-groups">
                        <label>SSS Number</label>
                        <input type="text" name="sss_number" value="<?= htmlspecialchars($employee['sss_number']) ?>" placeholder="00-0000000-0" required>
                    </div>
                    <div class="field-groups">
                        <label>PhilHealt Number</label>
                        <input type="text" name="philhealth_number" value="<?= htmlspecialchars($employee['philhealth_number']) ?>" placeholder="00-000000000-0" required>
                    </div>
                    <div class="field-groups">
                        <label>TIN (Tax ID)</label>
                        <input type="text" name="tin_number" value="<?= htmlspecialchars($employee['tin_number']) ?>" placeholder="000-000-000-000" required>
                    </div>
                </div>

                <button type="submit" name="update_employee" class="submit-btn">Update Employee</button>
            </form>
        </section>
    </main>

    <script src="employee/edit_employee_script.js"></script>
</body>
</html>