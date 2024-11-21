<?php
require_once(__DIR__ . '/../../functions.php');
require_once '../partials/header.php'; 
require_once '../partials/side-bar.php';

// Initialize variables
$alert_message = '';  // Consolidated alert message

// Handle Add Subject Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);

    // Validate inputs
    $errors = validateSubjectInputs($subject_code, $subject_name);

    if (empty($errors)) {
        // Check for duplicate subject code or name
        $duplicate_code_error = checkDuplicateSubjectData(['subject_code' => $subject_code]);
        $duplicate_name_error = checkDuplicateSubjectName($subject_name);

        if (!empty($duplicate_code_error) || !empty($duplicate_name_error)) {
            $alert_message = renderAlert(
                array_filter([$duplicate_code_error, $duplicate_name_error]), 
                'danger'
            );
        } else {
            // Insert new subject into the database
            $connection = databaseConnection();
            $query = "INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ss', $subject_code, $subject_name);

            if ($stmt->execute()) {
                $alert_message = renderAlert(["Subject added successfully!"], 'success');
                // Clear the fields after successful submission
                $subject_code = '';
                $subject_name = '';
            } else {
                $alert_message = renderAlert(["Error adding subject. Please try again."], 'danger');
            }
        }
    } else {
        $alert_message = renderAlert($errors, 'danger');
    }
}

// Fetch subjects to display in the list
$connection = databaseConnection();
$query = "SELECT * FROM subjects";
$result = $connection->query($query);

// Function to validate subject inputs
function validateSubjectInputs($subject_code, $subject_name) {
    $errors = [];
    if (empty($subject_code)) {
        $errors[] = "Subject Code is required.";
    } elseif (strlen($subject_code) > 4) {
        $errors[] = "Subject Code cannot be longer than 4 characters.";
    }
    if (empty($subject_name)) {
        $errors[] = "Subject Name is required.";
    }
    return $errors;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Add a New Subject</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add a New Subject</li>
        </ol>
    </nav>

    <!-- Display a single alert message between breadcrumb and the form -->
    <?php if (!empty($alert_message)): ?>
        <?php echo $alert_message; ?>
    <?php endif; ?>

    <!-- Add Subject Form -->
    <form method="post" action="">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="subject_code" name="subject_code" placeholder="Subject Code" value="<?php echo htmlspecialchars($subject_code ?? ''); ?>">
            <label for="subject_code">Subject Code</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="subject_name" name="subject_name" placeholder="Subject Name" value="<?php echo htmlspecialchars($subject_name ?? ''); ?>">
            <label for="subject_name">Subject Name</label>
        </div>
        <div class="mb-3">
            <button type="submit" name="add_subject" class="btn btn-primary w-100">Add Subject</button>
        </div>
    </form>

    <!-- Subject List -->
    <h3 class="mt-5">Subject List</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Option</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php require_once '../partials/footer.php'; ?>
