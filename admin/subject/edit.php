<?php
include("../../functions.php");
include("../partials/header.php");
include("../partials/side-bar.php");

$errorMessage = null;
$successMessage = null;

// Get subject details if 'id' is provided in the URL
$subject = null;
if (isset($_GET['id'])) {
    $subjectId = $_GET['id'];
    $subject = getSubjectById($subjectId);
    if (!$subject) {
        $errorMessage = "Subject not found!";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = $_POST['id'];
    $updatedSubjectName = trim($_POST['subjectName']);

    // Validate subject name
    if (empty($updatedSubjectName)) {
        $errorMessage = "Subject name is required!";
    } else {
        // Check for duplicates and update if no error
        if (isSubjectNameDuplicate($subjectId, $updatedSubjectName)) {
            $errorMessage = "The subject name already exists!";
        } else {
            if (updateSubject($subjectId, $updatedSubjectName)) {
                header("Location: add.php?message=updated");
                exit();
            } else {
                $errorMessage = "Failed to update subject details!";
            }
        }
    }
}

// Helper functions
function getSubjectById($subjectId) {
    $conn = databaseConnection();
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subject = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
    $stmt->close();
    $conn->close();
    return $subject;
}

function isSubjectNameDuplicate($subjectId, $subjectName) {
    $conn = databaseConnection();
    $checkStmt = $conn->prepare("SELECT * FROM subjects WHERE subject_name = ? AND id != ?");
    $checkStmt->bind_param("si", $subjectName, $subjectId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $isDuplicate = ($checkResult->num_rows > 0);
    $checkStmt->close();
    $conn->close();
    return $isDuplicate;
}

function updateSubject($subjectId, $subjectName) {
    $conn = databaseConnection  ();
    $stmt = $conn->prepare("UPDATE subjects SET subject_name = ? WHERE id = ?");
    $stmt->bind_param("si", $subjectName, $subjectId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <div>
        <h2>Edit Subject</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="add.php">Add Subject</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Subject</li>
            </ol>
        </nav>

        <!-- Alerts -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <?php if ($subject): ?>
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                        <div class="mb-3">
                            <label for="subjectCode" class="form-label">Subject Code</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="subjectCode" 
                                name="subjectCode" 
                                value="<?php echo $subject['subject_code']; ?>" 
                                readonly
                            >
                        </div>
                        <div class="mb-3">
                            <label for="subjectName" class="form-label">Subject Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="subjectName" 
                                name="subjectName" 
                                value="<?php echo $subject['subject_name']; ?>" 
                                placeholder="Enter Subject Name"
                            >
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary w-100">Update Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
include("../partials/footer.php");
?>
