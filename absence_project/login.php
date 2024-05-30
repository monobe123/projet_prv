<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['email'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'absencce_project');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM Teachers WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
        header("Location:test.php");
    } else {
        $error = "Incorrect email or password.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
            overflow: hidden;
            background: #fff;
        }

        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            color: #000;
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-form {
            background: rgba(255, 255, 255,0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .login-form:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .login-form h2 {
            margin-bottom: 20px;
            color: #007BFF;
        }

        label {
            display: block;
            margin: 10px 0;
            text-align: left;
            color: #007BFF;
        }

        input {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff; /* Updated to white */
            color: #000;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 5px;
            background: #007BFF;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        input::placeholder {
            color: #999;
        }

        p {
            color: red;
            margin-top: 10px;
        }

        canvas {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
    </style>
</head>
<body>
    <canvas id="lines"></canvas>
    <div class="login-container">
        <div class="login-form">
            <form method="post" action="">
                <h2>Welcome </h2>
                <label>Email:</label>
                <input type="email" name="email" placeholder="Enter your email" required>
                <label>Password:</label>
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Login</button>
                <p><?php echo $error; ?></p>
            </form>
        </div>
    </div>

    <script>
        // Connecting Lines Effect
        const canvasLines = document.getElementById('lines');
        const ctxLines = canvasLines.getContext('2d');

        canvasLines.width = window.innerWidth;
        canvasLines.height = window.innerHeight;

        const points = [];
        for (let i = 0; i < 150; i++) {  // Increased number of points
            points.push({
                x: Math.random() * canvasLines.width,
                y: Math.random() * canvasLines.height,
                vx: (Math.random() - 0.5) * 2,
                vy: (Math.random() - 0.5) * 2
            });
        }

        function drawLines() {
            ctxLines.clearRect(0, 0, canvasLines.width, canvasLines.height);

            for (let i = 0; i < points.length; i++) {
                points[i].x += points[i].vx;
                points[i].y += points[i].vy;

                if (points[i].x > canvasLines.width || points[i].x < 0) points[i].vx *= -1;
                if (points[i].y > canvasLines.height || points[i].y < 0) points[i].vy *= -1;

                for (let j = i + 1; j < points.length; j++) {
                    const dx = points[i].x - points[j].x;
                    const dy = points[i].y - points[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 100) {
                        ctxLines.beginPath();
                        ctxLines.moveTo(points[i].x, points[i].y);
                        ctxLines.lineTo(points[j].x, points[j].y);
                        ctxLines.strokeStyle = `rgba(0, 0, 139, ${1 - distance / 100})`; // dark blue color
                        ctxLines.stroke();
                    }
                }
            }
        }

        setInterval(drawLines, 33);

        window.addEventListener('resize', () => {
            canvasLines.width = window.innerWidth;
            canvasLines.height = window.innerHeight;
        });
    </script>
</body>
</html>
