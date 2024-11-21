<?php
ob_start(); // Start output buffering

include("../../functions.php");
include("../partials/header.php");
include("../partials/side-bar.php");

$errorMessage = null;
$successMessage = null;

// Check if ID is passed in the URL
if (isset($_GET['id'])) {
    $subjectId = $_GET['id'];

    // Establish database connection
    $conn = databaseConnection();
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $subject = $result->fetch_assoc();
    } else {
        $errorMessage = "Subject not found!";
    }

    $stmt->close();
    $conn->close();
}

// Handle form submission to delete the subject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $subjectIdToDelete = $_POST['id'];

        // Establish database connection again for deletion
        $conn = databaseConnection();
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $subjectIdToDelete);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: add.php?message=deleted"); // Redirect after successful deletion
            exit(); // Ensure script stops after redirect
        } else {
            $errorMessage = "Failed to delete the subject record!";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <div>
        <h2>Delete Subject</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="add-subject.php">Add Subject</a></li>
                <li class="breadcrumb-item active" aria-current="page">Delete Subject</li>
            </ol>
        </nav>

        <!-- Alerts -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Confirmation Form -->
        <?php if (isset($subject)): ?>
            <div class="card">
                <div class="card-body">
                    <p>Are you sure you want to delete the following subject record?</p>
                    <ul>
                        <li><strong>Subject Code:</strong> <?php echo $subject['subject_code']; ?></li>
                        <li><strong>Subject Name:</strong> <?php echo $subject['subject_name']; ?></li>
                    </ul>
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Delete Subject Record</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
include("../partials/footer.php");
ob_end_flush(); // End output buffering and send output

?>
