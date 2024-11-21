<?php
require_once(__DIR__ . '/../../functions.php');
require_once '../partials/header.php'; 
require_once '../partials/side-bar.php';

// Initialize messages
$error_message = '';
$success_message = '';

// Handle Add Subject Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);

    // Validate inputs
    $validation_errors = validateSubject($subject_code, $subject_name);

    if (empty($validation_errors)) {
        // Check for duplicate subject code or name
        $error_message = checkForDuplicates($subject_code, $subject_name);
        if ($error_message === '') {
            $success_message = addNewSubject($subject_code, $subject_name);
        }
    } else {
        $error_message = renderAlert($validation_errors, 'danger');
    }
}

// Fetch all subjects for the list
$subjects = getAllSubjects();

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Add a New Subject</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add a New Subject</li>
        </ol>
    </nav>

    <!-- Display messages -->
    <?php echo $error_message ?: $success_message; ?>

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
            <?php foreach ($subjects as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require_once '../partials/footer.php'; ?>
