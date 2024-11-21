<?php
ob_start(); // Start output buffering

require_once '../partials/header.php';
require_once '../partials/side-bar.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$error_message = '';
$success_message = '';
$student_data = null;

// Fetch student data if ID is provided
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    $student_data = getStudentDataById($student_id);
    if (!$student_data) {
        $error_message = "Student not found.";
    }
} else {
    $error_message = "No student selected to delete.";
}

// Handle form submission to delete the student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    deleteStudent($student_id);
}

function deleteStudent($student_id) {
    global $error_message;

    if (isset($student_id)) {
        $connection = db_connect();
        $query = "DELETE FROM students WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $student_id);

        if ($stmt->execute()) {
            header("Location: ../student/register.php");
            exit();
        } else {
            $error_message = "Failed to delete student record. Error: " . $stmt->error;
        }

        $stmt->close();
        $connection->close();
    } else {
        $error_message = "Invalid student ID.";
    }
}

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Delete a Student</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../student/register.php">Register Student</a></li>
            <li class="breadcrumb-item active" aria-current="page">Delete Student</li>
        </ol>
    </nav>

    <?php displayMessage($error_message, 'danger'); ?>
    <?php displayMessage($success_message, 'success'); ?>

    <?php if ($student_data): ?>
        <div class="card">
            <div class="card-body">
                <p>Are you sure you want to delete the following student record?</p>
                <ul>
                    <li><strong>Student ID:</strong> <?php echo htmlspecialchars($student_data['student_id']); ?></li>
                    <li><strong>First Name:</strong> <?php echo htmlspecialchars($student_data['first_name']); ?></li>
                    <li><strong>Last Name:</strong> <?php echo htmlspecialchars($student_data['last_name']); ?></li>
                </ul>
                <form method="post" action="">
                    <button type="submit" name="delete_student" class="btn btn-danger">Delete Student Record</button>
                    <a href="../student/register.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
require_once '../partials/footer.php';
ob_end_flush(); // Flush output buffer

// Function to display messages
function displayMessage($message, $type) {
    if (!empty($message)) {
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
?>
