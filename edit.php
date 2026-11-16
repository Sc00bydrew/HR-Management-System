<?php
require_once "Student.php";
$student = new Student();

// If no ID is passed, redirect back
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Get student by ID
$editStudent = $student->getById($_GET['id']);

// Handle Update
if (isset($_POST['update'])) {
    $student->update($_POST['id'], $_POST['name'], $_POST['course']);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    Edit Student
                </div>
                <div class="card-body">

                    <form method="post">
                        <input type="hidden" name="id" value="<?= $editStudent['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text"
                                name="name"
                                class="form-control"
                                value="<?= htmlspecialchars($editStudent['name']) ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <input type="text"
                                name="course"
                                class="form-control"
                                value="<?= htmlspecialchars($editStudent['course']) ?>"
                                required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                Back
                            </a>

                            <button type="submit" name="update" class="btn btn-warning">
                                Update Student
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
