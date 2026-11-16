<?php
// Demo dashboard data. Replace these arrays with database queries when your MySQL tables are ready.
date_default_timezone_set('Asia/Manila');

$stats = [
    'total_employees' => 173,
    'active_employees' => 140,
    'employees_on_leave' => 8,
    'pending_leave' => 10,
    'pending_applications' => 20
];

$employees = [
    ['name' => 'John Doe', 'id' => '00001', 'position' => 'Marketing', 'email' => 'jdoe@gmail.com', 'status' => 'Active'],
    ['name' => 'Tony Stark', 'id' => '00002', 'position' => 'Manager', 'email' => 'tstark@gmail.com', 'status' => 'On leave'],
    ['name' => 'Peter Parker', 'id' => '00003', 'position' => 'Production Staff', 'email' => 'speter@gmail.com', 'status' => 'Active'],
    ['name' => 'Jane Doe', 'id' => '00004', 'position' => 'Marketing', 'email' => 'jane@gmail.com', 'status' => 'Active']
];

function e($value) { 
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Admin Dashboard</title>
    <link rel="stylesheet" href="main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="main.js" defer></script>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="brand-row">
            <div class="brand">YOUR COMPANY.</div>
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">▣</button>
        </div>
        
        <div class="nav-group-title">Main Menu</div>
        <a class="nav-item active" href="dashboard.php"><span class="nav-icon">⌂</span>Dashboard</a>
        
        <div class="nav-group-title">HR Management</div>
        <a class="nav-item" href="#"><span class="nav-icon">♧</span>Employees</a>
        <a class="nav-item" href="#"><span class="nav-icon">▤</span>Attendance</a>
        <a class="nav-item" href="#"><span class="nav-icon">♧</span>Leave Request</a>
        
        <div class="nav-group-title">Analytics &amp; Reports</div>
        <a class="nav-item" href="#"><span class="nav-icon">▱</span>Recruitment</a>
        <a class="nav-item" href="#"><span class="nav-icon">⇄</span>Payroll</a>
        
        <a class="nav-item logout" href="#"><span class="nav-icon">⇥</span>Log out</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <button class="mobile-menu" id="mobileMenu" aria-label="Open menu">☰</button>
            <div class="top-actions">
                <button class="button export-button" id="exportButton">♧ Export</button>
                <button class="profile" aria-label="Profile">●</button>
            </div>
        </header>

        <section class="welcome-row">
            <div>
                <h1>Hello, HR Admin</h1>
                <p><?= e(date('l, d F Y')) ?></p>
            </div>
            <button class="button primary"><span>♙</span> Add New Employee</button>
        </section>

        <section class="stats-grid">
            <article class="stat-card blue">
                <div class="stat-top">
                    <span class="stat-icon">♧</span>
                    <span class="dots">•••</span>
                </div>
                <div class="stat-number"><?= e($stats['total_employees']) ?></div>
                <h3>Total employees</h3>
            </article>
            <article class="stat-card green">
                <div class="stat-top">
                    <span class="stat-icon">✓</span>
                    <span class="dots">•••</span>
                </div>
                <div class="stat-number"><?= e($stats['active_employees']) ?></div>
                <h3>Active employees</h3>
            </article>
            <article class="stat-card red">
                <div class="stat-top">
                    <span class="stat-icon">♙</span>
                    <span class="dots">•••</span>
                </div>
                <div class="stat-number"><?= e($stats['employees_on_leave']) ?></div>
                <h3>Employees on leave</h3>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="panel chart-panel">
                <div class="panel-heading">
                    <h2>Daily attendance</h2>
                    <select id="periodSelect" aria-label="Attendance period">
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="chart-wrap">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </article>
            
            <div class="side-stats">
                <article class="stat-card compact red">
                    <div class="stat-top">
                        <span class="stat-icon">▣</span>
                        <span class="dots">•••</span>
                    </div>
                    <div class="stat-number"><?= e($stats['pending_leave']) ?></div>
                    <h3>Pending leave request</h3>
                </article>
                <article class="stat-card compact blue">
                    <div class="stat-top">
                        <span class="stat-icon">♧</span>
                        <span class="dots">•••</span>
                    </div>
                    <div class="stat-number"><?= e($stats['pending_applications']) ?></div>
                    <h3>Pending application</h3>
                </article>
            </div>
        </section>

        <section class="panel employees-panel">
            <div class="panel-heading">
                <h2>Employees</h2>
                <div class="employee-actions">
                    <button class="button primary">♙ Add New Employee</button>
                    <button class="button outline" id="seeAllButton">See all</button>
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
                    <?php foreach ($employees as $employee): 
                        $isActive = strtolower($employee['status']) === 'active'; 
                    ?>
                        <tr>
                            <td><?= e($employee['name']) ?></td>
                            <td><?= e($employee['id']) ?></td>
                            <td><?= e($employee['position']) ?></td>
                            <td><?= e($employee['email']) ?></td>
                            <td>
                                <span class="status <?= $isActive ? 'active-status' : 'leave-status' ?>">
                                    <?= e($employee['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>