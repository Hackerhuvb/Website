<?php
session_start(); // Start or resume session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    
    // Retrieve form data
    $_User = $_POST["Username"];
    $_password = $_POST["password"];

    // Prepare and execute SQL statement to select user with provided username
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $_User);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        // Fetch user's data
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($_password, $row['password'])) {
            // Password is correct, set session variable
            $_SESSION['id'] = $row['id'];
            
            // Redirect to main dashboard
            header("Location: userdeskboard.php");
            exit;
        } else {
            // Incorrect password
            echo "Incorrect password.";
        }
    } else {
        // User not found
        echo "User not found.";
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['Username'];
        $password = $_POST['password'];
        $recaptchaResponse = $_POST['g-recaptcha-response'];
    
        // Verify the reCAPTCHA response
        $secretKey = 'YOUR_SECRET_KEY';
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
        $responseData = json_decode($verifyResponse);
    
        if ($responseData->success) {
            // Proceed with user authentication
            $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
            $result = mysqli_query($conn, $query);
    
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $_SESSION["id"] = $row["id"];
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Invalid username or password.";
            }
        } else {
            echo "reCAPTCHA verification failed. Please try again.";
        }
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
}
?>
