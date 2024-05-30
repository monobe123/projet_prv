<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli('localhost', 'root', '', 'absencce_project');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$class_id = '';
$student_id = '';
$full_name = '';
$num_absences = 0;
$absences = [];
$students = [];
$filter_option = '';
$filter_value = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['class_id'])) {
        $class_id = $_POST['class_id'];

        // Fetch students based on the selected class
        $sql_students = "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM Students WHERE class_id='$class_id'";
        $result_students = $conn->query($sql_students);
        if ($result_students->num_rows > 0) {
            while ($row = $result_students->fetch_assoc()) {
                $students[] = $row;
            }
        }
    }

    if (isset($_POST['student_id'])) {
        $student_id = $_POST['student_id'];
        $filter_option = isset($_POST['filter_option']) ? $_POST['filter_option'] : '';
        $filter_value = isset($_POST['filter_value']) ? $_POST['filter_value'] : '';

        $condition = '';
        if ($filter_option === 'specific_date' && !empty($filter_value)) {
            $condition = "AND DATE(a.date) = '$filter_value'";
        }

        // Fetch absences for the selected student with the filter
        $sql_absences = "SELECT CONCAT(s.first_name, ' ', s.last_name) AS full_name, COUNT(*) as num_absences, GROUP_CONCAT(a.date ORDER BY a.date) as dates 
                         FROM Absences a 
                         JOIN Students s ON a.student_id = s.id
                         WHERE a.student_id='$student_id' $condition
                         GROUP BY s.id";

        $result_absences = $conn->query($sql_absences);
        if ($result_absences === false) {
            echo "Error: " . $conn->error;
        } else if ($result_absences->num_rows > 0) {
            $row = $result_absences->fetch_assoc();
            $full_name = $row['full_name'];
            $num_absences = $row['num_absences'];
            $absences = explode(",", $row['dates']);
        } else {
            echo "No absences found.<br>";
        }
    }

    if (isset($_POST['export_csv'])) {
        $class_id = $_POST['class_id'];
        $student_id = $_POST['student_id'];
        $filter_option = $_POST['filter_option'];
        $filter_value = $_POST['filter_value'];

        $condition = '';
        if ($filter_option === 'specific_date' && !empty($filter_value)) {
            $condition = "AND DATE(a.date) = '$filter_value'";
        }

        $sql_absences = "SELECT CONCAT(s.first_name, ' ', s.last_name) AS full_name, COUNT(*) as num_absences, GROUP_CONCAT(a.date ORDER BY a.date) as dates 
                         FROM Absences a 
                         JOIN Students s ON a.student_id = s.id
                         WHERE a.student_id='$student_id' $condition
                         GROUP BY s.id";
        $result_absences = $conn->query($sql_absences);
        if ($result_absences === false) {
            echo "Error: " . $conn->error;
        } else if ($result_absences->num_rows > 0) {
            $row = $result_absences->fetch_assoc();
            $full_name = $row['full_name'];
            $num_absences = $row['num_absences'];
            $absences = explode(",", $row['dates']);
        }

        $filename = "absences_{$full_name}.csv";
        $file = fopen($filename, "w");

        fputcsv($file, ["Student Name", "Total Absences"]);
        fputcsv($file, [$full_name, $num_absences]);
        fputcsv($file, ["Date of Absence"]);

        foreach ($absences as $absence) {
            fputcsv($file, [$absence]);
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
        exit();
    }
}

$sql = "SELECT id, class_name FROM Classes";
$classes_result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <title>View Student Absence</title>
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
                    <span class="text">View Student Absence</span>
                </div>

                <div class="form-container">
                    <form method="post" action="">
                        <label for="class">Select a Class:</label>
                        <select name="class_id" id="class" required>
                            <option value="">Select Class</option>
                            <?php while ($row = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['class_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="create-class-button">Show Students</button>
                    </form>
                </div>

                <?php if (!empty($students)): ?>
                <div class="form-container">
                    <form method="post" action="">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <label for="student">Select a Student:</label>
                        <select name="student_id" id="student" required>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo $student['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <label for="filter_option">Filter:</label>
                        <select name="filter_option" id="filter_option" required>
                            <option value="all">All Absences</option>
                            <option value="specific_date">Specific Date</option>
                        </select><br>
                        <div id="specific_date_input" style="display: none;">
                            <label for="filter_value">Enter Date:</label>
                            <input type="date" id="filter_value" name="filter_value"><br>
                        </div>
                        <button type="submit" class="create-class-button">Show Absences</button>
                        <br><br>
                    </form>
                </div>
                <?php endif; ?>

                <?php if (!empty($num_absences)): ?>
                <div class="title">
                    <i class="uil uil-list-ul"></i>
                    <span class="text"><?php echo $full_name; ?>'s Absences</span>
                </div>

                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Date of Absence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($absences as $absence): ?>
                            <tr>
                                <td><?php echo $absence; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <p>Total Number of Absences: <?php echo $num_absences; ?></p>
                
                <form method="post" action="">
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                    <input type="hidden" name="filter_option" value="<?php echo $filter_option; ?>">
                    <input type="hidden" name="filter_value" value="<?php echo $filter_value; ?>">
                    <button type="submit" name="export_csv" class="create-class-button">Export</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('filter_option').addEventListener('change', function() {
            var specificDateInput = document.getElementById('specific_date_input');
            if (this.value === 'specific_date') {
                specificDateInput.style.display = 'block';
            } else {
                specificDateInput.style.display = 'none';
            }
        });
    </script>
</body>
</html>
