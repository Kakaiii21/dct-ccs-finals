<?php
$Pagetitle = "Add New Student"; // Page title
include_once '../partials/header.php';
include_once '../partials/side-bar.php';
include_once '../../functions.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = '';
$success_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_student = processStudentForm();  // Process the form data
    $validation_errors = validateStudentData($new_student);  // Validate student data

    if (empty($validation_errors)) {
        $errors = handleDuplicateStudent($new_student);  // Handle duplicates
        if (empty($errors)) {
            $success_msg = registerNewStudent($new_student);  // Register the student if no errors
        }
    } else {
        $errors = displayAlert($validation_errors, 'danger');  // Show validation errors
    }
}

/**
 * Process and prepare the form data.
 * 
 * @return array The student data.
 */
function processStudentForm() {
    return [
        'id_number' => sanitizeStudentId(trim($_POST['student_id'] ?? '')),
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
    return verifyStudentData($student);
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

/**
 * Registers a new student in the database.
 * 
 * @param array $new_student The student data to insert.
 * @return string Success or error message.
 */
function registerNewStudent($new_student) {
    $db = databaseConnection();
    $unique_id = createUniqueStudentId();
    $insert_query = "INSERT INTO students (id, student_id, first_name, last_name) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($insert_query);
    
    if ($stmt) {
        $stmt->bind_param('isss', $unique_id, $new_student['id_number'], $new_student['first_name'], $new_student['last_name']);
        
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return displayAlert(["Student registration successful!"], 'success');
        } else {
            $stmt->close();
            $db->close();
            return displayAlert(["Registration failed: " . $stmt->error], 'danger');
        }
    } else {
        $db->close();
        return displayAlert(["Error preparing statement: " . $db->error], 'danger');
    }
}

/**
 * Fetch all students from the database.
 * 
 * @param object $db The database connection.
 * @return object The result set of students.
 */
function fetchAllStudents($db) {
    $fetch_query = "SELECT * FROM students";
    return $db->query($fetch_query);
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Add New Student</h1>

    <!-- Breadcrumb navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Student</li>
        </ol>
    </nav>

    <!-- Display errors and success messages -->
    <?php if (!empty($errors)): ?>
        <?php echo $errors; ?>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="post" action="">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter Student ID" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Register Student</button>
                </div>
            </div>
        </div>
    </form>

    <hr>

    <!-- List of students -->
    <div class="card">
        <div class="card-body">
            <h2 class="h4">Student List</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Student ID</th>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $db = databaseConnection();
                    $students = fetchAllStudents($db);
                    while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                <a href="attach-subject.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Attach Subject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php $db->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include_once '../partials/footer.php'; ?>
