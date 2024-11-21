<?php    
 function databaseConnection(): mysqli {
    $servername = 'localhost';
    $username = 'root';        // Change if needed
    $password = "";            // Change if needed
    $dbname = 'dct-ccs-finals'; // Replace with your actual database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
function userLogin($email, $password) {
    $conn = databaseConnection();
    $query = "SELECT * FROM users WHERE email = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }

    $hashedPassword = md5($password);
    $stmt->bind_param("ss", $email, $hashedPassword); // Bind parameters (string, string)

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}
// Function to fetch all subjects from the database
function getAllSubjects() {
    $connection = databaseConnection();
    $query = "SELECT * FROM subjects";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row; // Fetch and store each subject as an associative array
        }
        return $subjects;
    }

    return []; // Return an empty array if no subjects are found
}

// Function to validate subject data (subject code and subject name)
function validateSubjectInputs($subject_data) {
    $errors = [];

    // Check if subject code is provided and has a valid length
    if (empty($subject_data['subject_code'])) {
        $errors[] = "Subject code is required.";
    } elseif (strlen($subject_data['subject_code']) > 4) { // Limiting subject code length to 4 characters
        $errors[] = "Subject code cannot be longer than 4 characters.";
    }

    // Check if subject name is provided and is not too long
    if (empty($subject_data['subject_name'])) {
        $errors[] = "Subject name is required.";
    } elseif (strlen($subject_data['subject_name']) > 100) { // Limiting subject name length to 100 characters
        $errors[] = "Subject name cannot be longer than 100 characters.";
    }

    return $errors; // Return the list of errors
}

function checkDuplicateSubjectCode($subject_code) {
    $connection = databaseConnection(); // Corrected to use databaseConnection()
    $query = "SELECT * FROM subjects WHERE subject_code = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Subject code already exists. Please choose another."; // Return the error message for duplicates
    }

    return ''; // No duplicate found
}

function checkDuplicateSubjectName($subject_name) {
    $connection = databaseConnection();
    $query = "SELECT * FROM subjects WHERE subject_name = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $subject_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Subject Name already exists.";
    }

    return ''; // No error
}



// Function to add a new subject to the database
function addNewSubject($subject_data) {
    // Validate the input data
    $validation_errors = validateSubjectInputs($subject_data);
    if (!empty($validation_errors)) {
        return renderAlert($validation_errors, 'danger'); // Return validation errors
    }

    // Check for duplicates
    $duplicate_code_error = checkDuplicateSubjectCode($subject_data['subject_code']);
    $duplicate_name_error = checkDuplicateSubjectName($subject_data['subject_name']);

    if ($duplicate_code_error) {
        return renderAlert([$duplicate_code_error], 'danger'); // Return error if subject code is duplicated
    }

    if ($duplicate_name_error) {
        return renderAlert([$duplicate_name_error], 'danger'); // Return error if subject name is duplicated
    }

    // If no errors, insert the subject into the database
    $connection = databaseConnection();
    $query = "INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ss', $subject_data['subject_code'], $subject_data['subject_name']);
    
    if ($stmt->execute()) {
        return renderAlert(["Subject added successfully!"], 'success'); // Return success message
    } else {
        return renderAlert(["Error adding subject. Please try again."], 'danger'); // Return database error message
    }
}

// Function to render alert message (for success or errors)
function renderAlert($messages, $type) {
    $alert_class = $type === 'success' ? 'alert-success' : 'alert-danger';
    $message_html = '<div class="alert ' . $alert_class . '">';
    foreach ($messages as $message) {
        $message_html .= '<p>' . htmlspecialchars($message) . '</p>';
    }
    $message_html .= '</div>';
    return $message_html;
}

// Example usage in the form handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject_data = [
        'subject_code' => trim($_POST['subject_code']),
        'subject_name' => trim($_POST['subject_name'])
    ];

    // Call the function to add the subject
    $message = addNewSubject($subject_data);

    // Display the message (success or error)
    echo $message;
}
function checkDuplicateSubjectData($data) {
    // Assuming databaseConnection() function is defined elsewhere
    $connection = databaseConnection();
    $subject_code = $data['subject_code'];
    
    // Prepare the query to check if the subject code exists in the database
    $query = "SELECT * FROM subjects WHERE subject_code = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();

    // Return error message if duplicate subject code is found
    if ($result->num_rows > 0) {
        return "Subject Code already exists.";
    }

    return ''; // No error, proceed with insertion
}
// Verify student data for required fields
function verifyStudentData($data) {
    $validation_errors = [];

    if (empty($data['id_number'])) {
        $validation_errors[] = "Student ID is required.";
    }

    if (empty($data['first_name'])) {
        $validation_errors[] = "First Name is required.";
    }

    if (empty($data['last_name'])) {
        $validation_errors[] = "Last Name is required.";
    }

    return $validation_errors;
}

// Check if student ID already exists in the database
function isStudentIdDuplicate($data) {
    $db = databaseConnection();
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $data['id_number']);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        return "This Student ID is already taken.";
    }

    return '';
}
function logout_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Start the session if not already started
    }
    session_destroy(); // Destroy the session
    header("Location:../index.php"); // Redirect to root login page
    exit();
}
// Generate a unique student ID based on the max ID in the database
function createUniqueStudentId() {
    $db = databaseConnection();
    $query = "SELECT MAX(id) AS current_max FROM students";
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    $db->close();
    
    return ($data['current_max'] ?? 0) + 1;
}

// Sanitize student ID for use in the database (limit to the first 4 characters)
function sanitizeStudentId($id) {
    return substr($id, 0, 4);
}

// Display alert messages
function displayAlert($messages, $alertType = 'danger') {
    if (!$messages) {
        return '';
    }

    $messages = (array) $messages; // Ensure messages is always an array
    $alertHTML = '<div class="alert alert-' . htmlspecialchars($alertType) . ' alert-dismissible fade show" role="alert">';
    $alertHTML .= '<ul>';
    
    foreach ($messages as $message) {
        $alertHTML .= '<li>' . htmlspecialchars($message) . '</li>';
    }

    $alertHTML .= '</ul>';
    $alertHTML .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $alertHTML .= '</div>';

    return $alertHTML;
}

// Fetch the count of students who passed (average grade >= 75)
function getPassedStudentsCount($connection) {
    $query = "
        SELECT COUNT(*) AS passed_count
        FROM (
            SELECT student_id, AVG(grade) AS avg_grade
            FROM students_subjects
            WHERE grade IS NOT NULL
            GROUP BY student_id
            HAVING avg_grade >= 75
        ) AS passed_students";
    
    $result = $connection->query($query);
    return $result->fetch_assoc()['passed_count'] ?? 0;
}

// Fetch the count of students who failed (average grade < 75)
function getFailedStudentsCount($connection) {
    $query = "
        SELECT COUNT(*) AS failed_count
        FROM (
            SELECT student_id, AVG(grade) AS avg_grade
            FROM students_subjects
            WHERE grade IS NOT NULL
            GROUP BY student_id
            HAVING avg_grade < 75
        ) AS failed_students";
    
    $result = $connection->query($query);
    return $result->fetch_assoc()['failed_count'] ?? 0;
}

// Fetch the total number of registered students
function getTotalStudents($db) {
    $query = "SELECT COUNT(*) AS total_students FROM students";
    $result = $db->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row['total_students'];
    }

    return 0;
}

// Fetch the total number of subjects from the database
function getTotalSubjects($db) {
    $query = "SELECT COUNT(*) AS total_subjects FROM subjects";
    $result = $db->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row['total_subjects'];
    }

    return 0;
}
/**
 * Fetch the data of a specific student by their ID.
 *
 * @param int $student_id The ID of the student to fetch.
 * @return array|null The student data as an associative array, or null if not found.
 */
function getStudentDataById(int $student_id): ?array {
    // Establish the database connection
    $connection = databaseConnection();
    if ($connection === false) {
        // Handle connection failure (Optional: log the error)
        return null;
    }

    // Prepare the SQL query to fetch student data by ID
    $query = "SELECT * FROM students WHERE id = ?";
    if ($stmt = $connection->prepare($query)) {
        // Bind the student ID parameter to the query
        $stmt->bind_param('i', $student_id);

        // Execute the query
        $stmt->execute();

        // Get the result of the query
        $result = $stmt->get_result();

        // Fetch the student data as an associative array
        $student = $result->fetch_assoc();

        // Clean up and close the statement and connection
        $stmt->close();
        $connection->close();

        // Return the student data, or null if not found
        return $student ?: null;
    } else {
        // Handle statement preparation failure (Optional: log the error)
        $connection->close();
        return null;
    }
}


?>  