<?php
session_start();
require_once "Database.php";
$conn = (new Database())->connect();

if (isset($_GET['id'])) {
    $recordId = intval($_GET['id']);

    // Fetch the specific past attendance file record from your database
    // (Adjust 'attendance_files' and column names to match your actual database schema)
    $stmt = $conn->prepare("SELECT * FROM attendance_files WHERE id = ?");
    $stmt->execute([$recordId]);
    $fileRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fileRecord) {
        $storedFileName = $fileRecord['file_name']; // e.g., 1729952000_Attendance_Report.csv
        $filePath = "uploads/" . $storedFileName;

        if (file_exists($filePath)) {
            // Clean original filename for the user download (strips the timestamp prefix)
            $downloadName = preg_replace('/^\d+_(.*)$/', '$1', $storedFileName);

            // Force CSV browser download headers
            header('Description: File Transfer');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            flush();
            readfile($filePath);
            exit;
        } else {
            die("Error: The requested attendance file could not be found on the server.");
        }
    } else {
        die("Error: Attendance record not found in the database.");
    }
} else {
    die("Invalid download request.");
}