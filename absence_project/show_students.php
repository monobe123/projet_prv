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

$class_id = '';
$class_name = '';
$students = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['show_students'])) {
        $class_id = $_POST['class_id'];
        $sql = "SELECT id, first_name, last_name, date_of_birth FROM Students WHERE class_id='$class_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        } else {
            $message = "No students found for the selected class.";
        }
        $class_result = $conn->query("SELECT class_name FROM Classes WHERE id='$class_id'");
        if ($class_result->num_rows > 0) {
            $class_row = $class_result->fetch_assoc();
            $class_name = $class_row['class_name'];
        }
    } elseif (isset($_POST['export_students'])) {
        $class_id = $_POST['class_id'];
        $sql = "SELECT first_name, last_name, date_of_birth FROM Students WHERE class_id='$class_id'";
        $result = $conn->query($sql);

        $class_result = $conn->query("SELECT class_name FROM Classes WHERE id='$class_id'");
        if ($class_result->num_rows > 0) {
            $class_row = $class_result->fetch_assoc();
            $class_name = $class_row['class_name'];
        }

        $filename = "student_list.csv";
        $file = fopen($filename, "w");

        fputcsv($file, [$class_name]);
        fputcsv($file, ["First Name", "Last Name", "Date of Birth"]);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($file, $row);
            }
        }

        fclose($file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        unlink($filename);
        exit;
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

    <title>Show Students</title>
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
                    <i class="uil uil-eye"></i>
                    <span class="text">Show Students</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="show_students" value="1">
                        <label for="class_id">Select Class:</label>
                        <select id="class_id" name="class_id" required>
                            <?php while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name']; ?></option>
                            <?php endwhile; ?>
                        </select><br>
                        <button type="submit" class="create-class-button">Show Students</button>
                        <br>
                    <br>
                        <p><?php echo $message; ?></p>
                    </form>
                </div>

                <?php if (!empty($students)): ?>
                <div class="title">
                    <i class="uil uil-list-ul"></i>
                    <span class="text">Students List</span>
                </div>

                <table class="students-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date of Birth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['first_name']; ?></td>
                                <td><?php echo $student['last_name']; ?></td>
                                <td><?php echo $student['date_of_birth']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <form method="post" action="">
                    <input type="hidden" name="export_students" value="1">
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <button type="submit" class="create-class-button">download excel</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
