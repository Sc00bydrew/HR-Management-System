<?php
session_start();
require_once "Database.php";
require_once "Employee.php";

$conn = (new Database())->connect();
$empObj = new Employee();
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['onboard_employee'])) {
    $first_name         = trim($_POST['first_name']);
    $last_name          = trim($_POST['last_name']);
    $phone_number       = trim($_POST['mobile']);
    $email              = trim($_POST['email']);
    $emergency_contact  = trim($_POST['emergency_contact']);
    $relative_name      = trim($_POST['relative_name']);
    $gender             = trim($_POST['gender']);
    $date_joined        = trim($_POST['date_joined']);
    $position           = trim($_POST['position']);
    
    // Remove comma formatting from salary before saving to database
    $salary             = str_replace(',', '', trim($_POST['salary']));
    
    $bank               = trim($_POST['bank']);
    $account_number     = trim($_POST['account_number']);

    // New Philippine Government / Statutory ID inputs
    $sss_number         = trim($_POST['sss_number'] ?? '');
    $philhealth_number  = trim($_POST['philhealth_number'] ?? '');
    $tin_number         = trim($_POST['tin_number'] ?? '');

    $avatar_file        = $_FILES['avatar'] ?? null;

    $result = $empObj->add(
        $first_name, $last_name, $phone_number, $email, 
        $emergency_contact, $relative_name, $gender, 
        $date_joined, $position, $salary, $bank, $account_number, 
        $sss_number, $philhealth_number, $tin_number, 
        $avatar_file
    );

    if ($result === true) {
        // Redirect to employee list upon successful save
        header("Location: employees_list.php");
        exit();
    } else {
        $message = $result;
    }
}

// Default Fallbacks for Admin Profile
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';

// Fetch logged-in user's details safely using $conn
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
    <title>Lorem Ipsum | Onboard Employees</title>
    <link rel="stylesheet" href="onboard/onboard_employees_style.css">
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
        <a class="nav-item in" href="employees_list.php">
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
                <a href="admin_profile.php" class="profile" aria-label="Profile">
                    <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="Admin Profile">
                </a>
            </div>
        </header>

        <section class="welcome-row">
            <div>
                <h1>Onboard Employees</h1>
            </div>
        </section>

        <section class="panel employees-panel">
            <div class="section-title">
                <i class="ri-id-card-line"></i> Personal &amp; Official Details
            </div>

            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="grid">
                    <div class="field-group field-full">
                        <label class="label">Employee Avatar / Picture</label>
                        <div class="upload-card-box">
                            <div class="upload-icon">
                                <i class="ri-upload-2-line"></i>
                            </div>
                            <div class="upload-text">Upload your Employee Profile</div>
                            <div class="upload-subtext" id="file-name-display">or</div>
                            
                            <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="updateFileName(this)">
                            <label for="avatar" class="btn-browse">Browse Files</label>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="label">First name</label>
                        <input type="text" name="first_name" placeholder="John" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Last name</label>
                        <input type="text" name="last_name" placeholder="Doe" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Mobile</label>
                        <input type="text" name="mobile" placeholder="0912 345 6789" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Email</label>
                        <input type="email" name="email" placeholder="example@gmail.com" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Emergency</label>
                        <input type="text" name="emergency_contact" placeholder="Relative Mobile Number" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Relative Full name</label>
                        <input type="text" name="relative_name" placeholder="John Doe" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Gender</label>
                        <select name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="label">Date Joined</label>
                        <input type="date" name="date_joined" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Position</label>
                        <select name="position" required>
                            <option value="" disabled selected>Select Position</option>
                            <option value="Fashion Designer">Fashion Designer</option>
                            <option value="Graphic Designer">Graphic Designer</option>
                            <option value="Materials Researcher">Materials Researcher</option>
                            <option value="Fashion Stylist">Fashion Stylist</option>
                            <option value="Product Manager">Product Manager</option>
                            <option value="Sample Maker">Sample Maker</option>
                            <option value="Production Manager">Production Manager</option>
                            <option value="Fashion Merchandiser">Fashion Merchandiser</option>
                            <option value="Sales Clerk">Sales Clerk</option>
                            <option value="Store Manager">Store Manager</option>
                            <option value="Retail Manager">Retail Manager</option>
                            <option value="Customer Service">Customer Service</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="label">Salary</label>
                        <input type="text" name="salary" id="salary-input" placeholder="000,000" autocomplete="off" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Bank</label>
                        <input type="text" name="bank" placeholder="BDO Bank" required>
                    </div>
                    <div class="field-group">
                        <label class="label">Account Number</label>
                        <input type="text" name="account_number" placeholder="0123456789" required>
                    </div>
                    <div class="field-group">
                        <label class="label">SSS Number</label>
                        <input type="text" name="sss_number" placeholder="0123456789" required>
                    </div>
                    <div class="field-group">
                        <label class="label">PhilHealth Number</label>
                        <input type="text" name="philhealth_number" placeholder="0123456789" required>
                    </div>
                    <div class="field-group">
                        <label class="label">TIN (Tax ID)</label>
                        <input type="text" name="tin_number" placeholder="0123456789" required>
                    </div>
                </div>

                <button type="submit" name="onboard_employee" class="submit-btn">Save Employee Records</button>
            </form>
        </section>
    </main>

    <script src="onboard/onboard_employees_script.js"></script>
</body>
</html>