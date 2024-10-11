<?php
session_start();
include 'database.php';

// Function to generate CSRF token
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars($data);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize form data
    $username = sanitizeInput($_POST['username']);
    $old_password = $_POST['old_password']; 
    $new_password = $_POST['new_password'];
    $retype_password = $_POST['retype_password'];



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

        // Verify user exists and old password matches
        if ($user && password_verify($old_password, $user['password'])) {
            // Validate new password
            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+])[A-Za-z0-9!@#$%^&*()_+]{8,}$/';
            if (!preg_match($passwordRegex, $new_password)) {
                echo '<script>alert("New password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long.");</script>';
            } elseif ($new_password !== $retype_password) {
                echo '<script>alert("New password and retype password do not match.");</script>';
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->bindParam(':password', $hashed_password);
                $updateStmt->bindParam(':id', $user['id']);
                $updateStmt->execute();

                echo '<script>alert("Password changed successfully.");</script>';
                header("refresh:3; url=index.php");
            }
        } else {
            echo '<script>alert("Invalid username or old password.");</script>';
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Generate and store CSRF token in session
$_SESSION['csrf_token'] = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        /* CSS styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        
        form {
            max-width: 400px;
            margin: 0 auto;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
        }
        
        input[type="password"] {
            width: calc(100% - 10px);
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required><br>
            <label for="old_password">Old Password:</label>
            <input type="password" id="old_password" name="old_password" placeholder="Enter your old password" required><br>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" required><br>
            <label for="retype_password">Retype Password:</label>
            <input type="password" id="retype_password" name="retype_password" placeholder="Retype your new password" required><br><br>
            <input type="submit" value="Change Password">
        </form>
        <a href="index.php">Back to Sign In page</a>
    </div>
</body>
</html>

