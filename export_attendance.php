<?php
require_once "Database.php";

$conn = (new Database())->connect();
$filter_date = $_GET['filter_date'] ?? '';

$filename = "Attendance_Report_" . (!empty($filter_date) ? $filter_date : date('Y-m-d')) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['Date', 'Employee ID', 'First Name', 'Last Name', 'Position', 'Time In', 'Time Out', 'Status']);

if (!empty($filter_date)) {
    $sql = "SELECT a.date, e.id AS employee_id, e.first_name, e.last_name, e.position, 
                    a.time_in, a.time_out, COALESCE(a.status, 'Absent') AS status
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            WHERE a.date = ?
            ORDER BY a.date DESC, e.first_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$filter_date]);
} else {
    $sql = "SELECT a.date, e.id AS employee_id, e.first_name, e.last_name, e.position, 
                    a.time_in, a.time_out, COALESCE(a.status, 'Absent') AS status
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            ORDER BY a.date DESC, e.first_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['date'],
        'EMP-' . sprintf('%03d', $row['employee_id']),
        $row['first_name'],
        $row['last_name'],
        $row['position'],
        $row['time_in'] ?? '--:--',
        $row['time_out'] ?? '--:--',
        $row['status']
    ]);
}

fclose($output);
exit;