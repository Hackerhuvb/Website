<?php
$servername = "localhost";
$username = "root";
$password = "Rohan@1234";
$dbname = "Registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>