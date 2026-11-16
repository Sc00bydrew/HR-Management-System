<?php
require_once "Database.php";

class Auth
{
    private $conn;
    public function __construct()
    {
        // Safe session check to prevent duplicate session start notices
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->conn = (new Database())->connect();
    }


    public function login($identifier, $password)
    {
        // TEMPORARY AUTO-FIX: Force update user ID 30011 password to "123" right now
        $freshHash = password_hash('123', PASSWORD_DEFAULT);
        $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = 30011");
        $updateStmt->execute([$freshHash]);

        // Now proceed with normal login check
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['auth'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return true;
        }

        return false;
    }

    // REGISTER FUNCTION
    public function register($username, $email, $password)
    {
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return "Email already registered";
        }

        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed]);

        return true;
    }

    // LOGOUT FUNCTION
    public function logout()
    {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['auth']);
    }
}