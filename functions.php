  
<?php    
function databaseConnection(): mysqli {
   $server = 'localhost';
   $username = 'root';       
   $password = "";            
   $dbname = 'dct-ccs-finals'; 

   
   $conn = new mysqli($server, $username, $password, $dbname);

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
function StudentIdDuplicate($data) {
    $db = databaseConnection();
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $data['id_number']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        return "This Student ID is already taken.";
    }

    return ''; // No duplicate found
}

// Function to generate a unique student ID (you can customize it based on your needs)
function createUniqueStudentId() {
    return uniqid(); // Generates a unique ID
}

// Function to verify student data (customize validation as per your needs)
function verifyStudent($student) {
    $errors = [];

    // Check if any field is empty
    if (empty($student['id_number'])) {
        $errors[] = "Student ID is required.";
    }
    if (empty($student['first_name'])) {
        $errors[] = "First name is required.";
    }
    if (empty($student['last_name'])) {
        $errors[] = "Last name is required.";
    }

    // Add more validation rules as necessary

    return $errors;
}

// Function to display alert messages
function displayAlert($messages, $type) {
    $alert = '<div class="alert alert-' . $type . '">';
    foreach ($messages as $message) {
        $alert .= '<p>' . htmlspecialchars($message) . '</p>';
    }
    $alert .= '</div>';
    return $alert;
}

// Helper function to get the student ID prefix (customize as needed)
function getStudentIdPrefix($student_id) {
    // Example logic: Ensure the ID starts with "STU-" followed by the provided ID
    return 'STU-' . strtoupper($student_id);
}

// Function to fetch all students from the database
function fetchAllStudents($db) {
    $fetch_query = "SELECT * FROM students";
    return $db->query($fetch_query);
}

?>  