<?php
require_once "Database.php";

class Employee
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    // HELPER: Handle Avatar Uploads
    private function uploadAvatar($file)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $file['tmp_name'];
            $fileName = $file['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = 'uploads/';
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    return $newFileName;
                }
            }
        }
        return null;
    }

    // ADD NEW EMPLOYEE / ONBOARDING
    public function add(
        $first_name, $last_name, $mobile, $email, $emergency_contact, 
        $relative_name, $gender, $date_joined, $position, $salary, 
        $bank, $account_number, 
        $sss_number, $philhealth_number, $tin_number, 
        $avatar_file = null
    ) {
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return "This email is already registered.";
        }

        // Process avatar upload
        $avatarName = $this->uploadAvatar($avatar_file);

        // Insert employee details including government/statutory numbers
        $sql = "INSERT INTO employees 
                (first_name, last_name, mobile, email, emergency_contact, relative_name, gender, date_joined, position, salary, bank, account_number, sss_number, philhealth_number, tin_number, avatar) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute([
            $first_name, $last_name, $mobile, $email, 
            $emergency_contact, $relative_name, $gender, 
            $date_joined, $position, $salary, $bank, $account_number, 
            $sss_number, $philhealth_number, $tin_number, 
            $avatarName
        ])) {
            return true;
        }

        return "Failed to onboard employee. Please try again.";
    }

    // GET ALL EMPLOYEES
    public function getAll()
    {
        return $this->conn->query("SELECT * FROM employees ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET SINGLE EMPLOYEE BY ID
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE EMPLOYEE
    public function update(
        $id, $first_name, $last_name, $mobile, $email, $emergency_contact, 
        $relative_name, $gender, $date_joined, $position, $salary, 
        $bank, $account_number, 
        $sss_number, $philhealth_number, $tin_number, 
        $avatar_file = null
    ) {
        // Check if email is already taken by another employee
        $stmt = $this->conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            return "This email is already used by another employee.";
        }

        // Get current record to manage existing avatar
        $current = $this->getById($id);
        $avatarName = $current['avatar'] ?? null;

        // If a new avatar was uploaded, replace the old one
        if (isset($avatar_file) && $avatar_file['error'] === UPLOAD_ERR_OK) {
            $newAvatar = $this->uploadAvatar($avatar_file);
            if ($newAvatar) {
                if ($avatarName && file_exists("uploads/" . $avatarName)) {
                    unlink("uploads/" . $avatarName);
                }
                $avatarName = $newAvatar;
            }
        }

        $sql = "UPDATE employees SET 
                first_name = ?, last_name = ?, mobile = ?, email = ?, 
                emergency_contact = ?, relative_name = ?, gender = ?, 
                date_joined = ?, position = ?, salary = ?, bank = ?, account_number = ?, 
                sss_number = ?, philhealth_number = ?, tin_number = ?, 
                avatar = ? 
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute([
            $first_name, $last_name, $mobile, $email, 
            $emergency_contact, $relative_name, $gender, 
            $date_joined, $position, $salary, $bank, $account_number, 
            $sss_number, $philhealth_number, $tin_number, 
            $avatarName, $id
        ])) {
            return true;
        }

        return "Failed to update employee details.";
    }

    // DELETE EMPLOYEE
    public function delete($id)
    {
        // Remove avatar file from server if it exists
        $current = $this->getById($id);
        if ($current && !empty($current['avatar']) && file_exists("uploads/" . $current['avatar'])) {
            unlink("uploads/" . $current['avatar']);
        }

        $stmt = $this->conn->prepare("DELETE FROM employees WHERE id = ?");
        return $stmt->execute([$id]);
    }
}