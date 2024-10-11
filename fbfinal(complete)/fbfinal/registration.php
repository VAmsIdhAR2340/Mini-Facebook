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

// Check if session is valid
function isValidSession() {
    return isset($_SESSION['user_id']);
}



// Create connection using PDO with error handling
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    // Retrieve form data and sanitize
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = htmlspecialchars($_POST['email']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $repassword = htmlspecialchars($_POST['repassword']);
    $phonenumber = htmlspecialchars($_POST['phonenumber']);
    
    // Validate if all fields are filled
    if(empty($firstname) || empty($lastname) || empty($email) || empty($username) || empty($password) || empty($repassword) || empty($phonenumber)) {
        echo '<script>alert("All fields are mandatory.");</script>';
    } else {
        // Check if passwords match
        if($password !== $repassword) {
            echo '<script>alert("Passwords do not match.");</script>';
        } else {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<script>alert("Invalid email format.");</script>';
            } else {
                // Check if username and password meet the specified criteria
                $usernameRegex = '/^[\w .-]+@[\w-]+(.[\w-]+)*$/';
                $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+])[A-Za-z0-9!@#$%^&*()_+]{8,}$/';
                if (!preg_match($usernameRegex, $username)) {
                    echo '<script>alert("Please enter a valid email address as username.");</script>';
                } elseif (!preg_match($passwordRegex, $password)) {
                    echo '<script>alert("Password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long.");</script>';
                } else {
                    // Hash the password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Prepare SQL statement as a prepared statement to prevent SQL injection
                    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname) VALUES (?, ?, ?)");
                    $stmt->bindParam(1, $username);
                    $stmt->bindParam(2, $hashed_password);
                    $fullname = $firstname . ' ' . $lastname;
                    $stmt->bindParam(3, $fullname);
                    
                    // Execute SQL statement
                    try {
                        $stmt->execute();
                        
                        // Display success message
                        echo '<script>alert("Registration successful. Redirecting to index.php...");</script>';
                        // Redirect to index.php after successful registration
                        header("refresh:3; url=index.php"); // Redirect after 3 seconds
                        exit(); // Ensure that script execution stops after redirection
                    } catch(PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                }
            }
        }
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
    <title>User Registration</title>
    <style>
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
        
        input[type="text"],
        input[type="password"],
        input[type="email"] {
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
        
        .signin-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #555;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .signin-btn:hover {
            background-color: #333;
        }
        
        .password-toggle {
            position: relative;
            display: inline-block;
        }
        
        .password-toggle input[type="password"] {
            padding-right: 35px;
        }
        
        .password-toggle .toggle-btn {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background-color: transparent;
            border: none;
            outline: none;
            cursor: pointer;
        }
    </style>
    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;
            var usernameRegex = /^[\w .-]+@[\w-]+(.[\w-]+)*$/;
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+])[A-Za-z0-9!@#$%^&*()_+]{8,}$/;
            if (!usernameRegex.test(username)) {
                alert("Please enter a valid email address as username.");
                return false;
            }
            if (!passwordRegex.test(password)) {
                alert("Password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long.");
                return false;
            }
        }
        
      
    function togglePassword(inputId, btnId) {
        var passwordInput = document.getElementById(inputId);
        var toggleBtn = document.getElementById(btnId);
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleBtn.textContent = "Hide";
        } else {
            passwordInput.type = "password";
            toggleBtn.textContent = "Show";
        }
    }


</script>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter your first name" required><br>
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" placeholder="Enter your last name" required><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required><br>
            <label for="username">Username (Email Address):</label>
            <input type="email" id="username" name="username" placeholder="Enter your email address as username" required><br>
            <label for="password">Password:</label>
	    <div class="password-toggle">
		    <input type="password" id="password" name="password" placeholder="Enter your password" required>
		    <button type="button" class="toggle-btn" onclick="togglePassword('password', 'toggle-btn')">Show</button>
	    </div>
	    <label for="repassword">Re-enter Password:</label>
	    <div class="password-toggle">
		    <input type="password" id="repassword" name="repassword" placeholder="Enter your password" required>
		    <button type="button" class="toggle-btn" onclick="togglePassword('repassword', 'toggle-btn')">Show</button>
	    </div>

            <label for="phonenumber">Phone Number:</label>
            <input type="text" id="phonenumber" name="phonenumber" placeholder="Enter your phone number" required><br><br>
            <input type="submit" value="Register">
        </form>
        <a href="index.php">Back to Sign In page</a>
    </div>
</body>
</html>

