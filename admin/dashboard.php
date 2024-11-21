<?php 
require_once '../admin/partials/header.php'; 
require_once '../admin/partials/side-bar.php';
require_once '../functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$connection = databaseConnection(); // Use the new connection function

// Fetch the number of subjects
$subject_count_query = "SELECT COUNT(*) as subject_count FROM subjects";
$subject_result = $connection->query($subject_count_query);
$subject_count = 0;
if ($subject_result && $row = $subject_result->fetch_assoc()) {
    $subject_count = $row['subject_count'];
}

// Fetch the number of students
$student_count_query = "SELECT COUNT(*) as student_count FROM students";
$student_result = $connection->query($student_count_query);
$student_count = 0;
if ($student_result && $row = $student_result->fetch_assoc()) {
    $student_count = $row['student_count'];
}

// Query to fetch the number of students who failed based on their average grades
$failed_students_query = "
    SELECT COUNT(*) AS failed_students
    FROM (
        SELECT 
            students.id AS student_id,
            AVG(students_subjects.grade) AS average_grade
        FROM students
        LEFT JOIN students_subjects ON students.id = students_subjects.student_id
        GROUP BY students.id
        HAVING average_grade < 75
    ) AS failed";
$failed_students = 0;
$failed_students_result = $connection->query($failed_students_query);
if ($failed_students_result && $row = $failed_students_result->fetch_assoc()) {
    $failed_students = $row['failed_students'];
}

// Query to fetch the number of students who passed based on their average grades
$passed_students_query = "
    SELECT COUNT(*) AS passed_students
    FROM (
        SELECT 
            students.id AS student_id,
            AVG(students_subjects.grade) AS average_grade
        FROM students
        LEFT JOIN students_subjects ON students.id = students_subjects.student_id
        GROUP BY students.id
        HAVING average_grade >= 75
    ) AS passed";
$passed_students = 0;
$passed_students_result = $connection->query($passed_students_query);
if ($passed_students_result && $row = $passed_students_result->fetch_assoc()) {
    $passed_students = $row['passed_students'];
}
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
