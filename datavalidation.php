<?php
include 'login.php';

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "Rohan@1234";
$database = "Registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Perform SQL query to check user credentials
    $sql = "SELECT * FROM user WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    // Check if there is a match
    if ($result->num_rows > 0) {
        // Redirect to another page on successful login
        header("Location: loginsesson.html");
        exit();
    } else {
        // Redirect back to login page with error message
        header("Location: login.php?error=1");
        exit();
    }
}

// Close connection
$conn->close();
?>
