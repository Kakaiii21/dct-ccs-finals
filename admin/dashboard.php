<?php 
require_once '../admin/partials/header.php'; 
require_once '../admin/partials/side-bar.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$connection = databaseConnection();

// Fetch the number of subjects
$subject_count = getTotalSubjects($connection);

// Fetch the number of students
$student_count = getTotalStudents($connection);

// Fetch the number of students who failed based on their average grades
$failed_students = getFailedStudentsCount($connection);

// Fetch the number of students who passed based on their average grades
$passed_students = getPassedStudentsCount($connection);
?>

<!-- Template Files here -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">    
    <h1 class="h2">Dashboard</h1>        
    
    <div class="row mt-5">
        <div class="col-12 col-xl-3">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white border-primary">Number of Subjects:</div>
                <div class="card-body text-primary">
                    <h5 class="card-title"><?php echo htmlspecialchars($subject_count); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white border-primary">Number of Students:</div>
                <div class="card-body text-primary">
                    <h5 class="card-title"><?php echo htmlspecialchars($student_count); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white border-danger">Number of Failed Students:</div>
                <div class="card-body text-danger">
                    <h5 class="card-title"><?php echo htmlspecialchars($failed_students); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white border-success">Number of Passed Students:</div>
                <div class="card-body text-success">
                    <h5 class="card-title"><?php echo htmlspecialchars($passed_students); ?></h5>
                </div>
            </div>
        </div>
    </div>    
</main>
<!-- Template Files here -->
<?php require_once '../admin/partials/footer.php'; ?>
