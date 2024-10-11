<?php
session_start();
include 'database.php'
// Function to generate CSRF token
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize form data
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password']; // You may want to perform further validation


    

    try {
        // Create a PDO instance
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare SQL statement to retrieve user details by username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Redirect to welcome page
            header("Location: welcome.php");
            exit();
        } else {
            echo '<script>alert("Invalid username/password.");</script>';
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Generate and store CSRF token in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In Page</title>
    <style>
        /* CSS styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-top: 50px;
            text-align: center;
        }
        
        h1 {
            color: #333;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .form-container {
            margin-bottom: 20px;
        }
        
        .form-container input {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SecAD Project</h1>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="text" name="username" placeholder="Enter your username">
                <input type="password" name="password" placeholder="Enter your password">
                <button type="submit" class="btn">Submit</button>
                <a href="changepassword.php" class="btn">Change Password</a>
            </form>
        </div>
        <a href="registration.php" class="btn">Sign Up</a>
        <a href="" class="btn">Sign In</a>
    </div>
</body>
</html>

