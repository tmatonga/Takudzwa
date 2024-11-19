<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineapp";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $role = $_POST['role'];
    $sql = "INSERT INTO Users (username, password, email, role) VALUES ('$username', '$password', '$email', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM Users WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            echo "Login successful!";
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }
}

if (isset($_POST['book_appointment'])) {
    $user_id = $_SESSION['user_id'];
    $provider_id = $_POST['provider_id'];
    $appointment_date = $_POST['appointment_date'];
    $time_slot = $_POST['time_slot'];
    $sql = "SELECT * FROM Appointments WHERE provider_id='$provider_id' AND appointment_date='$appointment_date' AND time_slot='$time_slot'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        $sql = "INSERT INTO Appointments (user_id, provider_id, appointment_date, time_slot, status) VALUES ('$user_id', '$provider_id', '$appointment_date', '$time_slot', 'booked')";
        if ($conn->query($sql) === TRUE) {
            echo "Appointment booked successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Selected slot is already booked!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Appointment System</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; }
        .form-section { margin-top: 20px; padding: 10px; border: 1px solid #ccc; }
        input, select, button { display: block; width: 100%; margin: 10px 0; padding: 10px; }
    </style>
	<link rel="stylesheet" href="assign1.css">
</head>
<body>
<div class="container">
    <h1>Online Appointment Scheduling System</h1>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="form-section">
            <h2>Register</h2>
            <form method="POST">
                <input type="text" name="username" required placeholder="Username">
                <input type="password" name="password" required placeholder="Password">
                <input type="email" name="email" required placeholder="Email">
                <select name="role">
                    <option value="client">Client</option>
                    <option value="provider">Provider</option>
                </select>
                <button type="submit" name="register">Register</button>
            </form>
        </div>
        <div class="form-section">
            <h2>Login</h2>
            <form method="POST">
                <input type="text" name="username" required placeholder="Username">
                <input type="password" name="password" required placeholder="Password">
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    <?php else: ?>
        <p>Welcome! <a href="?logout">Logout</a></p>
        <?php if ($_SESSION['role'] == 'client'): ?>
            <div class="form-section">
                <h2>Book an Appointment</h2>
                <form method="POST">
                    <label>Select Provider:</label>
                    <select name="provider_id">
                        <?php
                        $sql = "SELECT * FROM Service_Providers";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='".$row['id']."'>".$row['name']."</option>";
                        }
                        ?>
                    </select>
                    <label>Date:</label>
                    <input type="date" name="appointment_date" required>
                    <label>Time Slot:</label>
                    <select name="time_slot">
                        <option value="9:00-10:00">9:00 - 10:00</option>
                        <option value="10:00-11:00">10:00 - 11:00</option>
                        <option value="11:00-12:00">11:00 - 12:00</option>
                    </select>
                    <button type="submit" name="book_appointment">Book Appointment</button>
                </form>
            </div>
            <div class="form-section">
                <h2>Your Appointments</h2>
                <table border="1">
                    <tr><th>Date</th><th>Time Slot</th><th>Status</th></tr>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $sql = "SELECT * FROM Appointments WHERE user_id='$user_id' ORDER BY appointment_date DESC";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row['appointment_date'] . "</td><td>" . $row['time_slot'] . "</td><td>" . $row['status'] . "</td></tr>";
                    }
                    ?>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    ?>
</div>
</body>
</html>
