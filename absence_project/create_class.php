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

$class_name = '';
$message = '';
$delete_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_class'])) {
        $class_name = $_POST['class_name'];
        $sql = "INSERT INTO Classes (class_name) VALUES ('$class_name')";
        if ($conn->query($sql) === TRUE) {
            $message = "Class created successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['delete_class'])) {
        $class_id = $_POST['class_id'];
        
        // Delete related absences first
        $sql_delete_absences = "DELETE FROM Absences WHERE student_id IN (SELECT id FROM Students WHERE class_id = '$class_id')";
        if ($conn->query($sql_delete_absences) === TRUE) {
            // Then delete the students
            $sql_delete_students = "DELETE FROM Students WHERE class_id = '$class_id'";
            if ($conn->query($sql_delete_students) === TRUE) {
                // Finally, delete the class
                $sql_delete_class = "DELETE FROM Classes WHERE id = '$class_id'";
                if ($conn->query($sql_delete_class) === TRUE) {
                    $delete_message = "Class and its students deleted successfully!";
                } else {
                    $delete_message = "Error: " . $conn->error;
                }
            } else {
                $delete_message = "Error: " . $conn->error;
            }
        } else {
            $delete_message = "Error: " . $conn->error;
        }
    }
}

$classes_result = $conn->query("SELECT id, class_name FROM Classes");

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

    <title>Create and Delete Class</title>
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
                    <span class="text">Create Class</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="create_class" value="1">
                        <label for="class_name">Class Name:</label>
                        <input type="text" id="class_name" name="class_name" required><br>
                        <button type="submit" class="create-class-button">Create Class</button>
                        <br>
                        <br>
                        <p><?php echo $message; ?></p>
                    </form>
                </div>

                <div class="title">
                    <i class="uil uil-trash-alt"></i>
                    <span class="text">Delete Class</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="delete_class" value="1">
                        <label for="class_id">Select Class to Delete:</label>
                        <select id="class_id" name="class_id" required>
                            <?php while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
                            <?php endwhile; ?>
                        </select><br>
                        <button type="submit" class="delete-class-button">Delete Class</button>
                        <p><?php echo $delete_message; ?></p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
