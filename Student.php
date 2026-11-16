<?php
require_once "Database.php";

class Student
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // CREATE
    public function add($name, $course)
    {
        $stmt = $this->conn->prepare("INSERT INTO employees (name, course) VALUES (?, ?)");
        $stmt->execute([$name, $course]);
    }
    // READ
    public function getAll()
    {
        return $this->conn->query("SELECT * FROM employees")->fetchAll(PDO::FETCH_ASSOC);
    }
    // UPDATE
    public function update($id, $name, $course) {
        $stmt = $this->conn->prepare("UPDATE employees SET name=?, course=? WHERE id=?");
        $stmt->execute([$name, $course, $id]);
    }
    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM employees WHERE id=?");
        $stmt->execute([$id]);
    }

    //EDIT
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

}

}
