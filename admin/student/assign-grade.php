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

// Function to establish database connection
function databaseConnection(): mysqli {
    $servername = 'localhost';
    $username = 'root';         // Change as needed
    $password = '';             // Change as needed
    $dbname = 'dct-ccs-finals'; // Replace with your actual database name

    // Create and check the connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Get record ID from POST or GET request
$record_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$record_id) {
    // Redirect to the attach-subject page if no valid ID is provided
    header("Location: attach-subject.php");
    exit;
}

// Fetch student and subject data based on the record ID
$connection = databaseConnection();
$query = "SELECT students.id AS student_id, students.first_name, students.last_name, 
                 subjects.subject_code, subjects.subject_name, students_subjects.grade 
          FROM students_subjects 
          JOIN students ON students_subjects.student_id = students.id 
          JOIN subjects ON students_subjects.subject_id = subjects.id 
          WHERE students_subjects.id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $record = $result->fetch_assoc();

    // Handle form submission for assigning or updating the grade
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_grade'])) {
        $grade = $_POST['grade'];

        // Grade validation
        if (empty($grade)) {
            $error_message = "Grade cannot be blank.";
        } elseif (!is_numeric($grade) || $grade < 0 || $grade > 100) {
            $error_message = "Grade must be a numeric value between 0 and 100.";
        } else {
            $grade = floatval($grade);

            // Update grade in the database
            $update_query = "UPDATE students_subjects SET grade = ? WHERE id = ?";
            $update_stmt = $connection->prepare($update_query);
            $update_stmt->bind_param('di', $grade, $record_id);

            if ($update_stmt->execute()) {
                $success_message = "Grade successfully assigned.";
                header("Location: attach-subject.php?id=" . htmlspecialchars($record['student_id']));
                exit;
            } else {
                $error_message = "Failed to assign the grade. Please try again.";
            }
        }
    }
} else {
    // Redirect if no record is found
    header("Location: attach-subject.php");
    exit;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Assign Grade to Subject</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../student/register.php">Register Student</a></li>
            <li class="breadcrumb-item"><a href="attach-subject.php?id=<?php echo htmlspecialchars($record['student_id'] ?? ''); ?>">Attach Subject to Student</a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign Grade to Subject</li>
        </ol>
    </nav>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($record)): ?>
        <div class="card">
            <div class="card-body">
                <h5>Selected Student and Subject Information</h5>
                <ul>
                    <li><strong>Student ID:</strong> <?php echo htmlspecialchars($record['student_id']); ?></li>
                    <li><strong>Name:</strong> <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></li>
                    <li><strong>Subject Code:</strong> <?php echo htmlspecialchars($record['subject_code']); ?></li>
                    <li><strong>Subject Name:</strong> <?php echo htmlspecialchars($record['subject_name']); ?></li>
                </ul>

           
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../partials/footer.php'; ?>
<?php ob_end_flush(); // Flush output buffer ?>
