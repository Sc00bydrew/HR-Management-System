<?php
require_once "Database.php";

class Attendance
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // CLOCK IN
    public function clockIn($employee_id, $date)
    {
        // Check if already clocked in today
        $stmt = $this->conn->prepare("SELECT id, time_in FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employee_id, $date]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentTime = date('H:i:s');

        if ($existing) {
            if (!empty($existing['time_in'])) {
                return "Employee has already clocked in for this date.";
            } else {
                // Update time_in if record exists without it
                $update = $this->conn->prepare("UPDATE attendance SET time_in = ?, status = 'Present' WHERE id = ?");
                $update->execute([$currentTime, $existing['id']]);
                return true;
            }
        } else {
            // Insert new attendance record
            $sql = "INSERT INTO attendance (employee_id, date, time_in, status) VALUES (?, ?, ?, 'Present')";
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute([$employee_id, $date, $currentTime])) {
                return true;
            }
        }
        return "Failed to process clock-in.";
    }

    // CLOCK OUT
    public function clockOut($employee_id, $date)
    {
        $stmt = $this->conn->prepare("SELECT id, time_in, time_out FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->execute([$employee_id, $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record || empty($record['time_in'])) {
            return "Cannot clock out. Employee has not clocked in today.";
        }

        if (!empty($record['time_out'])) {
            return "Employee has already clocked out for today.";
        }

        $currentTime = date('H:i:s');
        $update = $this->conn->prepare("UPDATE attendance SET time_out = ? WHERE id = ?");
        if ($update->execute([$currentTime, $record['id']])) {
            return true;
        }

        return "Failed to process clock-out.";
    }

    // GET ALL EMPLOYEES FOR DROPDOWN
    public function getAllEmployees()
    {
        return $this->conn->query("SELECT id, first_name, last_name, position FROM employees ORDER BY first_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET TODAY'S ATTENDANCE LOGS FOR TABLE DISPLAY
    public function getAttendanceLogs($date)
    {
        $sql = "SELECT e.id as employee_id, e.first_name, e.last_name, e.position, e.avatar, 
                    a.date, a.time_in, a.time_out, COALESCE(a.status, 'Absent') AS status
                FROM employees e
                LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
                ORDER BY e.first_name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}