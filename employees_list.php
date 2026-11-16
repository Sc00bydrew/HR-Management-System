<?php
session_start();
require_once "Database.php";
require_once "Auth.php";
require_once "Employee.php";

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Initialize Database and Employee object
$conn = (new Database())->connect();
$empObj = new Employee();

// Handle Delete Action
if (isset($_GET['delete'])) {
    $empObj->delete($_GET['delete']);
    header("Location: employees_list.php?deleted=1");
    exit;
}

// Default Fallbacks
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';
$isSuperAdmin = false;

// Fetch logged-in user's details ONCE securely
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT last_name, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
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

            // Check role
            $role = strtolower(trim($user['role'] ?? ''));
            if ($role === 'superadmin' || $role === 'super_admin') {
                $isSuperAdmin = true;
            }
        }
    }
} catch (Exception $e) {
    // Fallbacks remain active if query fails
}

// Handle Position Filter
$selectedPosition = trim($_GET['position'] ?? '');

if (!empty($selectedPosition)) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE position = ? ORDER BY id DESC");
    $stmt->execute([$selectedPosition]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $employees = $empObj->getAll();
}

// Static list of positions for the filter dropdown
$staticPositions = [
    "Fashion Designer",
    "Graphic Designer",
    "Materials Researcher",
    "Fashion Stylist",
    "Product Manager",
    "Sample Maker",
    "Production Manager",
    "Fashion Merchandiser",
    "Sales Clerk",
    "Store Manager",
    "Retail Manager",
    "Customer Service"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Employee List</title>
    <link rel="stylesheet" href="employee/employees_list_style.css">
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
        <a class="nav-item in" href="#">
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

        <section class="welcome-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1>Employees</h1>
            </div>
            
            <div class="employee-header-actions">
                <!-- Custom Position Dropdown Filter Button -->
                <div class="custom-dropdown-container">
                    <button type="button" class="button outline" id="filterDropdownBtn">
                        <i class="ri-equalizer-line"></i>
                        <span><?= htmlspecialchars($selectedPosition ?: 'Filter') ?></span>
                    </button>

                    <div id="filterDropdownMenu" class="dropdown-menu">
                        <a href="employees_list.php" class="dropdown-item <?= empty($selectedPosition) ? 'active' : '' ?>">
                            All Positions
                        </a>
                        <?php foreach ($staticPositions as $posName): ?>
                            <?php $isSelected = ($selectedPosition === $posName); ?>
                            <a href="employees_list.php?position=<?= urlencode($posName) ?>" class="dropdown-item <?= $isSelected ? 'active' : '' ?>">
                                <?= htmlspecialchars($posName) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="button primary">
                    <a href="onboard_employees.php" class="add-employee-btn-link">
                        <i class="ri-user-add-line"></i> Add New Employee
                    </a>
                </button>
            </div>
        </section>

        <section class="panel employees-panel">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Employee record deleted successfully.</div>
            <?php elseif (isset($_GET['updated'])): ?>
                <div class="alert alert-success">Employee record updated successfully.</div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Date Joined</th>
                        <th>Salary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($employees) > 0): ?>
                        <?php foreach ($employees as $row): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['avatar']) && file_exists("uploads/" . $row['avatar'])): ?>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background-color: #F0F2F0; display: inline-block;">
                                            <img src="uploads/<?= htmlspecialchars($row['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background-color: #F0F2F0; display: inline-block;">
                                            <img src="uploads/image.png" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>EMP-<?= sprintf('%04d', $row['id']) ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['mobile']) ?></td>
                                <td style="word-break: break-word; max-width: 180px;"><?= htmlspecialchars($row['email']) ?></td>
                                <td style="word-break: break-word; max-width: 150px;"><?= htmlspecialchars($row['position']) ?></td>
                                <td><?= htmlspecialchars($row['date_joined']) ?></td>
                                <td>₱<?= number_format($row['salary'], 2) ?></td>
                                <td style="white-space: nowrap;">
                                    <a href="view_employees.php?id=<?= $row['id'] ?>" class="action-btn view-btn">
                                        <i class="ri-eye-fill"></i>
                                    </a>
                                    <a href="edit_employee.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                                        <i class="ri-edit-fill"></i>
                                    </a>
                                    <a href="employees_list.php?delete=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">
                                        <i class="ri-delete-bin-5-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

<script src="employee/employees_list_script.js"></script>
</body>
</html>