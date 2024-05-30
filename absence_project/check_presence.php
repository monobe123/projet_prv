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
$students = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    $class_id = $_POST['class_id'];
    $sql = "SELECT id, first_name, last_name, date_of_birth FROM Students WHERE class_id='$class_id'";
    $students_result = $conn->query($sql);
    if ($students_result->num_rows > 0) {
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_presence'])) {
    $date = $_POST['date'];
    $class_id = $_POST['class_id'];
    $students_sql = "SELECT id FROM Students WHERE class_id='$class_id'";
    $students_result = $conn->query($students_sql);
    if ($students_result->num_rows > 0) {
        while ($student_row = $students_result->fetch_assoc()) {
            $student_id = $student_row['id'];
            $status = $_POST['status_' . $student_id];
            if ($status == 'absent') {
                // Insert the absence into the Absences table
                $sql = "INSERT INTO Absences (student_id, date) VALUES ('$student_id', '$date')";
                $conn->query($sql);

                // Update the absence count in the Students table
                $update_absence_count = "UPDATE Students SET absence_count = absence_count + 1 WHERE id='$student_id'";
                $conn->query($update_absence_count);
            }
        }
    }
    // Optionally, you can redirect to another page or show a success message
    header("Location: check_presence.php?class_id=$class_id&date=$date&success=1");
    exit();
}

$sql = "SELECT * FROM Classes";
$classes_result = $conn->query($sql);
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

    <title>Check Student Presence</title>
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
                    <i class="uil uil-check-circle"></i>
                    <span class="text">Check Student Presence</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <label for="class_id">Class:</label>
                        <select name="class_id" id="class_id" required>
                            <?php while ($row = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php if(isset($class_id) && $class_id == $row['id']) echo 'selected'; ?>><?php echo $row['class_name']; ?></option>
                            <?php endwhile; ?>
                        </select><br>
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>"><br>
                        <button type="submit" name="confirm" class="create-class-button">Confirm</button>
                    </form>
                </div>

                <?php if (!empty($students)): ?>
                <div class="title">
                    <i class="uil uil-list-ul"></i>
                    <span class="text">Students List</span>
                </div>

                <form method="post" action="">
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <input type="hidden" name="date" value="<?php echo $_POST['date']; ?>">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Date of Birth</th>
                                <th>Mark Presence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                    <td><?php echo $student['date_of_birth']; ?></td>
                                    <td>
                                        <input type="radio" name="status_<?php echo $student['id']; ?>" value="present" required> Present
                                        <input type="radio" name="status_<?php echo $student['id']; ?>" value="absent" required> Absent
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="mark_presence" class="create-class-button">Mark Presence</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <p>Presence successfully marked!</p>
    <?php endif; ?>
    
    <script src="script.js"></script>
</body>
</html>
