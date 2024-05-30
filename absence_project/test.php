<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'absencce_project');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get teacher name
$username = $_SESSION['username'];
$sql = "SELECT name FROM Teachers WHERE username='$username'";
$result = $conn->query($sql);
$name = ($result->num_rows > 0) ? $result->fetch_assoc()['name'] : "Unknown";

// Get total number of students
$sql = "SELECT COUNT(*) AS total_students FROM Students";
$result = $conn->query($sql);
$num_students = ($result->num_rows > 0) ? $result->fetch_assoc()['total_students'] : 0;

// Get total number of classes
$sql = "SELECT COUNT(*) AS total_classes FROM Classes";
$result = $conn->query($sql);
$num_classes = ($result->num_rows > 0) ? $result->fetch_assoc()['total_classes'] : 0;

// Get total number of absences
$sql = "SELECT COUNT(*) AS total_absences FROM Absences";
$result = $conn->query($sql);
$num_absences = ($result->num_rows > 0) ? $result->fetch_assoc()['total_absences'] : 0;

// Close the database connection
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
    <title>Teacher Dashboard Panel</title>
    <style>
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            font-size: 36px;
            font-weight: bold;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .stat-item .text {
            font-size: 24px;
        }
        .stat-item .number {
            font-size: 36px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
               <img src="images/logo.png" alt="">
            </div>
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
                    <span class="text">Overview</span>
                </div>
                <br>
                <br>

                <div class="stats-container">
                    <div class="stat-item">
                        <i class="uil uil-users-alt icon"></i>
                        <span class="text">Total Students</span>
                        <span class="number"><?php echo $num_students; ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="uil uil-books icon"></i>
                        <span class="text">Total Classes</span>
                        <span class="number"><?php echo $num_classes; ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="uil uil-user-times icon"></i>
                        <span class="text">Total Absences</span>
                        <span class="number"><?php echo $num_absences; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
