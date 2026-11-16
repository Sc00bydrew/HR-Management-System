<?php
session_start();
require_once "Database.php";
require_once "Auth.php";
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = (new Database())->connect();

// 1. Total Employees
$totalEmployees = 0;
try {
    $totalEmployees = $conn->query("SELECT COUNT(*) FROM employees")->fetchColumn() ?: 0;
} catch (Exception $e) { $totalEmployees = 0; }

// 2. Active Employees
$activeEmployees = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'");
    $activeEmployees = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) { $activeEmployees = 0; }

// 3. Employees on Leave
// Employees on Leave query
$onLeaveEmployees = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM employees WHERE LOWER(status) LIKE '%leave%'");
    $onLeaveEmployees = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    $onLeaveEmployees = 0; 
}

// 4. Pending Leave Requests
$pendingLeaves = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'");
    $pendingLeaves = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) { $pendingLeaves = 0; }

// 5. Pending Applications (Checks applications table or defaults to 0)
$pendingApplications = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM applicant WHERE status = 'Pending' OR status = 'pending'");
    $pendingApplications = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    $pendingApplications = 0; 
}

// 6. Weekly Attendance Chart Data
// Maps day of week (1=Sun, 2=Mon, ..., 7=Sat) to attendance counts
$weeklyAttendance = [];
try {
    $attendanceStmt = $conn->query("
        SELECT DAYOFWEEK(date) as day_num, COUNT(DISTINCT employee_id) as total 
        FROM attendance 
        WHERE YEARWEEK(date, 1) = YEARWEEK(NOW(), 1)
        GROUP BY DAYOFWEEK(date)
    ");
    while ($row = $attendanceStmt->fetch(PDO::FETCH_ASSOC)) {
        $weeklyAttendance[$row['day_num']] = $row['total'];
    }
} catch (Exception $e) {
    // Fallback if table structure varies
    $weeklyAttendance = [];
}

// Helper function to compute bar heights safely relative to total employees
function getBarHeight($dayIndex, $attendanceData, $totalEmps) {
    if (!isset($attendanceData[$dayIndex]) || $totalEmps <= 0) {
        return 15; // baseline minimum height
    }
    $count = $attendanceData[$dayIndex];
    $percentage = ($count / max($totalEmps, 1)) * 100;
    return min(max($percentage, 15), 100);
}

// 7. Recent Employees for Bottom Table
$employees = [];
try {
    $stmt = $conn->query("SELECT * FROM employees ORDER BY id DESC LIMIT 10");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $employees = [];
}


// Fetch logged-in user's avatar
$adminAvatar = 'uploads/default-avatar.png'; // Fallback image with 'uploads'
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($user['avatar'])) {
            $filename = basename($user['avatar']); 
            // Prepend 'uploads/' with an 's' and rawurlencode to handle spaces/symbols safely
            $adminAvatar = 'uploads/' . rawurlencode($filename);
        }
    }
} catch (Exception $e) {
    $adminAvatar = 'uploads/default-avatar.png';
}

$adminLastName = 'Admin'; // Fallback default name
$adminAvatar = 'uploads/default-avatar.png'; // Fallback image

try {
    if (isset($_SESSION['user_id'])) {
        // Fetch both first_name and avatar from the users table
        $stmt = $conn->prepare("SELECT last_name, avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set first name if available
        if (!empty($user['last_name'])) {
            $adminLastName = $user['last_name'];
        }

        // Set avatar path
        if (!empty($user['avatar'])) {
            $filename = basename($user['avatar']); 
            $adminAvatar = 'uploads/' . rawurlencode($filename);
        }
    }
} catch (Exception $e) {
    $adminLastName = 'Admin';
    $adminAvatar = 'uploads/default-avatar.png';
}

$adminAvatar = 'uploads/image.png';

try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Check if the database has an avatar and the file actually exists on the server
        if (!empty($user['avatar'])) {
            $filename = basename($user['avatar']); 
            $filePath = 'uploads/' . $filename;
            
            if (file_exists($filePath)) {
                $adminAvatar = 'uploads/' . rawurlencode($filename);
            }
        }
    }
} catch (Exception $e) {
    // Falls back to default-avatar.png automatically if query fails
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lorem Ipsum | Admin Dashboard</title>
    <link rel="stylesheet" href="sup_dashboard/superadmin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100&icon_names=favorite,home,search,settings" rel="stylesheet" />
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
        <header class="topbar">
            <div class="top-actions">
                <a href="export_attendance.php" class="button export-button" id="exportButton">
                    <i class="ri-download-2-line"></i></i> Export
                </a>

                <a href="add_hr_acc.php" class="btn add-button">
                    <i class="ri-user-add-line"></i>
                </a>
        
                <!-- <a href="superadmin_profile.php" class="profile" aria-label="Profile">
                    <img src="<?= htmlspecialchars($adminAvatar) ?>" alt="Admin Profile">
                </a> -->

                <a href="admin_profile.php" class="profile" aria-label="Profile">
                    <img src="<?= htmlspecialchars(!empty($adminAvatar) ? $adminAvatar : 'uploads/default-avatar.png') ?>" alt="Admin Profile">
                </a>
            </div>
        </header>

        <section class="welcome-row">
            <div>
                <h1>Hello, <?= htmlspecialchars($adminLastName) ?></h1>
                <p><?= date('l, d F Y') ?></p>
            </div>
            
            <button class="button primary">
                <a href="onboard_employees.php">
                    <i href="" class="ri-user-add-line"></i> Add New Employee
                </a>
            </button>
        </section>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-top">
                    <i class="ri-group-line stat-icon blue-icon"></i>
                    <a href="employees_list.php" class="stat-menu">
                        <i class="ri-more-2-fill blue-menu"></i>
                    </a>
                </div>
                <div class="stat-number"><?= number_format($totalEmployees) ?></div>
                <h3>Total employees</h3>
            </article>
            <article class="stat-card">
                <div class="stat-top">
                    <i class="ri-user-follow-line stat-icon green-icon"></i>
                    <a href="attendance_list.php" class="stat-menu">
                        <i class="ri-more-2-fill green-menu"></i>
                    </a>
                </div>
                <div class="stat-number stat-icon blue-icon"><?= number_format($activeEmployees) ?></div>
                <h3>Active employees</h3>
            </article>
            <article class="stat-card">
                <div class="stat-top">
                    <i class="ri-user-minus-line stat-icon red-icon"></i>
                    <a href="leave_management.php" class="stat-menu">
                        <i class="ri-more-2-fill red-menu"></i>
                    </a>
                </div>
                <div class="stat-number"><?= number_format($onLeaveEmployees) ?></div>
                <h3>Employees on leave</h3>
            </article>
        </section>

        <div class="dashboard-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">Daily attendance</div>
                </div>
                <div class="chart-body">
                    <div class="chart-axis">
                        <span>100%</span>
                        <span>75%</span>
                        <span>50%</span>
                        <span>25%</span>
                        <span>0</span>
                    </div>
                    <div class="bars-container">
                        <?php
                            // Days: 1=Sun, 2=Mon, 3=Tue, 4=Wed, 5=Thu, 6=Fri, 7=Sat
                            $daysMap = ['Sun' => 1, 'Mon' => 2, 'Tue' => 3, 'Wed' => 4, 'Thu' => 5, 'Fri' => 6, 'Sat' => 7];
                            foreach ($daysMap as $dayName => $dayNum):
                                $heightPct = getBarHeight($dayNum, $weeklyAttendance, $totalEmployees);
                        ?>
                            <div class="bar-col">
                                <div class="bar-col">
                                    <!-- Custom Tooltip Box -->
                                    <div class="chart-tooltip">
                                        <span class="tooltip-title"><?= $dayName ?></span>
                                        <span class="tooltip-body"><?= round($heightPct) ?>% attendance (demo data)</span>
                                    </div>

                                    <!-- Pass height via a CSS variable style attribute -->
                                    <div class="bar" style="--bar-height: <?= $heightPct ?>%;"></div>
                                    <span class="bar-label"><?= $dayName ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="right-stack">
                <div class="small-metric-card">
                    <div class="stat-top">
                        <i class="ri-logout-box-line card-icon green-check"></i>
                        <a href="leave_management.php" class="stat-menus">
                            <i class="ri-more-2-fill green-menus"></i>
                        </a>
                    </div>
                    <div class="stat-bottom">
                        <div class="metric-value"><?= number_format($pendingLeaves) ?></div>
                        <div class="metric-label">Pending leave request</div>
                    </div>
                </div>

                <div class="small-metric-card">
                    <div class="stat-top">
                        <i class="ri-folder-check-line card-icon blue-file"></i>
                        <a href="dashboard_recruitment.php" class="stat-menus">
                            <i class="ri-more-2-fill blue-menus"></i>
                        </a>
                    </div>
                    <div class="stat-bottom">
                        <div class="metric-value"><?= number_format($pendingApplications) ?></div>
                        <div class="metric-label">Pending application</div>
                    </div>
                </div>
            </div>
            
            <section class="panel employees-panel">
                <div class="panel-heading">
                    <h2>Employees</h2>
                    <div class="employee-actions">
                        <a href="onboard_employees.php" class="button primary">
                            <i class="ri-user-add-line a-primary"></i>
                            <span>Add New Employee</span>
                        </a>
                        <a href="employees_list.php" class="button outline" id="seeAllButton">See all</a>
                    </div>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee name</th>
                                <th>Employee ID</th>
                                <th>Position</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTable">
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $emp): ?>
                                    <?php 
                                        // Handle full name display supporting either single 'name' or separate 'first_name'/'last_name'
                                        if (isset($emp['name'])) {
                                            $fullName = $emp['name'];
                                        } else {
                                            $fullName = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
                                        }
                                        
                                        $status = $emp['status'] ?? 'Active';
                                        $isActive = (strcasecmp($status, 'Active') === 0);
                                        $badgeClass = $isActive ? 'active-status' : 'leave-status';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fullName) ?></td>
                                        <td>EMP-<?= sprintf('%04d', $emp['id']) ?></td>
                                        <td><?= htmlspecialchars($emp['position'] ?? 'Staff') ?></td>
                                        <td><?= htmlspecialchars($emp['email'] ?? '--') ?></td>
                                        <td>
                                            <span class="status <?= $badgeClass ?>">
                                                <?= htmlspecialchars($status) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No employees found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

<script src="sup_dashboard/superadmin_script.js"></script>
</body>
</html>