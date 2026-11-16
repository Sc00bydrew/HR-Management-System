<?php
require_once "Database.php";

class Employee
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // ADD APPLICANT
    public function add($first_name, $last_name, $phone_number, $email, $position)
    {
        // Check if email already exists in the employees table
        $stmt = $this->conn->prepare("SELECT * FROM applicant WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return "This email is already registered.";
        }

        // Insert new applicant record
        $stmt = $this->conn->prepare("INSERT INTO applicant (first_name, last_name, phone_number, email, position) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$first_name, $last_name, $phone_number, $email, $position])) {
            return true;
        }

        return "Failed to submit application. Please try again.";
    }

    // GET ALL APPLICANTS (For Admin View)
    public function getAll()
    {
        return $this->conn->query("SELECT * FROM applicant ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // DELETE APPLICANT
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM applicant WHERE id = ?");
        return $stmt->execute([$id]);
    }
}