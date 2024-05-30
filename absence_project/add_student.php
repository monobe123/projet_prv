<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'absencce_project');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$first_name = '';
$last_name = '';
$date_of_birth = '';
$class_id = '';
$message = '';
$delete_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_student'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $date_of_birth = $_POST['date_of_birth'];
        $class_id = $_POST['class_id'];
        $sql = "INSERT INTO Students (first_name, last_name, date_of_birth, class_id) VALUES ('$first_name', '$last_name', '$date_of_birth', '$class_id')";
        if ($conn->query($sql) === TRUE) {
            $message = "Student added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['delete_student'])) {
        $student_id = $_POST['student_id'];
        
        // Delete related absences first
        $sql_delete_absences = "DELETE FROM Absences WHERE student_id = '$student_id'";
        if ($conn->query($sql_delete_absences) === TRUE) {
            // Then delete the student
            $sql_delete_student = "DELETE FROM Students WHERE id = '$student_id'";
            if ($conn->query($sql_delete_student) === TRUE) {
                $delete_message = "Student deleted successfully!";
            } else {
                $delete_message = "Error: " . $conn->error;
            }
        } else {
            $delete_message = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['upload_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0 && isset($_POST['class_id'])) {
            $class_id = $_POST['class_id'];
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            while (($data = fgetcsv($file)) !== FALSE) {
                if (count($data) == 3) { // Ensure there are exactly 3 columns
                    $first_name = $conn->real_escape_string($data[0]);
                    $last_name = $conn->real_escape_string($data[1]);
                    $date_of_birth = $conn->real_escape_string($data[2]);

                    // Format the date of birth to Y-m-d if necessary
                    $date_of_birth = date('Y-m-d', strtotime($date_of_birth));

                    $sql = "INSERT INTO Students (first_name, last_name, date_of_birth, class_id) VALUES ('$first_name', '$last_name', '$date_of_birth', '$class_id')";
                    if ($conn->query($sql) === FALSE) {
                        $message = "Error: " . $conn->error;
                    }
                }
            }
            fclose($file);
            if ($message == '') {
                $message = "CSV file uploaded and students added successfully!";
            }
        } else {
            $message = "Error: Please select a class and upload a valid CSV file.";
        }
    } elseif (isset($_POST['delete_all_students'])) {
        if (isset($_POST['class_id'])) {
            $class_id = $_POST['class_id'];
            
            // Delete related absences first
            $sql_delete_absences = "DELETE FROM Absences WHERE student_id IN (SELECT id FROM Students WHERE class_id = '$class_id')";
            if ($conn->query($sql_delete_absences) === TRUE) {
                // Then delete the students
                $sql_delete_students = "DELETE FROM Students WHERE class_id = '$class_id'";
                if ($conn->query($sql_delete_students) === TRUE) {
                    $delete_message = "All students in the class deleted successfully!";
                } else {
                    $delete_message = "Error: " . $conn->error;
                }
            } else {
                $delete_message = "Error: " . $conn->error;
            }
        }
    }
}

$classes_result = $conn->query("SELECT id, class_name FROM Classes");
$students_result = $conn->query("SELECT id, first_name, last_name FROM Students");

$classes = [];
if ($classes_result->num_rows > 0) {
    while ($row = $classes_result->fetch_assoc()) {
        $classes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="style.css">
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <title>Add and Delete Student</title>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete all students in this class?');
        }
    </script>
</head>
<body>
    <nav>
        <div class="logo-name">
            <span class="logo_name">Teacher Dashboard</span>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="test.php">
                    <span class="link-name">Home</span>
                </a></li>
                <li><a href="create_class.php">
                    <span class="link-name">Manage Classes</span>
                </a></li>
                <li><a href="add_student.php">
                    <span class="link-name">Manage Students</span>
                </a></li>
                <li><a href="show_students.php">
                    <span class="link-name">View Class Students</span>
                </a></li>
                <li><a href="check_presence.php">
                    <span class="link-name">Check Student Presence</span>
                </a></li>
                <li><a href="view_absences.php">
                    <span class="link-name">View Student Absence Report</span>
                </a></li>
            </ul>
        </div>

        <ul class="logout-mode">
            <li><a href="login.php">
                <i class="uil uil-signout"></i>
                <span class="link-name">Logout</span>
            </a></li>
        </ul>
    </nav>
    
    <section class="dashboard">
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-tachometer-fast-alt"></i>
                    <span class="text">Add Student</span>
                </div>
                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="add_student" value="1">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required><br>
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required><br>
                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" required><br>
                        <label for="class_id">Class:</label>
                        <select name="class_id" id="class_id" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <button type="submit" class="create-class-button">Add Student</button>
                        <br><br>
                    </form>
                </div>

                <div class="title">
                    <i class="uil uil-file-upload"></i>
                    <span class="text">Upload all Class Student</span>
                </div>

                <div class="form-container">
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="upload_csv" value="1">
                        <label for="class_id_csv">Select Class:</label>
                        <select name="class_id" id="class_id_csv" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <label for="csv_file">Select excel file:</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <button type="submit" class="upload-csv-button">Upload excel file</button>
                    </form><br>
                    <p><?php echo $message; ?></p>
                    <br>
                </div>

                <div class="title">
                    <i class="uil uil-trash-alt"></i>
                    <span class="text">Delete Student</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="delete_student" value="1">
                        <label for="student_id">Select Student to Delete:</label>
                        <select id="student_id" name="student_id" required>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></option>
                            <?php endwhile; ?>
                        </select><br>
                        <button type="submit" class="delete-class-button">Delete Student</button><br>
                        <br>
                        <p><?php echo $delete_message; ?></p>
                    </form><br>
                </div>

                <div class="title">
                    <i class="uil uil-trash-alt"></i>
                    <span class="text">Delete All the Students</span>
                </div>

                <div class="form-container">
                    <form method="post" action="" onsubmit="return confirmDelete();">
                        <input type="hidden" name="delete_all_students" value="1">
                        <label for="class_id_delete">Select Class:</label>
                        <select name="class_id" id="class_id_delete" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <button type="submit" class="delete-all-class-button">Delete All Students</button>
                        <br>
                        <br>
                        <p><?php echo $delete_message; ?></p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
