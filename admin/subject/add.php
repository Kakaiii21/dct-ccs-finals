<?php
include("../../functions.php");
$Pagetitle = "Add Subject";
include("../partials/header.php");
include("../partials/side-bar.php");

$errorMessage = null; // Variable to store error messages
$subjectCode = ''; // Store subject code for form retention
$subjectName = ''; // Store subject name for form retention

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectCode = trim($_POST['subjectCode']);
    $subjectName = trim($_POST['subjectName']);

    // Validate input fields
    if (empty($subjectCode) || empty($subjectName)) {
        $errorMessage = "All fields are required!";
    } else {
        $conn = databaseConnection();

        // Check for duplicate subject code
        $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_code = ?");
        $stmt->bind_param("s", $subjectCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "A subject with this code already exists!";
        } else {
            // Check for duplicate subject name
            $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_name = ?");
            $stmt->bind_param("s", $subjectName);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errorMessage = "A subject with this name already exists!";
            } else {
                // Insert new subject into the database
                $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
                $stmt->bind_param("ss", $subjectCode, $subjectName);
                $stmt->execute();

                // Clear the form fields after successful submission
                $subjectCode = '';
                $subjectName = '';
            }
        }

        // Close database connection
        $stmt->close();
        $conn->close();
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <div>
        <h2>Add a New Subject</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Subject</li>
            </ol>
        </nav>

        <!-- Display error message if any -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Subject registration form -->
        <div class="card">
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="subjectCode" class="form-label">Subject Code</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="subjectCode" 
                            name="subjectCode" 
                            placeholder="Enter Subject Code" 
                            value="<?php echo htmlspecialchars($subjectCode); ?>"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="subjectName" class="form-label">Subject Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="subjectName" 
                            name="subjectName" 
                            placeholder="Enter Subject Name" 
                            value="<?php echo htmlspecialchars($subjectName); ?>"
                        >
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary w-100">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Display list of existing subjects -->
        <div class="card mt-4">
            <div class="card-header">Subject List</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Subject Code</th>
                            <th scope="col">Subject Name</th>
                            <th scope="col">Option</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch and display all subjects from the database
                        $conn = databaseConnection();
                        $result = $conn->query("SELECT * FROM subjects ORDER BY id ASC");

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['subject_code']}</td>
                                    <td>{$row['subject_name']}</td>
                                    <td>
                                        <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-info'>Edit</a>
                                        <a href='delete.php?id={$row['id']}' class='btn btn-sm btn-danger'>Delete</a>
                                    </td>
                                </tr>";
                        }

                        // Close connection
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php 
include("../partials/footer.php");
?>
