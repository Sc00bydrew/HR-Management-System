<?php
date_default_timezone_set('Asia/Manila'); // Add this line
session_start(); // <--- CRITICAL: Must be at the very top to read session variables
require_once "Database.php";

$conn = (new Database())->connect();

// 1. Determine Current Half-Month Period
$current_day   = (int)date('j'); // Day of the month without leading zeros (1 to 31)
$current_month = date('m');      // Current month (01 to 12)
$current_year  = date('Y');      // Current year

if ($current_day <= 15) {
    // First Half: Days 1 to 15
    $startDate = "$current_year-$current_month-01";
    $endDate   = "$current_year-$current_month-15";
    $periodTitle = date('F 1', strtotime($startDate)) . " - " . date('F 15, Y', strtotime($endDate));
} else {
    // Second Half: Day 16 to Last Day of Month
    $lastDay   = date('t'); // Gets last day of the current month (28, 29, 30, or 31)
    $startDate = "$current_year-$current_month-16";
    $endDate   = "$current_year-$current_month-$lastDay";
    $periodTitle = date('F 16', strtotime($startDate)) . " - " . date('F t, Y', strtotime($endDate));
}

// Get Status Filter from URL if set
$statusFilter = trim($_GET['status'] ?? '');

// 2. Query Current Active Half-Month Attendance with Status Filtering
$sql = "SELECT a.date, e.id AS employee_id, e.first_name, e.last_name, e.position, e.avatar, 
               a.time_in, a.time_out, 
               CASE 
                   WHEN a.time_out IS NULL AND a.date < CURDATE() THEN 'Absent'
                   ELSE COALESCE(a.status, 'Absent')
               END AS status
        FROM attendance a
        JOIN employees e ON a.employee_id = e.id
        WHERE a.date BETWEEN ? AND ?";

$params = [$startDate, $endDate];

// Filter by calculated status using HAVING
if (!empty($statusFilter)) {
    $sql .= " HAVING status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY a.date DESC, e.first_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Query Previous Half-Month Periods with True Calendar Ranges
$archivedSql = "SELECT 
                    log_year,
                    log_month,
                    period_type,
                    start_range,
                    end_range,
                    COUNT(id) as total_records
                FROM (
                    SELECT 
                        a.id,
                        YEAR(a.date) as log_year,
                        MONTH(a.date) as log_month,
                        IF(DAY(a.date) <= 15, '1st Half Month (1-15)', '2nd Half Month (16-End)') AS period_type,
                        IF(DAY(a.date) <= 15, 
                           CONCAT(YEAR(a.date), '-', LPAD(MONTH(a.date), 2, '0'), '-01'), 
                           CONCAT(YEAR(a.date), '-', LPAD(MONTH(a.date), 2, '0'), '-16')
                        ) as start_range,
                        IF(DAY(a.date) <= 15, 
                           CONCAT(YEAR(a.date), '-', LPAD(MONTH(a.date), 2, '0'), '-15'), 
                           LAST_DAY(CONCAT(YEAR(a.date), '-', LPAD(MONTH(a.date), 2, '0'), '-01'))
                        ) as end_range
                    FROM attendance a
                    WHERE a.date < ?
                ) as derived
                GROUP BY log_year, log_month, period_type, start_range, end_range
                ORDER BY start_range DESC";

$archivedStmt = $conn->prepare($archivedSql);
$archivedStmt->execute([$startDate]);
$archivedPeriods = $archivedStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Auto-Sync Archived Cycles into payroll_files Table
foreach ($archivedPeriods as $archive) {
    $startR = $archive['start_range'];
    $endR = $archive['end_range'];
    $filename = "Attendance_Cycle_" . $startR . "_to_" . $endR . ".csv";
    $filepath = "uploads/payroll_files/" . $filename;

    // Ensure directory exists
    if (!file_exists('uploads/payroll_files/')) {
        mkdir('uploads/payroll_files/', 0777, true);
    }

    // Check if this file name already exists in the database table
    $chk = $conn->prepare("SELECT id FROM payroll_files WHERE file_name = ?");
    $chk->execute([$filename]);
    
    if (!$chk->fetch()) {
        // Automatically create CSV file on server if not present
        if (!file_exists($filepath)) {
            $csvExportSql = "SELECT a.date, e.id AS employee_id, e.first_name, e.last_name, e.position, 
                                   a.time_in, a.time_out, COALESCE(a.status, 'Absent') AS status
                            FROM attendance a
                            JOIN employees e ON a.employee_id = e.id
                            WHERE a.date BETWEEN ? AND ?";
            $csvStmt = $conn->prepare($csvExportSql);
            $csvStmt->execute([$startR, $endR]);
            $csvRows = $csvStmt->fetchAll(PDO::FETCH_ASSOC);

            $output = fopen($filepath, 'w');
            fputcsv($output, ['Date', 'Employee ID', 'First Name', 'Last Name', 'Position', 'Time In', 'Time Out', 'Status']);
            foreach ($csvRows as $row) {
                fputcsv($output, [
                    $row['date'],
                    'EMP-' . sprintf('%03d', $row['employee_id']),
                    $row['first_name'],
                    $row['last_name'],
                    $row['position'],
                    $row['time_in'],
                    $row['time_out'],
                    $row['status']
                ]);
            }
            fclose($output);
        }

        // Insert record matching your exact table columns (file_name)
        $ins = $conn->prepare("INSERT INTO payroll_files (file_name) VALUES (?)");
        $ins->execute([$filename]);
    }
}

// Default Fallbacks for Admin Profile
$adminLastName = 'Admin';
$adminAvatar = 'uploads/default-avatar.png';
$isSuperAdmin = false;

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

            // Check if the user role is superadmin
            if (!empty($user['role']) && strtolower(trim($user['role'])) === 'super_admin') {
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
    <title>Lorem Ipsum | Attendance List</title>
    <link rel="stylesheet" href="attendance/attendance_list_style.css">
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
        <a class="nav-item in" href="attendance_list.php">
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
                <h1>Attendance</h1>
                <p><strong>Current Cycle:</strong> <?= $periodTitle ?></p>
            </div>
            
            <div class="employee-header-actions">
                <!-- Custom Status Dropdown Filter -->
                <div class="custom-dropdown-container">
                    <button type="button" class="button outline" id="filterDropdownBtn">
                        <i class="ri-equalizer-line"></i>
                        <span><?= !empty($statusFilter) ? htmlspecialchars($statusFilter) : 'Filter' ?></span>
                    </button>

                    <div id="filterDropdownMenu" class="dropdown-menu">
                        <a href="attendance_list.php" class="dropdown-item <?= empty($statusFilter) ? 'active' : '' ?>">
                            All Status
                        </a>
                        <a href="attendance_list.php?status=Present" class="dropdown-item <?= ($statusFilter === 'Present') ? 'active' : '' ?>">
                            Present
                        </a>
                        <a href="attendance_list.php?status=Late" class="dropdown-item <?= ($statusFilter === 'Late') ? 'active' : '' ?>">
                            Late
                        </a>
                        <a href="attendance_list.php?status=Absent" class="dropdown-item <?= ($statusFilter === 'Absent') ? 'active' : '' ?>">
                            Absent
                        </a>
                    </div>
                </div>

                <!-- Export Button -->
                <button class="button primary">
                    <a href="export_attendance.php?start=<?= $startDate ?>&end=<?= $endDate ?>" class="add-employee-btn-link">
                        <i class="ri-download-2-line"></i> Export Current Cycle
                    </a>
                </button>
            </div>
        </section>

        <section class="panel employees-panel">
            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Date</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Position</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($log['avatar']) && file_exists("uploads/" . $log['avatar'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($log['avatar']) ?>" class="table-avatar" alt="Avatar">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; border-radius: 12px; overflow: hidden; background-color: #F0F2F0; display: inline-block;">
                                            <img src="uploads/image.png" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['date']) ?></td>
                                <td>EMP-<?= sprintf('%03d', $log['employee_id']) ?></td>
                                <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                                <td><?= htmlspecialchars($log['position']) ?></td>
                                <td><?= !empty($log['time_in']) ? date('h:i A', strtotime($log['time_in'])) : '<span style="color: #605E5E;">----:----</span>' ?></td>
                                <td><?= !empty($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : '<span style="color: #605E5E;">----:----</span>' ?></td>
                                <td>

                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $log['status'])) ?>">
                                        <?= htmlspecialchars($log['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #9ca3af; padding: 30px;">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="archived-row">
            <h3>
                Past Half-Month Attendance Records
            </h3>
        </section>

        <section class="panel">
            <table class="archived-table">
                <thead>
                    <tr>
                        <th>Cycle Period</th>
                        <th>Date Range</th>
                        <th>Total Records</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($archivedPeriods) > 0): ?>
                        <?php foreach ($archivedPeriods as $archive): ?>
                            <tr>
                                <td><?= htmlspecialchars($archive['period_type']) ?></td>
                                <td><?= date('M d, Y', strtotime($archive['start_range'])) ?> - <?= date('M d, Y', strtotime($archive['end_range'])) ?></td>
                                <td><?= $archive['total_records'] ?> Logs</td>
                                <td>
                                    <a href="export_attendance.php?start=<?= $archive['start_range'] ?>&end=<?= $archive['end_range'] ?>" class="button secondary">
                                        <i class="ri-download-2-line"></i> Download Export
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                No archived half-month records available yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

<script src="attendance/attendance_list_script.js"></script>
</body>
</html>