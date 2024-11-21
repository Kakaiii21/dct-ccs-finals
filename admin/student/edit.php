<?php
ob_start(); // Start output buffering
$title = "Edit Student"; // Set the title
include_once '../partials/header.php';
include_once '../partials/side-bar.php';
include_once '../../functions.php'; // Include the functions for reuse

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = '';
$success_msg = '';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch student data
    $student_data = getStudentDataById($student_id);
    if (!$student_data) {
        $errors = displayAlert(['Student not found.'], 'danger');
    }
} else {
    $errors = displayAlert(['No student selected to edit.'], 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($student_id)) {
        $updated_student = processStudentForm();  // Process the form data
        $validation_errors = validateStudentData($updated_student);  // Validate student data

        if (empty($validation_errors)) {
            // Update student data if no validation errors
            $update_errors = handleDuplicateStudent($updated_student);  // Handle duplicates
            if (empty($update_errors)) {
                $success_msg = updateStudent($updated_student, $student_id);  // Update the student if no errors
            }
        } else {
            $errors = displayAlert($validation_errors, 'danger');  // Show validation errors
        }
    }
}

/**
 * Process and prepare the form data.
 * 
 * @return array The student data.
 */
function processStudentForm() {
    return [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? '')
    ];
}

/**
 * Validate student data.
 * 
 * @param array $student The student data.
 * @return array Validation errors.
 */
function validateStudentData($student) {
    // Add validation rules if necessary
    $validation_errors = [];
    if (empty($student['first_name'])) {
        $validation_errors[] = 'First Name is required.';
    }
    if (empty($student['last_name'])) {
        $validation_errors[] = 'Last Name is required.';
    }
    return $validation_errors;
}

/**
 * Update student in the database.
 * 
 * @param array $updated_student The updated student data.
 * @param int $student_id The student ID.
 * @return string Success or error message.
 */
function updateStudent($updated_student, $student_id) {
    $db = databaseConnection();
    $query = "UPDATE students SET first_name = ?, last_name = ? WHERE id = ?";
    $stmt = $db->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ssi', $updated_student['first_name'], $updated_student['last_name'], $student_id);
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return displayAlert(['Student record successfully updated.'], 'success');
        } else {
            $stmt->close();
            $db->close();
            return displayAlert(['Failed to update student record: ' . $stmt->error], 'danger');
        }
    } else {
        $db->close();
        return displayAlert(['Error preparing statement: ' . $db->error], 'danger');
    }
}

/**
 * Handle potential duplicate student entries.
 * 
 * @param array $student The student data.
 * @return string Error message if a duplicate is found, empty otherwise.
 */
function handleDuplicateStudent($student) {
    $duplicate_check = isStudentIdDuplicate($student);
    return !empty($duplicate_check) ? displayAlert([$duplicate_check], 'danger') : '';
}

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Edit Student</h1>

    <!-- Breadcrumb navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../student/register.php">Register Student</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Student</li>
        </ol>
    </nav>

    <!-- Display errors and success messages -->
    <?php if (!empty($errors)): ?>
        <?php echo $errors; ?>
    <?php elseif (!empty($success_msg)): ?>
        <?php echo $success_msg; ?>
    <?php endif; ?>

    <!-- Edit form -->
    <?php if (isset($student_data)): ?>
        <form method="post" action="">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_data['student_id']); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" value="<?php echo htmlspecialchars($student_data['first_name']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?php echo htmlspecialchars($student_data['last_name']); ?>">
                    </div>

                    <div class="mb-3">
                        <button type="submit" name="update_student" class="btn btn-primary w-100">Update Student</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php include_once '../partials/footer.php'; ?>
<?php ob_end_flush(); // Flush output buffer ?>
