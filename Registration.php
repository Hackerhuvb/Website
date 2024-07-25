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

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $profile_image = $_FILES["profile_image"];

    // Handle file upload
    $target_dir = "upload/";
    $target_file = $target_dir . basename($profile_image["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($profile_image["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($profile_image["size"] > 4000000) { // 4MB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($profile_image["tmp_name"], $target_file)) {
            echo "The file ". basename($profile_image["name"]). " has been uploaded.";

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare and bind statement
            $stmt = $conn->prepare("INSERT INTO user (username, email, password, profile_image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $target_file);

            // Execute the statement
            if ($stmt->execute()) {
                echo "New record inserted successfully";
                // Close statement
                $stmt->close();
                // Close connection
                $conn->close();
                // After successful registration, redirect to login page
                header("Location: loginpage.html");
                exit; // Make sure to exit after redirecting 
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Close connection
$conn->close();
?>
