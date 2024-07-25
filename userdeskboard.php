<?php
require 'connection.php';
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.html");
    exit();
} else {
    $sessionId = $_SESSION["id"];
}

$queryUser = "SELECT * FROM user WHERE id = '$sessionId'";
$resultUser = mysqli_query($conn, $queryUser);

if (!$resultUser) {
    die("User Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($resultUser);

if ($user) {
    $id = $user["id"];
    $name = $user["username"];
    $email = $user["email"];
    $image = $user["image"] ?? 'noprofil.jpg';
} else {
    $id = 0;
    $name = 'Default Name';
    $email = 'default@example.com';
    $image = 'noprofil.jpg';
}

$imageUploadSuccess = false;

if (isset($_FILES["image"]["name"])) {
    $idUpload = $sessionId;
    $nameUpload = '';
    $imageName = $_FILES["image"]["name"];
    $imageSize = $_FILES["image"]["size"];
    $tmpName = $_FILES["image"]["tmp_name"];

    $validImageExtensions = ['jpg', 'jpeg', 'png'];
    $imageExtension = explode('.', $imageName);
    $imageExtension = strtolower(end($imageExtension));
    if (!in_array($imageExtension, $validImageExtensions)) {
        echo '<script>alert("Invalid Image Extension");</script>';
    } elseif ($imageSize > 1200000) {
        echo '<script>alert("Image size is too large");</script>';
    } else {
        $newImageName = $idUpload . "-" . date("Y-m-d") . "-" . date("h-i-sa") . "." . $imageExtension;

        $directory = 'updateimageprofile/';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $moveResult = move_uploaded_file($tmpName, $directory . $newImageName);

        if (!$moveResult) {
            die("Failed to move uploaded file.");
        }

        $query = "INSERT INTO tb_upload (id, image) VALUES (?, ?) ON DUPLICATE KEY UPDATE image = ?";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'iss', $idUpload, $newImageName, $newImageName);
        $execResult = mysqli_stmt_execute($stmt);

        if (!$execResult) {
            die("Execute failed: " . mysqli_stmt_error($stmt));
        }

        $image = $newImageName;
        $imageUploadSuccess = true;
    }
}

$contentUpdateSuccess = false;

if (isset($_POST['add_content'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    $query = "INSERT INTO carousel_content (title, content) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $title, $content);
        $execResult = mysqli_stmt_execute($stmt);

        if (!$execResult) {
            echo "Error inserting content: " . mysqli_stmt_error($stmt);
        } else {
            $contentUpdateSuccess = true;
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }

    if (!empty($_FILES['new_carousel_image']['name'])) {
        $imageName = $_FILES["new_carousel_image"]["name"];
        $imageSize = $_FILES["new_carousel_image"]["size"];
        $tmpName = $_FILES["new_carousel_image"]["tmp_name"];

        $imageExtension = explode('.', $imageName);
        $imageExtension = strtolower(end($imageExtension));
        if (!in_array($imageExtension, ['jpg', 'jpeg', 'png'])) {
            echo '<script>alert("Invalid Image Extension for Carousel Image");</script>';
        } elseif ($imageSize > 1200000) {
            echo '<script>alert("Carousel Image size is too large");</script>';
        } else {
            $newImageName = "carousel-" . date("Y-m-d") . "-" . date("h-i-sa") . "." . $imageExtension;

            $directory = 'carousel_images/';
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $moveResult = move_uploaded_file($tmpName, $directory . $newImageName);

            if (!$moveResult) {
                die("Failed to move uploaded carousel image.");
            }

            $query = "INSERT INTO carousel_images (image) VALUES (?)";
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $newImageName);
                $execResult = mysqli_stmt_execute($stmt);

                if (!$execResult) {
                    echo "Error inserting carousel image: " . mysqli_stmt_error($stmt);
                } else {
                    $contentUpdateSuccess = true;
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Error preparing statement: " . mysqli_error($conn);
            }
        }
    }
}

$queryUpload = "SELECT image FROM tb_upload WHERE id = '$sessionId'";
$resultUpload = mysqli_query($conn, $queryUpload);
$imageRow = mysqli_fetch_assoc($resultUpload);

if ($imageRow) {
    $image = $imageRow['image'];
}

$imagePath = 'updateimageprofile/' . $image;
if (!file_exists($imagePath)) {
    $imagePath = 'noprofil.jpg';
}

$updateTimeQuery = "UPDATE user SET last_login = NOW() WHERE id = '$sessionId'";
mysqli_query($conn, $updateTimeQuery);

$timeQuery = "SELECT last_login, last_logout FROM user WHERE id = '$sessionId'";
$timeResult = mysqli_query($conn, $timeQuery);
$timeRow = mysqli_fetch_assoc($timeResult);

$lastLogin = $timeRow['last_login'] ?? 'Not available';
$lastLogout = $timeRow['last_logout'] ?? 'Not available';

if (isset($_POST['logout'])) {
    $updateLogoutTimeQuery = "UPDATE user SET last_logout = NOW() WHERE id = '$sessionId'";
    mysqli_query($conn, $updateLogoutTimeQuery);

    session_destroy();
    header("Location: landingpage.html");
    exit();
}

$queryCarouselContent = "SELECT * FROM carousel_content";
$resultCarouselContent = mysqli_query($conn, $queryCarouselContent);
$carouselContents = mysqli_fetch_all($resultCarouselContent, MYSQLI_ASSOC);

$queryCarouselImages = "SELECT * FROM carousel_images";
$resultCarouselImages = mysqli_query($conn, $queryCarouselImages);
$carouselImages = mysqli_fetch_all($resultCarouselImages, MYSQLI_ASSOC);
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .navbar {
            overflow: hidden;
            background-color: #333;
        }

        .navbar a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        .navbar .profile-btn {
            float: right;
            background-color:red;
            margin-right:3%;
            text-align: right;
            font-size:20px;
            margin-top:1%;
            

        }

        .content {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .center-div {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 50vh;
    
    float:right;
   
    margin-top:1%;
  
    background: rgba(255, 255, 255, 0.1); /* White background with 50% opacity */
    border: none; /* Hide the border */
    box-shadow: none; /* Remove any shadow */
}



        .user-info {
            text-align: center;
            background-color: white;
            padding: 20px;
            width: 20%;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 300px;
            
        }

        .upload img {
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .logout-button {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-button:hover {
            background-color: #d32f2f;
        }
        body {
       margin: 0;
       font-family: Arial, Helvetica, sans-serif;
}
      .video-background {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
        }
        .left{
            text-align:left;
            font-weight: bold;
        }

         /* Style for the modal */
         .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            animation-name: animatetop;
            animation-duration: 0.4s;
        }
        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .scrollable-container {
    max-height: 300px; /* Adjust based on your needs */
    overflow-y: auto;
    padding-right: 10px; /* Adds padding to prevent content from being cut off */
    border: 1px solid #ccc; /* Optional: for better visibility */
    border-radius: 5px; /* Optional: for rounded corners */
}



        
    </style>
</head>
<body>
<video autoplay muted loop class="video-background">
        <source src="city of game.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">
            <img src="images/Medhavi_Skills_University_official_Logo.png" alt="Logo" style="height: 30px;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="#" onclick="showModal()">Content Update</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="toggleContent()">Profile</a>
                </li>
            </ul>
        </div>
    </nav>


    <div class="content" id="profileContent">
        <div class="center-div">
            <div class="user-info">
                <form method="post">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
                <form class="form" id="form" enctype="multipart/form-data" method="post">
                    <div class="upload">
                        <img src="<?php echo $imagePath; ?>" width="125" height="125" title="<?php echo $image; ?>">
                        <div class="round">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="name" value="<?php echo $name; ?>">
                            <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png">
                            <i class="fa fa-camera" style="color: #fff;"></i>
                        </div>
                    </div>
                </form>
                <div class="left">
                    <p>ID: <?php echo $id; ?></p>
                    <p>Name: <?php echo $name; ?></p>
                    <p>Email: <?php echo $email; ?></p>
                    <p>Last Login: <?php echo $lastLogin; ?></p>
                    <p>Last Logout: <?php echo $lastLogout; ?></p>
                </div>
            </div>
        </div>
    </div>
     
    <div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Project Content</h2>
        <form action="userdeskboard.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title">
            </div>
            <div class="form-group">
                <label for="content">About</label>
                <textarea class="form-control" id="content_1" name="content" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="form-control" id="image_1" name="new_carousel_image" accept=".jpg, .jpeg, .png">
            </div>
            <div class="scrollable-container">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title_2" name="title">
                </div>
                <div class="form-group">
                    <label for="content">SKill_1</label>
                    <textarea class="form-control" id="content_2" name="content" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image_2" name="new_carousel_image" accept=".jpg, .jpeg, .png">
                </div>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title_3" name="title">
                </div>
                <div class="form-group">
                    <label for="content">skill_2</label>
                    <textarea class="form-control" id="content_3" name="content" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image_3" name="new_carousel_image" accept=".jpg, .jpeg, .png">
                </div>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title_4" name="title">
                </div>
                <div class="form-group">
                    <label for="content">Skills_3</label>
                    <textarea class="form-control" id="content_4" name="content" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image_4" name="new_carousel_image" accept=".jpg, .jpeg, .png">
                </div>
            </div>
            <button type="submit" name="add_content" class="btn btn-primary">Update Content</button>
        </form>
    </div>
</div>

     
    
    <script type="text/javascript">
        function toggleContent() {
            var content = document.getElementById("profileContent");
            if (content.style.display === "none" || content.style.display === "") {
                content.style.display = "block";
            } else {
                content.style.display = "none";
            }
        }

        document.getElementById("image").onchange = function() {
            document.getElementById("form").submit();
        }

        <?php if ($imageUploadSuccess): ?>
            alert("Image saved successfully!");
        <?php endif; ?>
        function showModal() {
        document.getElementById("modal").style.display = "block";
     }
     function showModal() {
            document.getElementById("modal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("modal").style.display = "none";
        }

        document.getElementById("image").onchange = function() {
            document.getElementById("form").submit();
        }
        
    document.getElementById("form").onsubmit = function(event) {
        event.preventDefault(); // Prevent default form submission
        var formData = new FormData(this);
        
        fetch('user_dashboard.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Log response from server
            alert('Content updated successfully!');
        })
        .catch(error => console.error('Error:', error));
    }
    // Set the inactivity timeout duration (5 seconds)
const timeoutDuration = 5000; // 5000 milliseconds = 5 seconds
let inactivityTimer;

// Function to reset the inactivity timer
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(logoutUser, timeoutDuration);
}

// Function to log out the user
function logoutUser() {
    // Perform the logout action (e.g., send a POST request to log out)
    fetch('logout.php', {
        method: 'POST',
        body: JSON.stringify({logout: true}),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'login.html'; // Redirect to login page
        }
    })
    .catch(error => console.error('Logout Error:', error));
}

// Event listeners to detect user activity
document.addEventListener('mousemove', resetInactivityTimer);
document.addEventListener('keypress', resetInactivityTimer);
document.addEventListener('click', resetInactivityTimer);

// Initialize the inactivity timer
resetInactivityTimer();

 </script>
</body>
</html>
